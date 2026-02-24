@push('scripts')
    
<script>
    document.addEventListener('DOMContentLoaded', function() {

        let editingDirectorId = null;
        const appBasePath = "{{ rtrim(parse_url(config('app.url'), PHP_URL_PATH) ?? '', '/') }}";
        const directorUpdateBaseUrl = `${appBasePath}/ajax/directors`;
        // ── Initialize with existing banks if provided ──
        const existingDirectors = {!! isset($existingDirectors) ? json_encode($existingDirectors) : '[]' !!};
        if (existingDirectors && existingDirectors.length > 0) {
            
            savedDirectors = existingDirectors.map(director => ({
                ...director,
                id: director.db_id, // Use db_id as the id for consistency
                is_existing: true,
                is_deleted: false
            }));
        }

        // Display banks on page load
        displaySavedDirectors();

        // ── Save Bank Button ──
        $(document).on('click', '#save-director-btn', function() {
            if (validateDirectorForm()) {
                if (typeof isEditMode !== 'undefined' && isEditMode && editingDirectorId) {
                    updateDirectorAjax(editingDirectorId);
                } else {
                    saveDirector();
                }
            }
        });

        function setDirectorModalMode(mode) {
            const $btn = $('#save-director-btn');

            if (mode === 'update') {
                $btn.html('<i class="fa fa-save me-2"></i> Update');

                $('#date-resigned-wrapper').show();

                if ($.fn.selectpicker) {
                    $('#person_id').prop('disabled', true).selectpicker('refresh');
                } else {
                    $('#person_id').prop('disabled', true);
                }

                return;
            }

            // clearDirectorForm();
            $btn.html('<i class="fa fa-save me-2"></i> Save');

            $('#date-resigned-wrapper').hide();
            if ($.fn.selectpicker) {
                $('#person_id').prop('disabled', false).selectpicker('refresh');
            } else {
                $('#person_id').prop('disabled', false);
            }
        }

        function openDirectorModalWithData(director) {
            if (!director) {
                return;
            }

            editingDirectorId = director.id;
            setDirectorModalMode('update');

            $('#director-form').show();
            $('#save-director-btn').show();

            if ($.fn.selectpicker) {
                $('#person_id').selectpicker('val', String(director.person_id || ''));
                $('#director_type').selectpicker('val', String(director.director_type_id || ''));
                $('#director_status').selectpicker('val', String(director.director_status_id || ''));
                $('#person_id').selectpicker('refresh');
                $('#director_type').selectpicker('refresh');
                $('#director_status').selectpicker('refresh');
            } else {
                $('#person_id').val(String(director.person_id || ''));
                $('#director_type').val(String(director.director_type_id || ''));
                $('#director_status').val(String(director.director_status_id || ''));
            }

            $('#director_type').trigger('change');

            $('#number_of_director_shares').val(String(director.number_of_director_shares || ''));

            $('#date_engaged').val(director.date_engaged || '');
            $('#date_engaged_display').val(director.date_engaged ? formatDateDisplay(director.date_engaged) : '');

            $('#date_resigned').val(director.date_resigned || '');
            $('#date_resigned_display').val(director.date_resigned ? formatDateDisplay(director.date_resigned) : '');


             // Set min/max dates for date_resigned picker based on date_engaged
            if ($.fn.bootstrapMaterialDatePicker && director.date_engaged) {
                const engagedDate = new Date(director.date_engaged);
                const today = new Date();
                
                try {
                    $('#date_resigned_display').bootstrapMaterialDatePicker('setMinDate', engagedDate);
                    $('#date_resigned_display').bootstrapMaterialDatePicker('setMaxDate', today);
                } catch (e) {
                    console.warn('Could not set date_resigned picker constraints:', e);
                }
            }

            $('#director-form .sd_tooltip_red').hide();

            const directorModalEl = document.getElementById('directorModal');
            if (directorModalEl && window.bootstrap && bootstrap.Modal) {
                const modal = bootstrap.Modal.getOrCreateInstance(directorModalEl);
                modal.show();
            }
        }

        function resetDirectorEditState() {
            editingDirectorId = null;
            setDirectorModalMode('create');
        }

       function validateDirectorConstraints() {
            const toInt = (value) => parseInt(String(value || '').replace(/[,\s]/g, ''), 10) || 0;

            const maxDirectors = toInt($('#number_of_directors').val());
            const maxShares = toInt($('#number_of_shares').val());

            const tempDirector = {
                id: editingDirectorId || Date.now(),
                db_id: editingDirectorId || null,
                director_type_id: $('#director_type').val(),
                director_status_id: $('#director_status').val(),
                number_of_director_shares: toInt($('#number_of_director_shares').val()),
                is_deleted: false
            };

            const activeDirectors = savedDirectors.filter((director) => !director.is_deleted);
            const mergedDirectors = [...activeDirectors];

            const keyOf = (director) => String(director.db_id ?? director.id ?? '');
            const tempKey = String(tempDirector.db_id ?? tempDirector.id);

            const existingIndex = mergedDirectors.findIndex((director) => keyOf(director) === tempKey);

            if (existingIndex >= 0) {
                mergedDirectors[existingIndex] = { ...mergedDirectors[existingIndex], ...tempDirector };
            } else {
                mergedDirectors.push(tempDirector);
            }

            const shareholderDirectors = mergedDirectors.filter((director) =>
                String(director.director_type_id) === '1' &&
                String(director.director_status_id) === '1'
            );

            const shareholderCount = shareholderDirectors.length;
            const totalShares = shareholderDirectors.reduce((sum, director) => sum + toInt(director.number_of_director_shares), 0);

            const activeType2Count = mergedDirectors.filter((director) =>
                String(director.director_type_id) === '2' &&
                String(director.director_status_id) === '1'
            ).length;

            const activeType3Count = mergedDirectors.filter((director) =>
                String(director.director_type_id) === '3' &&
                String(director.director_status_id) === '1'
            ).length;

            const errors = [];

            if (shareholderCount > maxDirectors) {
                errors.push({
                    field: 'director_type',
                    message: `Cannot add more shareholder directors. Maximum: ${maxDirectors}`
                });
            }

            // if (totalShares > maxShares) {
            //     const overBy = totalShares - maxShares;
            //     const remaining = Math.max(0, maxShares - totalShares);

            //     errors.push({
            //         field: 'number_of_director_shares',
            //         message: `You have ${maxShares.toLocaleString()} total shares available. Right now, ${totalShares.toLocaleString()} shares are assigned, so you need to remove ${overBy.toLocaleString()} share${overBy === 1 ? '' : 's'} to continue.`
            //     });
            // }

           if (totalShares > maxShares) {
                const overBy = totalShares - maxShares;

                const isCurrentShareholderActive =
                    String(tempDirector.director_type_id) === '1' &&
                    String(tempDirector.director_status_id) === '1';

                const currentEntryShares = isCurrentShareholderActive
                    ? toInt(tempDirector.number_of_director_shares)
                    : 0;

                const alreadyAllocated = Math.max(0, totalShares - currentEntryShares);

                errors.push({
                    field: 'number_of_director_shares',
                    message: `You have ${maxShares.toLocaleString()} shares available in total. ` +
                        `${alreadyAllocated.toLocaleString()} share${alreadyAllocated === 1 ? '' : 's'} are already assigned to other current directors. ` +
                        `This entry adds ${currentEntryShares.toLocaleString()}, bringing the total to ${totalShares.toLocaleString()}. ` +
                        `Please reduce by ${overBy.toLocaleString()} share${overBy === 1 ? '' : 's'}.`
                });
            }

            if (activeType2Count > 1) {
                errors.push({
                    field: 'director_type',
                    message: 'Only one Active director is allowed for Director Type Incorporator.'
                });
            }

            if (activeType3Count > 1) {
                errors.push({
                    field: 'director_type',
                    message: 'Only one Active director is allowed for Director Type SARS Representative.'
                });
            }

            return {
                isValid: errors.length === 0,
                errors
            };
        }

        // function validateDirectorConstraints() {
        //     const toInt = (value) => parseInt(String(value || '').replace(/[,\s]/g, ''), 10) || 0;

        //     const maxDirectors = toInt($('#number_of_directors').val());
        //     const maxShares = toInt($('#number_of_shares').val());

        //     const tempDirector = {
        //         id: editingDirectorId || Date.now(),
        //         db_id: editingDirectorId || null,
        //         director_type_id: $('#director_type').val(),
        //         director_status_id: $('#director_status').val(),
        //         number_of_director_shares: toInt($('#number_of_director_shares').val()),
        //         is_deleted: false
        //     };

        //     // IMPORTANT: includes existing + new (only excludes deleted)
        //     const activeDirectors = savedDirectors.filter((director) => !director.is_deleted);
        //     const testDirectors = [...activeDirectors];

        //     const keyOf = (director) => String(director.db_id ?? director.id ?? '');
        //     const tempKey = String(editingDirectorId || tempDirector.id);

        //     const existingIndex = testDirectors.findIndex((director) => keyOf(director) === tempKey);

        //     if (existingIndex >= 0) {
        //         testDirectors[existingIndex] = { ...testDirectors[existingIndex], ...tempDirector };
        //     } else {
        //         testDirectors.push(tempDirector);
        //     }

        //     const shareholderDirectors = testDirectors.filter((director) =>
        //         String(director.director_type_id) === '1' &&
        //         String(director.director_status_id) === '1'
        //     );

        //     const shareholderCount = shareholderDirectors.length;

        //     const totalShares = shareholderDirectors.reduce((sum, director) => {
        //         return sum + toInt(director.number_of_director_shares);
        //     }, 0);

        //     const activeType2Count = testDirectors.filter((director) =>
        //         String(director.director_type_id) === '2' &&
        //         String(director.director_status_id) === '1'
        //     ).length;

        //     const activeType3Count = testDirectors.filter((director) =>
        //         String(director.director_type_id) === '3' &&
        //         String(director.director_status_id) === '1'
        //     ).length;

        //     const errors = [];

        //     if (shareholderCount > maxDirectors) {
        //         errors.push({
        //             field: 'director_type',
        //             message: `Cannot add more shareholder directors. Maximum: ${maxDirectors}`
        //         });
        //     }

        //     if (totalShares > maxShares) {
        //         const overBy = totalShares - maxShares;
        //         errors.push({
        //             field: 'number_of_director_shares',
        //             message: `You can assign only ${maxShares.toLocaleString()} shares in total. ${totalShares.toLocaleString()} is allocated. You are over by ${overBy.toLocaleString()} shares.`
        //         });
        //     }

        //     if (activeType2Count > 1) {
        //         errors.push({
        //             field: 'director_type',
        //             message: 'Only one Active director is allowed for Director Type Incorporator.'
        //         });
        //     }

        //     if (activeType3Count > 1) {
        //         errors.push({
        //             field: 'director_type',
        //             message: 'Only one Active director is allowed for Director Type SARS Representative.'
        //         });
        //     }

        //     return {
        //         isValid: errors.length === 0,
        //         errors
        //     };
        // }
        // ══════════════════════════════════════
        // validateBankForm — validates all fields including file (only for new banks)
        // ══════════════════════════════════════
        function validateDirectorForm() {
            debugger;
            let isValid = true;
            const directorTypeId = $('#director_type').val();
            const fields = [
                'director_type', 'person_id', 'date_engaged', 'director_status'
            ];

            // Add number_of_director_shares only if director_type is 1
            if (directorTypeId == 1) {
                fields.push('number_of_director_shares');
            }

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

            if (!isValid) {
                return false;
            }


            const validation = validateDirectorConstraints();
            if (!validation.isValid) {
                validation.errors.forEach(err => {
                    $(`#${err.field}_error`).text(err.message).show();
                });
                isValid = false;
            }

            return isValid;
        }

        // ══════════════════════════════════════
        // clearBankForm — properly resets all fields
        // ══════════════════════════════════════
        function clearDirectorForm() {
            
            // Clear select dropdowns with selectpicker
            if ($.fn.selectpicker) {
                $('#director_type').selectpicker('val', '');
                $('#person_id').selectpicker('val', '');
                $('#director_status').selectpicker('val', '');

                $('#director_type').selectpicker('refresh');
                $('#person_id').selectpicker('refresh');
                $('#director_status').selectpicker('refresh');
            } else {
                 $('#director_type, #person_id, #director_status').val('');
            }

            $('#number_of_director_shares').val('');


            // Clear datepicker (both display and hidden field)
            $('#date_engaged_display').val('');
            $('#date_engaged').val('');
            
            $('#date_resigned_display').val('');
            $('#date_resigned').val('');

            // Hide all error tooltips
            $('#director-form .sd_tooltip_red').hide();

            resetDirectorEditState();
        }

        // ══════════════════════════════════════
        // saveBank — captures ALL fields + file for NEW banks only
        // ══════════════════════════════════════
        function saveDirector() {
            debugger;
            const directorId = Date.now(); // Temporary ID for new bank
            const isDefault = $('#is_default').is(':checked');
            const directorTypeId = $('#director_type').val();

            if (isDefault) {
                savedDirectors = savedDirectors.map(director => ({
                    ...director,
                    is_default: false
                }));
            }

            const directorData = {
                id: directorId,
                person_id: $('#person_id').val(),
                person_name: $('#person_id option:selected').text().trim(),
                director_type_id: $('#director_type').val(),
                director_type_name: $('#director_type option:selected').text().trim(),
                director_status_id: $('#director_status').val(),
                director_status_name: $('#director_status option:selected').text().trim(),
                date_engaged: $('#date_engaged').val(),
                date_resigned: $('#date_resigned').val() ?? null,
                director_profile_image: $('#director_profile_image').val(),
                number_of_director_shares: directorTypeId == '1' ? $('#number_of_director_shares').val(): '',
                is_default: isDefault,
                is_existing: false, // Mark as new bank
                is_deleted: false
            };

            savedDirectors.push(directorData);


            displaySavedDirectors();

            const directorModal = document.getElementById('directorModal'); // Replace with your modal ID
            if (directorModal) {
                const modal = bootstrap.Modal.getInstance(directorModal);
                if (modal) {
                    modal.hide();
                }
            }


            clearDirectorForm();
            $('#director-form').hide();
            $('#save-director-btn').hide();

            toastr.success('Director added successfully!');
        }

        // $('#directorModal').on('shown.bs.modal', function() {
         

        // });

        $('#directorModal').on('hidden.bs.modal', function() {
            clearDirectorForm();
        });

        // $('#directorModal').on('show.bs.modal', function(event) {
        //     const activeDirectorsCount = savedDirectors.filter(b => !b.is_deleted).length;
        //     if (activeDirectorsCount >= 3) {
        //         event.preventDefault();
        //         toastr.warning('You can only save up to 3 Directors.');
        //     }
        // });
        // ══════════════════════════════════════
        // displaySavedDirectors — renders director cards (read-only for existing, full for new)
        // ══════════════════════════════════════
        function displaySavedDirectors() {
            const $container = $('#saved-director-list').empty();

            if (savedDirectors.length === 0) {
                $container.html(`
                    <div class="empty-state">
                        <i class="fas fa-building-columns"></i>
                        <p>No Directors saved yet. Fill in the form above and click Save Directors.</p>
                    </div>
                `);
                return;
            }

            savedDirectors.forEach(director => {
                const isDefault = !!director.is_default;
                const statusBadge = '<span class="badge bg-success">' + director.director_status_name + '</span>';
                // const cimsDocViewBaseUrl = "{{ url('cimsdocmanager/view') }}";
                
                // const viewFileButton = bank.document
                // ? ` <span class="badge bg-primary sd_background_pink">
                //     <a href="${cimsDocViewBaseUrl}/${bank.document}" class="text-white font-14" target="_blank">
                //         <i class="fa fa-download"></i> View Certificate
                //     </a>
                // </span>`
                // : ''; // To view the uploaded Confirmation of Banking document

                const viewFileButton = '';
                // Visual feedback for deleted banks
                const deletedClass = director.is_deleted ? 'opacity-50 text-decoration-line-through' : '';
                const deleteButtonText = director.is_deleted ? '<i class="fa fa-undo"></i> Restore' : '<i class="fa fa-trash"></i> Delete';
                const deleteButtonClass = director.is_deleted ? 'btn-warning' : 'btn-danger';
                 // const deletebtn =  `<button type="button" class="delete-card delete-bank ${deleteButtonClass}" title="${bank.is_deleted ? 'Restore bank' : 'Delete bank'}" data-bank-id="${bank.id}">
                //                             ${deleteButtonText}
                //                         </button>` // Delete Button Removed as per new design
                const deletebtn = '';

                const date_resigned = director.date_resigned ? `<dt style="width:14rem">Date Resigned:</dt><dd>${formatDateDisplay(director.date_resigned)}</dd>` : '' ;
                
                const number_of_director_shares = director.director_type_id == 1 ? `<dt style="width:14rem">Number of Shares:</dt><dd>${director.number_of_director_shares}</dd>` : '';

                const updateBtn = (typeof isEditMode !== 'undefined' && isEditMode && director.is_existing && !director.is_deleted)
                    ? `<button type="button" class="btn btn-primary btn-sm edit-director edit-card" data-director-id="${director.id}">
                            <i class="fa fa-pen"></i> Update
                       </button>`
                    : '';

                const $card = $(`
                    <div class="col-lg-6 mt-3 director-card ${director.is_deleted ? 'marked-for-delete' : ''}">
                        <div class="multi-card shadow-md ${director.is_deleted ? 'opacity-50' : ''}">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card-content">
                                             <div class="bank-logo-container mb-2 d-flex justify-content-center">
                                                <img src="${director.director_profile_image}" alt="${director.person_name} profile image"
                                                    class="img-fluid"
                                                    style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; object-position: center; border: 2px solid #e9ecef;">
                                            </div>
                                            <h5 class="${director.is_deleted ? 'text-muted' : ''}">${director.person_name}</h5>
                                            <dl class="mb-0" style="font-size: 22px;">
                                                <dt style="width:14rem">Director Name:</dt><dd>${director.person_name}</dd>
                                                <dt style="width:14rem">ID Number:</dt><dd>${director.identity_number}</dd>
                                                <dt style="width:14rem">Address:</dt><dd>${director.address}</dd>
                                                <dt style="width:14rem">Director Type:</dt><dd>${director.director_type_name}</dd>
                                                ${number_of_director_shares}
                                                <dt style="width:14rem">Date Engaged:</dt><dd>${formatDateDisplay(director.date_engaged)}</dd>
                                                ${date_resigned}
                                                <dt style="width:14rem">Status:</dt><dd>${statusBadge}</dd>
                                            </dl>
                                        </div>
                                        ${updateBtn}
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

        $(document).on('click', '.edit-director', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!(typeof isEditMode !== 'undefined' && isEditMode)) {
                toastr.warning('Director update is only available in Edit mode.');
                return;
            }

            const directorId = $(this).data('director-id');
            const director = savedDirectors.find(d => String(d.id) === String(directorId));

            if (!director) {
                toastr.error('Director not found.');
                return;
            }

            openDirectorModalWithData(director);
        });

        $(document).on('click', '#add-director', function(e) {
            e.preventDefault();
            e.stopPropagation();
            resetDirectorEditState()

            $('#director-form').show();
            $('#save-director-btn').show();
            $('#director_type').trigger('change');

            const directorModalEl = document.getElementById('directorModal');
            if (directorModalEl && window.bootstrap && bootstrap.Modal) {
                const modal = bootstrap.Modal.getOrCreateInstance(directorModalEl);
                modal.show();
            }

        });

        function updateDirectorAjax(directorId) {
             const directorTypeId = $('#director_type').val();
            if (!(typeof isEditMode !== 'undefined' && isEditMode)) {
                toastr.warning('Director update is only available in Edit mode.');
                return;
            }

            if (typeof clientId === 'undefined' || !clientId) {
                toastr.error('Missing client id; cannot update director.');
                return;
            }

            const payload = {
                _method: 'PUT',
                client_id: clientId,
                director_type_id: $('#director_type').val(),
                director_type_name: $('#director_type option:selected').text().trim(),
                director_status_id: $('#director_status').val(),
                director_status_name: $('#director_status option:selected').text().trim(),
                number_of_director_shares: directorTypeId == '1' ? $('#number_of_director_shares').val(): '',
                date_engaged: $('#date_engaged').val(),
                date_resigned: $('#date_resigned').val() ?? null
            };

            $.ajax({
                url: `${directorUpdateBaseUrl}/${directorId}`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: payload,
                success: function(response) {
                    if (!response || !response.director) {
                        toastr.error('Unexpected response from server.');
                        return;
                    }

                    const updated = response.director;
                    const index = savedDirectors.findIndex(d => String(d.id) === String(directorId));
                    if (index >= 0) {
                        savedDirectors[index] = {
                            ...savedDirectors[index],
                            ...updated,
                            id: updated.db_id,
                            is_existing: true,
                            is_deleted: false
                        };
                    }

                    displaySavedDirectors();

                    const directorModalEl = document.getElementById('directorModal');
                    if (directorModalEl && window.bootstrap && bootstrap.Modal) {
                        const modal = bootstrap.Modal.getInstance(directorModalEl);
                        if (modal) {
                            modal.hide();
                        }
                    }

                    resetDirectorEditState();
                    toastr.success('Director updated successfully!');
                },
                error: function(xhr) {
                    const msg = xhr?.responseJSON?.message
                        || xhr?.responseJSON?.error
                        || 'Failed to update director.';
                    toastr.error(msg);
                }
            });
        }


        $(document).on('change', '.director-default-radio', function(e) {
            e.stopPropagation();
            const directorId = $(this).data('director-id');
            savedDirectors = savedDirectors.map(director => ({
                ...director,
                is_default: director.id === directorId
            }));
            displaySavedDirectors();
        });

        // ── Delete/Restore bank card ──
        // $(document).on('click', '.delete-director', function(e) {
        //     e.stopPropagation();
        //     const directorId = $(this).data('director-id');
        //     const director = savedDirectors.find(d => d.id === directorId);
            
        //     if (director.is_existing) {
        //         // Toggle is_deleted flag for existing banks
        //         director.is_deleted = !director.is_deleted;
        //         if (director.is_deleted) {
        //             deletedDirectorIds.push(director.db_id);
        //             toastr.info('Direcotr marked for deletion');
        //         } else {
        //             deletedDirectorIds = deletedDirectorIds.filter(id => id !== director.db_id);
        //             toastr.info('Director restored');
        //         }
        //     } else {
        //         // Remove new banks immediately
        //         savedDirectors = savedDirectors.filter(d => d.id !== directorId);
        //         delete directorFiles[directorId];
        //         toastr.info('Director removed');
        //     }
        //     displaySavedDirectors();
        // });

       
    });
</script>


<script>
// $(function () {
//     var $engagedDisplay = $('#date_engaged_display');
//     var $engagedHidden = $('#date_engaged');

//     var $resignedDisplay = $('#date_resigned_display');
//     var $resignedHidden = $('#date_resigned');

//     if (!$engagedDisplay.length || !$resignedDisplay.length) {
//         return;
//     }

//     function disableResigned() {
//         $resignedHidden.val('');
//         $resignedDisplay.val('');
//         $resignedDisplay.prop('disabled', true);
//     }

//     function enableResigned() {
//         $resignedDisplay.prop('disabled', false);
//     }

//     function ensureResignedPickerInitialized() {
//         if (!$.fn.bootstrapMaterialDatePicker) {
//             return;
//         }

//         if ($resignedDisplay.data('bootstrapMaterialDatePicker')) {
//             return;
//         }

//         $resignedDisplay.bootstrapMaterialDatePicker({
//             weekStart: 0,
//             time: false,
//             format: 'ddd, D MMM YYYY',
//             clearButton: true,
//             maxDate: new Date()
//         }).on('change', function (e, date) {
//             // Uses your existing helper used elsewhere in this page
//             $resignedHidden.val(date ? formatDateDB(date) : '');
//         });
//     }

//     function applyResignedRules(minDate) {
//         ensureResignedPickerInitialized();

//         try {
//             $resignedDisplay.bootstrapMaterialDatePicker('setMinDate', minDate || null);
//         } catch (e) {}

//         try {
//             $resignedDisplay.bootstrapMaterialDatePicker('setMaxDate', new Date());
//         } catch (e) {}
//     }

//     // Default: disabled
//     ensureResignedPickerInitialized();
//     disableResigned();

//     // If engaged already has a value (edit/old input), enable + apply rules
//     if ($engagedHidden.val()) {
//         enableResigned();
//         applyResignedRules(moment($engagedHidden.val(), 'YYYY-MM-DD').toDate());
//     }

//     // When engaged changes, set min/max for resigned dynamically
//     $engagedDisplay.on('change', function (e, date) {
//         if (!date) {
//             disableResigned();
//             return;
//         }

//         enableResigned();
//         applyResignedRules(date.toDate()); // 'date' is a moment object

//         // Force re-pick within new range
//         $resignedHidden.val('');
//         $resignedDisplay.val('');
//     });
// });
</script>

<script>

$(document).on('change', '#director_type', function() {
    const directorTypeId = $(this).val();
    const $sharesWrapper = $('#number-of-director-shares-wrapper');
    const $statusWrapper = $('#director-status-wrapper');
    const $engagedWrapper = $('#date-engaged-wrapper');

    if (directorTypeId == 1) {
        // Show shares wrapper and adjust column sizes
        $sharesWrapper.show();
        $statusWrapper.removeClass('col-md-6').addClass('col-md-4');
        $engagedWrapper.removeClass('col-md-6').addClass('col-md-4');
    } else {
        // Hide shares wrapper and reset column sizes
        $sharesWrapper.hide();
        $statusWrapper.removeClass('col-md-4').addClass('col-md-6');
        $engagedWrapper.removeClass('col-md-4').addClass('col-md-6');
        // $('#number_of_director_shares').val(''); // Clear the field
    }
});

// Also update when date_engaged changes in the modal
$(document).on('change', '#date_engaged_display', function() {
    const engagedDate = $('#date_engaged').val();
    
    if (engagedDate && $.fn.bootstrapMaterialDatePicker) {
        const minDate = new Date(engagedDate);
        const maxDate = new Date();
        
        try {
            $('#date_resigned_display').bootstrapMaterialDatePicker('setMinDate', minDate);
            $('#date_resigned_display').bootstrapMaterialDatePicker('setMaxDate', maxDate);
            
            // Clear date_resigned if it's now outside the new range
            const resignedDate = $('#date_resigned').val();
            if (resignedDate) {
                const resigned = new Date(resignedDate);
                if (resigned < minDate || resigned > maxDate) {
                    $('#date_resigned').val('');
                    $('#date_resigned_display').val('');
                }
            }
        } catch (e) {
            console.warn('Could not update date_resigned picker constraints:', e);
        }
    }
});
</script>
@endpush