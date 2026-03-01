<?php

namespace Modules\CIMS_Email\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmailController extends Controller
{
    /**
     * Boot SMTP settings from database on each request
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->loadSmtpSettings();
            return $next($request);
        });
    }

    /**
     * Load SMTP settings from DB and apply to mail config
     */
    private function loadSmtpSettings()
    {
        try {
            $settings = DB::table('cims_email_settings')->pluck('setting_value', 'setting_key')->toArray();
            if (!empty($settings['smtp_host'])) {
                Config::set('mail.default', 'smtp');
                Config::set('mail.mailers.smtp.host', $settings['smtp_host']);
                Config::set('mail.mailers.smtp.port', $settings['smtp_port'] ?? 587);
                Config::set('mail.mailers.smtp.encryption', $settings['smtp_encryption'] ?? 'tls');
                Config::set('mail.mailers.smtp.username', $settings['smtp_username'] ?? '');
                Config::set('mail.mailers.smtp.password', $settings['smtp_password'] ?? '');
                Config::set('mail.from.address', $settings['from_email'] ?? $settings['smtp_username']);
                Config::set('mail.from.name', $settings['from_name'] ?? 'SmartWeigh CIMS');
            }
        } catch (\Exception $e) {
            // Table may not exist yet - silently continue
        }
    }

    /**
     * Get folder counts for sidebar
     */
    private function getFolderCounts()
    {
        return [
            'sent' => DB::table('cims_emails')->where('user_id', Auth::id())->where('folder', 'sent')->whereNull('deleted_at')->count(),
            'drafts' => DB::table('cims_emails')->where('user_id', Auth::id())->where('folder', 'drafts')->whereNull('deleted_at')->count(),
            'trash' => DB::table('cims_emails')->where('user_id', Auth::id())->where('folder', 'trash')->whereNull('deleted_at')->count(),
        ];
    }

