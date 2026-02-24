<?php

return [
    'name' => 'CIMSDocManager',

    // Upload path (relative to public folder)
    'upload_path' => 'uploads/documents/',

    // Allowed file types for upload
    'allowed_extensions' => [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif',
        'mp3', 'mp4', 'm4a', 'html', 'htm', 'txt', 'zip', 'rar', 'csv', 'pptx'
    ],

    // Max file size in KB (0 = unlimited)
    'max_file_size' => 0,

    // Document icons mapping
    'document_icons' => [
        'pdf' => 'fa-file-pdf text-danger',
        'doc' => 'fa-file-word text-primary',
        'docx' => 'fa-file-word text-primary',
        'xls' => 'fa-file-excel text-success',
        'xlsx' => 'fa-file-excel text-success',
        'pptx' => 'fa-file-powerpoint text-warning',
        'jpg' => 'fa-file-image text-info',
        'jpeg' => 'fa-file-image text-info',
        'png' => 'fa-file-image text-info',
        'gif' => 'fa-file-image text-info',
        'mp3' => 'fa-file-audio text-warning',
        'mp4' => 'fa-file-video text-warning',
        'm4a' => 'fa-file-audio text-warning',
        'html' => 'fa-file-code text-secondary',
        'htm' => 'fa-file-code text-secondary',
        'txt' => 'fa-file-alt text-muted',
        'zip' => 'fa-file-archive text-secondary',
        'rar' => 'fa-file-archive text-secondary',
        'csv' => 'fa-file-csv text-success',
    ],
];
