@push('scripts')
    
<script>
    document.addEventListener('DOMContentLoaded', function() {
        


        // ── Initialize with existing banks if provided ──
        const existingBanks = {!! isset($existingBanks) ? json_encode($existingBanks) : '[]' !!};
        if (existingBanks && existingBanks.length > 0) {
            
            savedBanks = existingBanks.map(bank => ({
                ...bank,
                id: bank.db_id, // Use db_id as the id for consistency
                is_existing: true,
                is_deleted: false
            }));
        }

        // Display banks on page load
        displaySavedBanks();

        // ── Save Bank Button ──
        $(document).on('click', '#save-bank-btn', function() {
            if (validateBankForm()) {
                saveBank();
            }
        });


        // ══════════════════════════════════════
        // validateBankForm — validates all fields including file (only for new banks)
        // ══════════════════════════════════════
        function validateBankForm() {
            let isValid = true;
            const fields = [
                'bank_name', 'bank_account_holder', 'bank_account_number',
                'bank_account_type', 'account_status', 'bank_branch_name',
                'bank_branch_code', 'bank_swift_code', 'bank_account_date_opened','bank_statement_frequency','statement_cut_off_date'
            ];

            fields.forEach(field => {
                const $field = $(`#${field}`);
                const $error = $(`#${field}_error`);
                const val = ($field.val() || '').trim();

                if (!val) {
                    $error.text('This field is required').show();
                    isValid = false;
                } else {
                    $error.hide();
                }
            });

            // Validate file upload — REQUIRED for new banks
            const fileInput = $('#confirmation_of_banking_uplaod')[0];
            const $fileError = $('#confirmation_of_banking_uplaod_error');
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                $fileError.text('Confirmation of Banking PDF is required').show();
                isValid = false;
            } else {
                const file = fileInput.files[0];
                const ext = file.name.split('.').pop().toLowerCase();
                if (ext !== 'pdf') {
                    $fileError.text('File must be a PDF').show();
                    isValid = false;
                } else {
                    $fileError.hide();
                }
            }

            // Check for duplicate bank_account_number within the same bank
            const currentBankId = $('#bank_name').val();
            const currentAccountNumber = ($('#bank_account_number').val() || '').trim();
            if (currentBankId && currentAccountNumber) {
                const duplicate = savedBanks.find(b =>
                    !b.is_deleted && b.bank_id === currentBankId && b.bank_account_number === currentAccountNumber
                );
                if (duplicate) {
                    $('#bank_account_number_error')
                        .text('This account number already exists for ' + duplicate.bank_name)
                        .show();
                    isValid = false;
                }
            }

            return isValid;
        }

        // ══════════════════════════════════════
        // clearBankForm — properly resets all fields
        // ══════════════════════════════════════
        function clearBankForm() {
            
            // Clear text inputs
            $('#bank_account_number').val('');
            $('#bank_branch_name').val('');
            $('#bank_branch_code').val('');
            $('#bank_swift_code').val('');
            $('#bank_logo').val('');
            // Clear select dropdowns with selectpicker
            if ($.fn.selectpicker) {
                $('#bank_name').selectpicker('val', '');
                $('#bank_account_type').selectpicker('val', '');
                $('#account_status').selectpicker('val', '');
                $('#bank_statement_frequency').selectpicker('val', '');

                $('#bank_name').selectpicker('refresh');
                $('#bank_account_type').selectpicker('refresh');
                $('#account_status').selectpicker('refresh');
                $('#bank_statement_frequency').selectpicker('refresh');
            } else {
                 $('#bank_name, #bank_account_type, #account_status','#bank_statement_frequency').val('');
            }

            // Clear datepicker (both display and hidden field)
            $('#bank_account_date_opened_display').val('');
            $('#bank_account_date_opened').val('');
            
            $('#statement_cut_off_date').val('');
            $('#statement_cut_off_date_display').val('');

            // Clear file input
            // Clear file input properly
            const fileInput = $('#confirmation_of_banking_uplaod')[0];
            if (fileInput) {
                fileInput.value = '';
                // For better browser compatibility
                if (fileInput.files) {
                    const dt = new DataTransfer();
                    fileInput.files = dt.files;
                }
            }

            // Hide Bank Logo
             $('#bank_logo_display').attr('src', `{{ asset('') }}/bank_logo/other_logo.jpg`);

            // Hide all error tooltips
            $('#bank-form .sd_tooltip_red').hide();
        }

        // ══════════════════════════════════════
        // saveBank — captures ALL fields + file for NEW banks only
        // ══════════════════════════════════════
        function saveBank() {
            const bankId = Date.now(); // Temporary ID for new bank
            const isDefault = $('#is_default').is(':checked');

            if (isDefault) {
                savedBanks = savedBanks.map(bank => ({
                    ...bank,
                    is_default: false
                }));
            }

            const bankData = {
                id: bankId,
                bank_id: $('#bank_name').val(),
                bank_name: $('#bank_name option:selected').text().trim(),
                bank_account_holder: $('#bank_account_holder').val(),
                bank_account_number: $('#bank_account_number').val(),
                bank_account_type_id: $('#bank_account_type').val(),
                bank_account_type_name: $('#bank_account_type option:selected').text().trim(),
                bank_account_status_id: $('#account_status').val(),
                bank_account_status_name: $('#account_status option:selected').text().trim(),
                bank_statement_frequency_id: $('#bank_statement_frequency').val(),
                bank_statement_frequency_name: $('#bank_statement_frequency option:selected').text().trim(),
                bank_branch_name: $('#bank_branch_name').val(),
                bank_branch_code: $('#bank_branch_code').val(),
                bank_swift_code: $('#bank_swift_code').val(),
                bank_logo: $('#bank_logo').val(),
                bank_account_date_opened: $('#bank_account_date_opened').val(),
                bank_statement_cut_off_date: $('#statement_cut_off_date').val(),
                is_default: isDefault,
                is_existing: false, // Mark as new bank
                is_deleted: false
            };

            // Store the file object separately
            const fileInput = $('#confirmation_of_banking_uplaod')[0];
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                bankFiles[bankId] = fileInput.files[0];
            }

            savedBanks.push(bankData);

            displaySavedBanks();

            const bankModal = document.getElementById('bankModal'); // Replace with your modal ID
            if (bankModal) {
                const modal = bootstrap.Modal.getInstance(bankModal);
                if (modal) {
                    modal.hide();
                }
            }


            clearBankForm();
            $('#bank-form').hide();
            $('#save-bank-btn').hide();

            toastr.success('Bank added successfully!');
        }

        $('#bankModal').on('shown.bs.modal', function() {
            
            $('#bank-form').show();
            $('#save-bank-btn').show();
            $('#add-bank-btn').hide();
            $("#bank_account_holder").val($("#company_name").val())
        });

        $('#bankModal').on('hidden.bs.modal', function() {
            clearBankForm();
        });

        $('#bankModal').on('show.bs.modal', function(event) {
            const activeBanksCount = savedBanks.filter(b => !b.is_deleted).length;
            if (activeBanksCount >= 3) {
                event.preventDefault();
                toastr.warning('You can only save up to 3 banks.');
            }
        });
        // ══════════════════════════════════════
        // displaySavedBanks — renders bank cards (read-only for existing, full for new)
        // ══════════════════════════════════════
        function displaySavedBanks() {
            const $container = $('#saved-bank-list').empty();

            if (savedBanks.length === 0) {
                $container.html(`
                    <div class="empty-state">
                        <i class="fas fa-building-columns"></i>
                        <p>No banks saved yet. Fill in the form above and click Save Bank.</p>
                    </div>
                `);
                return;
            }

            savedBanks.forEach(bank => {
                const isDefault = !!bank.is_default;
                // const hasFile = bankFiles[bank.id] ? '<span class="badge bg-primary ms-1"><i class="fa fa-file-pdf text-white"></i></span>' : (bank.has_file ? '<span class="badge bg-success ms-1"><i class="fa fa-file-pdf text-white"></i> Existing</span>' : '');
                const statusBadge = '<span class="badge bg-success">' + bank.bank_account_status_name + '</span>';
                const cimsDocViewBaseUrl = "{{ url('cimsdocmanager/view') }}";
                
                const viewFileButton = bank.document
                ? ` <span class="badge bg-primary sd_background_pink">
                    <a href="${cimsDocViewBaseUrl}/${bank.document}" class="text-white font-14" target="_blank">
                        <i class="fa fa-download"></i> View Certificate
                    </a>
                </span>`
                : ''; // To view the uploaded Confirmation of Banking document

                // Visual feedback for deleted banks
                const deletedClass = bank.is_deleted ? 'opacity-50 text-decoration-line-through' : '';
                const deleteButtonText = bank.is_deleted ? '<i class="fa fa-undo"></i> Restore' : '<i class="fa fa-trash"></i> Delete';
                const deleteButtonClass = bank.is_deleted ? 'btn-warning' : 'btn-danger';
                // const deletebtn =  `<button type="button" class="delete-card delete-bank ${deleteButtonClass}" title="${bank.is_deleted ? 'Restore bank' : 'Delete bank'}" data-bank-id="${bank.id}">
                //                             ${deleteButtonText}
                //                         </button>` // Delete Button Removed as per new design
                const deletebtn = '';
                 // <div class="col-md-2 d-flex align-items-center justify-content-start">
                //     <input type="checkbox" class="bank-checkbox multi-card-checkbox"
                //         data-bank-id="${bank.id}" ${isSelected ? 'checked' : ''}>
                // </div>
                const $card = $(`
                    <div class="col-lg-4 mt-3 bank-card ${bank.is_deleted ? 'marked-for-delete' : ''}">
                        <div class="multi-card shadow-md ${bank.is_deleted ? 'opacity-50' : ''}">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card-content">
                                             <div class="bank-logo-container mb-2">
                                                <img src="{{ asset('') }}${bank.bank_logo}" alt="${bank.bank_name} logo" 
                                                    class="img-fluid" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                                            </div>
                                            <h5 class="${bank.is_deleted ? 'text-muted' : ''}">${bank.bank_name}</h5>
                                            <dl class="mb-0">
                                                <dt>Account Holder:</dt><dd>${bank.bank_account_holder}</dd>
                                                <dt>Account No:</dt><dd>${bank.bank_account_number}</dd>
                                                <dt>Account Type:</dt><dd>${bank.bank_account_type_name}</dd>
                                                <dt>Date Opened:</dt><dd>${formatDateDisplay(bank.bank_account_date_opened)}</dd>
                                                <dt>Status:</dt><dd>${statusBadge}</dd>
                                            </dl>
                                            
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <div class="form-check d-flex align-items-center">
                                                    <input type="radio" class="form-check-input bank-default-radio" name="default_bank"
                                                        data-bank-id="${bank.id}" ${isDefault ? 'checked' : ''} ${bank.is_deleted ? 'disabled' : ''}>
                                                    <label class="form-check-label" style="margin-bottom:0px !important;margin-top:3px">Default</label>
                                                </div>
                                                ${viewFileButton}
                                            </div>
                                        </div>
                                        ${deletebtn}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                $container.append($card);
            });
        }


        $(document).on('change', '.bank-default-radio', function(e) {
            e.stopPropagation();
            const bankId = $(this).data('bank-id');
            savedBanks = savedBanks.map(bank => ({
                ...bank,
                is_default: bank.id === bankId
            }));
            displaySavedBanks();
        });

        // ── Delete/Restore bank card ──
        $(document).on('click', '.delete-bank', function(e) {
            e.stopPropagation();
            const bankId = $(this).data('bank-id');
            const bank = savedBanks.find(b => b.id === bankId);
            
            if (bank.is_existing) {
                // Toggle is_deleted flag for existing banks
                bank.is_deleted = !bank.is_deleted;
                if (bank.is_deleted) {
                    deletedBankIds.push(bank.db_id);
                    toastr.info('Bank marked for deletion');
                } else {
                    deletedBankIds = deletedBankIds.filter(id => id !== bank.db_id);
                    toastr.info('Bank restored');
                }
            } else {
                // Remove new banks immediately
                savedBanks = savedBanks.filter(b => b.id !== bankId);
                delete bankFiles[bankId];
                toastr.info('Bank removed');
            }
            displaySavedBanks();
        });

       
    });
</script>
@endpush