    /**
     * Email Dashboard - shows sent emails (default view)
     */
    public function index(Request $request)
    {
        $folder = $request->get('folder', 'sent');
        $search = $request->get('search', '');
        $clientFilter = $request->get('client_id');

        $query = DB::table('cims_emails')
            ->where('user_id', Auth::id())
            ->where('folder', $folder)
            ->whereNull('deleted_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('to_emails', 'like', "%{$search}%")
                  ->orWhere('body_text', 'like', "%{$search}%");
            });
        }

        if ($clientFilter) {
            $query->where('client_id', $clientFilter);
        }

        $emails = $query->orderByDesc('created_at')->paginate(20);

        $clients = DB::table('client_master')
            ->where('is_active', 1)
            ->orderBy('company_name')
            ->get(['client_id', 'client_code', 'company_name']);

        $counts = $this->getFolderCounts();

        return view('cims_email::emails.index', compact('emails', 'folder', 'search', 'clients', 'clientFilter', 'counts'));
    }

    /**
     * Compose new email
     */
    public function compose(Request $request)
    {
        $clients = DB::table('client_master')
            ->where('is_active', 1)
            ->orderBy('company_name')
            ->get(['client_id', 'client_code', 'company_name']);

        $templates = DB::table('cims_email_templates')
            ->where('is_active', 1)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $draft = null;
        if ($request->has('draft_id')) {
            $draft = DB::table('cims_emails')
                ->where('id', $request->get('draft_id'))
                ->where('user_id', Auth::id())
                ->where('folder', 'drafts')
                ->first();
        }

        $selectedClientId = $request->get('client_id') ?? ($draft->client_id ?? null);
        $counts = $this->getFolderCounts();

        return view('cims_email::emails.compose', compact('clients', 'templates', 'draft', 'selectedClientId', 'counts'));
    }

    /**
     * Send email
     */
    public function send(Request $request)
    {
        $request->validate([
            'to_emails' => 'required|string',
            'subject' => 'required|string|max:500',
            'body_html' => 'required|string',
        ]);

        $toEmails = array_map('trim', explode(',', $request->to_emails));
        $ccEmails = $request->cc_emails ? array_map('trim', explode(',', $request->cc_emails)) : [];
        $bccEmails = $request->bcc_emails ? array_map('trim', explode(',', $request->bcc_emails)) : [];

        $user = Auth::user();
        $fromEmail = config('mail.from.address', $user->email ?? 'noreply@smartweigh.co.za');
        $fromName = config('mail.from.name', trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'SmartWeigh');

        // Store the email record
        $emailId = DB::table('cims_emails')->insertGetId([
            'client_id' => $request->client_id ?: null,
            'user_id' => Auth::id(),
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'to_emails' => json_encode($toEmails),
            'cc_emails' => json_encode($ccEmails),
            'bcc_emails' => json_encode($bccEmails),
            'subject' => $request->subject,
            'body_html' => $request->body_html,
            'body_text' => strip_tags($request->body_html),
            'status' => 'sending',
            'folder' => 'sent',
            'is_read' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('email_attachments/' . $emailId, $filename, 'public');

                DB::table('cims_email_attachments')->insert([
                    'email_id' => $emailId,
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'created_at' => now(),
                ]);
            }
        }

        // Send the email
        try {
            $attachments = DB::table('cims_email_attachments')
                ->where('email_id', $emailId)
                ->get();

            Mail::html($request->body_html, function ($message) use ($toEmails, $ccEmails, $bccEmails, $fromEmail, $fromName, $request, $attachments) {
                $message->from($fromEmail, $fromName);
                $message->to($toEmails);
                if (!empty($ccEmails)) $message->cc($ccEmails);
                if (!empty($bccEmails)) $message->bcc($bccEmails);
                $message->subject($request->subject);

                foreach ($attachments as $att) {
                    $fullPath = storage_path('app/public/' . $att->file_path);
                    if (file_exists($fullPath)) {
                        $message->attach($fullPath, ['as' => $att->original_filename, 'mime' => $att->mime_type]);
                    }
                }
            });

            DB::table('cims_emails')->where('id', $emailId)->update([
                'status' => 'sent',
                'sent_at' => now(),
                'updated_at' => now(),
            ]);

            if ($request->draft_id) {
                DB::table('cims_emails')->where('id', $request->draft_id)->update([
                    'deleted_at' => now(),
                ]);
            }

            return redirect()->route('cimsemail.sent')
                ->with('success', 'Email sent successfully!');

        } catch (\Exception $e) {
            DB::table('cims_emails')->where('id', $emailId)->update([
                'status' => 'failed',
                'updated_at' => now(),
            ]);

            return back()->withInput()
                ->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    /**
     * Save as draft
     */
    public function saveDraft(Request $request)
    {
        $data = [
            'client_id' => $request->client_id ?: null,
            'user_id' => Auth::id(),
            'from_email' => $request->from_email ?: '',
            'from_name' => '',
            'to_emails' => json_encode($request->to_emails ? array_map('trim', explode(',', $request->to_emails)) : []),
            'cc_emails' => json_encode($request->cc_emails ? array_map('trim', explode(',', $request->cc_emails)) : []),
            'bcc_emails' => json_encode($request->bcc_emails ? array_map('trim', explode(',', $request->bcc_emails)) : []),
            'subject' => $request->subject ?? '',
            'body_html' => $request->body_html ?? '',
            'body_text' => strip_tags($request->body_html ?? ''),
            'status' => 'draft',
            'folder' => 'drafts',
            'is_read' => 1,
            'updated_at' => now(),
        ];

        if ($request->draft_id) {
            DB::table('cims_emails')->where('id', $request->draft_id)->where('user_id', Auth::id())->update($data);
            $emailId = $request->draft_id;
        } else {
            $data['created_at'] = now();
            $emailId = DB::table('cims_emails')->insertGetId($data);
        }

        return redirect()->route('cimsemail.compose', ['draft_id' => $emailId])
            ->with('success', 'Draft saved.');
    }

    /**
     * View sent emails
     */
    public function sent(Request $request)
    {
        $request->merge(['folder' => 'sent']);
        return $this->index($request);
    }

    /**
     * View drafts
     */
    public function drafts(Request $request)
    {
        $request->merge(['folder' => 'drafts']);
        return $this->index($request);
    }

    /**
     * View single email
     */
    public function view($id)
    {
        $email = DB::table('cims_emails')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->whereNull('deleted_at')
            ->first();

        if (!$email) abort(404);

        DB::table('cims_emails')->where('id', $id)->update(['is_read' => 1]);

        $attachments = DB::table('cims_email_attachments')
            ->where('email_id', $id)
            ->get();

        $client = null;
        if ($email->client_id) {
            $client = DB::table('client_master')->where('client_id', $email->client_id)->first(['client_id', 'client_code', 'company_name']);
        }

        $counts = $this->getFolderCounts();

        return view('cims_email::emails.view', compact('email', 'attachments', 'client', 'counts'));
    }

    /**
     * Move to trash
     */
    public function trash($id)
    {
        DB::table('cims_emails')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['folder' => 'trash', 'updated_at' => now()]);

        return back()->with('success', 'Email moved to trash.');
    }

    /**
     * Permanently delete
     */
    public function delete($id)
    {
        DB::table('cims_emails')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['deleted_at' => now()]);

        return back()->with('success', 'Email deleted.');
    }

    /**
     * Email templates management
     */
    public function templates()
    {
        $templates = DB::table('cims_email_templates')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $counts = $this->getFolderCounts();

        return view('cims_email::emails.templates', compact('templates', 'counts'));
    }

    /**
     * Store new template
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'subject' => 'required|string|max:500',
            'body_html' => 'required|string',
            'category' => 'required|string|max:100',
        ]);

        DB::table('cims_email_templates')->insert([
            'name' => $request->name,
            'subject' => $request->subject,
            'body_html' => $request->body_html,
            'category' => $request->category,
            'is_active' => 1,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('cimsemail.templates')->with('success', 'Template created.');
    }

    /**
     * Update template
     */
    public function updateTemplate(Request $request, $id)
    {
        DB::table('cims_email_templates')->where('id', $id)->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'body_html' => $request->body_html,
            'category' => $request->category,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'updated_at' => now(),
        ]);

        return redirect()->route('cimsemail.templates')->with('success', 'Template updated.');
    }

    /**
     * Delete template
     */
    public function deleteTemplate($id)
    {
        DB::table('cims_email_templates')->where('id', $id)->delete();
        return back()->with('success', 'Template deleted.');
    }

    /**
     * Load template (AJAX)
     */
    public function loadTemplate($id)
    {
        $template = DB::table('cims_email_templates')->where('id', $id)->first();
        if (!$template) return response()->json(['error' => 'Template not found'], 404);
        return response()->json($template);
    }

    /**
     * Get client contacts (AJAX)
     */
    public function getClientContacts($clientId)
    {
        $client = DB::table('client_master')->where('client_id', $clientId)->first();
        $directors = DB::table('client_master_directors')
            ->where('client_id', $clientId)
            ->where('is_active', 1)
            ->get(['firstname', 'surname', 'email']);

        $contacts = [];
        if ($client && $client->email) {
            $contacts[] = ['name' => $client->company_name, 'email' => $client->email, 'type' => 'Company'];
        }
        foreach ($directors as $d) {
            if ($d->email) {
                $contacts[] = ['name' => trim($d->firstname . ' ' . $d->surname), 'email' => $d->email, 'type' => 'Director'];
            }
        }

        return response()->json($contacts);
    }

    /**
     * SMTP Settings page
     */
    public function settings()
    {
        $settings = [];
        try {
            $settings = DB::table('cims_email_settings')->pluck('setting_value', 'setting_key')->toArray();
        } catch (\Exception $e) {
            // Table may not exist
        }

        $counts = $this->getFolderCounts();

        return view('cims_email::emails.settings', compact('settings', 'counts'));
    }

    /**
     * Save SMTP Settings
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|string',
            'smtp_encryption' => 'nullable|string',
            'smtp_username' => 'required|string',
            'smtp_password' => 'required|string',
            'from_email' => 'required|email',
            'from_name' => 'required|string',
        ]);

        $keys = ['smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password', 'from_email', 'from_name'];

        foreach ($keys as $key) {
            DB::table('cims_email_settings')->updateOrInsert(
                ['setting_key' => $key],
                ['setting_value' => $request->input($key, ''), 'updated_at' => now()]
            );
        }

        return redirect()->route('cimsemail.settings')->with('success', 'SMTP settings saved successfully!');
    }

    /**
     * Test SMTP connection (AJAX)
     */
    public function testConnection(Request $request)
    {
        try {
            // Temporarily configure SMTP
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $request->smtp_host);
            Config::set('mail.mailers.smtp.port', $request->smtp_port);
            Config::set('mail.mailers.smtp.encryption', $request->smtp_encryption ?: null);
            Config::set('mail.mailers.smtp.username', $request->smtp_username);
            Config::set('mail.mailers.smtp.password', $request->smtp_password);

            // Purge the smtp mailer to force re-creation with new config
            app('mail.manager')->purge('smtp');

            $fromEmail = $request->from_email ?: $request->smtp_username;
            $fromName = $request->from_name ?: 'SmartWeigh CIMS';

            // Send a test email to the from address
            Mail::raw('This is a test email from CIMS Email Module. If you receive this, your SMTP settings are working correctly!', function ($message) use ($fromEmail, $fromName) {
                $message->from($fromEmail, $fromName);
                $message->to($fromEmail);
                $message->subject('CIMS Email - SMTP Test (' . now()->format('d M Y H:i') . ')');
            });

            return response()->json([
                'success' => true,
                'message' => 'SMTP connection successful! A test email was sent to ' . $fromEmail
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SMTP connection failed: ' . $e->getMessage()
            ]);
        }
    }
}
