@push('scripts')
    
<script>
    document.addEventListener('DOMContentLoaded', function() {
        

        // ── Initialize with existing addresses if provided ──
        const existingAddresses = {!! isset($existingAddresses) ? json_encode($existingAddresses) : '[]' !!};
        if (existingAddresses && existingAddresses.length > 0) {
            savedAddresses = existingAddresses.map(address => ({
                ...address,
                id: address.db_id, // Use db_id as the id for consistency
                is_existing: true,
                is_deleted: false
            }));
        }

        // Display addresses on page load
        displaySavedAddresses();

        // ── Save Address Button ──
        $(document).on('click', '#save-address-btn', function() {
            if (validateAddressForm()) {
                saveAddress();
            }
        });

        // ── Add Address Button ──
        $('#add-address-btn').on('click', function() {
            clearAddressForm();
            $('#address-form').show();
            $('#address-form-buttons').show();
            $('#add-address-btn').hide();
        });

        // ── Cancel Button ──
        $(document).on('click', '#cancel-address-btn', function() {
            clearAddressForm();
            $('#address-form').hide();
            $('#address-form-buttons').hide();
            $('#add-address-btn').show();
        });

        // ══════════════════════════════════════
        // validateAddressForm — validates all required fields
        // ══════════════════════════════════════
        function validateAddressForm() {
            let isValid = true;
            let firstErrorField = null;
            const fields = [
                'address_type', 'address'
            ];

            fields.forEach(field => {
                const $field = $(`#${field}`);
                const $error = $(`#${field}_error`);
                const val = ($field.val() || '').trim();

                if (!val) {
                    $error.text('This field is required').show();
                    isValid = false;
                    if (!firstErrorField) {
                        firstErrorField = $field;
                    }
                } else {
                    $error.hide();
                }
            });

            // Check for duplicate address within the same type
            const currentAddressType = $('#address_type').val();
            const currentAddressId = $('#address').val();
            if (currentAddressType && currentAddressId) {
                const duplicate = savedAddresses.find(a =>
                    !a.is_deleted &&
                    String(a.address_type_id) === String(currentAddressType) &&
                    String(a.address_id) === String(currentAddressId)
                );
                if (duplicate) {
                    $('#address_error')
                        .text('This address already exists for type ' + duplicate.address_type_name)
                        .show();
                    isValid = false;
                    if (!firstErrorField) {
                        firstErrorField = $('#address');
                    }
                }
            }

            // Scroll to first error field
            if (!isValid && firstErrorField) {
                $('html, body').animate({
                    scrollTop: firstErrorField.closest('.form-group, .mb-3, .col').offset().top - 100
                }, 300);
                firstErrorField.focus();
            }

            return isValid;
        }

        // ══════════════════════════════════════
        // clearAddressForm — properly resets all fields
        // ══════════════════════════════════════
        function clearAddressForm() {
            // Clear text inputs
            $('#unit_number').val('');
            $('#complex_name').val('');
            $('#street_number').val('');
            $('#street_name').val('');
            $('#suburb').val('');
            $('#city').val('');
            $('#postal_code').val('');
            $('#province').val('');
            $('#country').val('');
            $('#municipality').val('');
            $('#ward').val('');
            $('#latitude').val('');
            $('#longitude').val('');
            $('#map_url').val('');

            // Clear select dropdowns with selectpicker
            if ($.fn.selectpicker) {
                $('#address').selectpicker('val', '');
                $('#address_type').selectpicker('val', '');
            } else {
                $('#address').val('');
                $('#address_type').val('');
            }

            // Clear default checkbox
            $('#is_default').prop('checked', false);

            // Hide all error tooltips
            $('#address-form .sd_tooltip_red').hide();
        }

        // ══════════════════════════════════════
        // saveAddress — captures ALL fields for NEW addresses only
        // ══════════════════════════════════════
        function saveAddress() {
            const addressId = Date.now(); // Temporary ID for new address
            const isDefault = $('#is_default').is(':checked');

            if (isDefault) {
                savedAddresses = savedAddresses.map(address => ({
                    ...address,
                    is_default: false
                }));
            }

            const addressData = {
                id: addressId,
                address_id: $('#address').val(),
                address_name: $('#address option:selected').text().trim(),
                address_type_id: $('#address_type').val(),
                address_type_name: $('#address_type option:selected').text().trim(),
                is_default: isDefault,
                is_existing: false, // Mark as new address
                is_deleted: false
            };

            savedAddresses.push(addressData);

            displaySavedAddresses();

            const addressModal = document.getElementById('addressModal'); // Replace with your modal ID
            if (addressModal) {
                const modal = bootstrap.Modal.getInstance(addressModal);
                if (modal) {
                    modal.hide();
                }
            }

            clearAddressForm();
            $('#address-form').hide();
            $('#address-form-buttons').hide();
            $('#add-address-btn').show();
            toastr.success('Address added successfully!');
        }

        $('#addressModal').on('shown.bs.modal', function() {
            
            $('#address-form').show();
            $('#address-form-buttons').show();
            $('#add-address-btn').hide();
        });

        $('#addressModal').on('hidden.bs.modal', function() {
            clearAddressForm();
        });

        // ══════════════════════════════════════
        // displaySavedAddresses — renders address cards (read-only for existing, full for new)
        // ══════════════════════════════════════
        function displaySavedAddresses() {
            const $container = $('#saved-address-list').empty();

            if (savedAddresses.length === 0) {
                $container.html(`
                    <div class="empty-state">
                        <i class="fas fa-location-dot"></i>
                        <p>No addresses saved yet. Fill in the form above and click Save Address.</p>
                    </div>
                `);
                return;
            }

            savedAddresses.forEach(address => {
                
                const isDefault = !!address.is_default;

                // Visual feedback for deleted addresses
                const deletedClass = address.is_deleted ? 'opacity-50 text-decoration-line-through' : '';
                const deleteButtonText = address.is_deleted ? '<i class="fa fa-undo"></i> Restore' : '<i class="fa fa-trash"></i>';
                const deleteButtonClass = address.is_deleted ? 'btn-warning' : 'btn-danger';

                const $card = $(
                    `<div class="col-lg-4 mt-3 address-card ${address.is_deleted ? 'marked-for-delete' : ''}" data-address-id="${address.id}">
                        <div class="multi-card shadow-md ${address.is_deleted ? 'opacity-50' : ''}">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card-content">
                                            <h5 class="${address.is_deleted ? 'text-muted' : ''}">${address.address_name}</h5>
                                            <dl class="mb-0">
                                                <dt>Address Type:</dt><dd>${address.address_type_name}</dd>
                                            </dl>
                                            <div class="form-check mt-2">
                                                <input type="radio" class="form-check-input address-default-radio" name="default_address"
                                                    data-address-id="${address.id}" ${isDefault ? 'checked' : ''} ${address.is_deleted ? 'disabled' : ''}>
                                                <label class="form-check-label">Default</label>
                                            </div>
                                        </div>
                                        <button type="button" class="delete-card delete-address ${deleteButtonClass}" title="${address.is_deleted ? 'Restore address' : 'Delete address'}" data-address-id="${address.id}">
                                            ${deleteButtonText}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                $container.append($card);
            });
        }

        // ── Default address toggle ──
        $(document).on('change', '.address-default-radio', function(e) {
            e.stopPropagation();
            const addressId = $(this).data('address-id');
            savedAddresses = savedAddresses.map(address => ({
                ...address,
                is_default: address.id === addressId
            }));
            displaySavedAddresses();
        });

        // ── Delete/Restore address card ──
        $(document).on('click', '.delete-address', function(e) {
            e.stopPropagation();
            const addressId = $(this).data('address-id');
            const address = savedAddresses.find(a => a.id === addressId);
            
            if (address.is_existing) {
                // Toggle is_deleted flag for existing addresses
                address.is_deleted = !address.is_deleted;
                if (address.is_deleted) {
                    deletedAddressIds.push(address.db_id);
                    address.is_default = false;
                    toastr.info('Address marked for deletion');
                } else {
                    deletedAddressIds = deletedAddressIds.filter(id => id !== address.db_id);
                    toastr.info('Address restored');
                }
            } else {
                // Remove new addresses immediately
                if (address.is_default) {
                    address.is_default = false;
                }
                savedAddresses = savedAddresses.filter(a => a.id !== addressId);
                delete addressFiles[addressId];
                toastr.info('Address removed');
            }
            displaySavedAddresses();
        });
    });
</script>

<script>

// function refreshAddressOptions() {
//     debugger
//     var $address = $('#address');
//     var $refreshButton = $('#refresh-addresses-btn');
//     var $refreshIcon = $refreshButton.find('i');

//     $address.html('<option value="">Select Address</option>');

//     $.ajax({
//         url: `{{ route('cimsaddresses.ajax.addresses') }}`,
//         type: 'GET',
//         dataType: 'json',
//         beforeSend: function () {
//             $refreshButton.prop('disabled', true);
//             $refreshIcon.addClass('fa-refresh');
//         },
//         success: function (response) {
//                 // Bank Account Type
//                 var $select = $('#address');
//                 var currentVal = $select.val();
//                 $select.html('<option value="">Select Address</option>');
//                 $.each(response, function(i, addr) {
//                     $select.append(
//                         $('<option>')
//                             .val(addr.value)
//                             .text(addr.label)
//                             .attr('data-description', addr.description || '')
//                     );
//                 });

//             // Restore previous selection if still available
//             if (currentVal) { $select.val(currentVal); }
            
//             // Refresh selectpicker
//             if ($.fn.selectpicker) { 
//                 $select.selectpicker('refresh'); 
//             }
            
//             // ════════════════════════════════════════════════
//             // RE-ATTACH TOOLTIP LISTENERS AFTER REFRESH
//             // ════════════════════════════════════════════════
       

//             // Delay tooltip attachment to ensure dropdown is rendered
//             setTimeout(function() {
//                 $select.next('.dropdown-menu').find('li').each(function() {
//                     var $li = $(this);
//                     var $option = $select.find('option').eq($li.index());
//                     var description = $option.data('description');
                    
//                     if (description) {
//                         $li.off('mouseenter mouseleave');
//                         $li.on('mouseenter', function() {
//                             var $tooltip = $('<div class="sd_tooltip_teal show tooltip_reset">' + description + '</div>');
//                             $li.append($tooltip);
//                         });
//                         $li.on('mouseleave', function() {
//                             $li.find('.sd_tooltip_teal').remove();
//                         });
//                     }
//                 });
//             }, 20);
//         },
//         error: function () {
//             if ($.fn.selectpicker) {
//                 $address.selectpicker('refresh');
//             }
//         },
//         complete: function () {
//             $refreshButton.prop('disabled', false);
//             $refreshIcon.removeClass('fa-spin');
//         }
//     });
// }

function refreshAddressOptions() {
    var $address = $('#address');
    var $refreshButton = $('#refresh-addresses-btn');
    var $refreshIcon = $refreshButton.find('i');
    var spinStartedAt = Date.now();

    $address.html('<option value="">Select Address</option>');

    $.ajax({
        url: `{{ route('cimsaddresses.ajax.addresses') }}`,
        type: 'GET',
        dataType: 'json',
        beforeSend: function () {
            $refreshButton.prop('disabled', true);
            $refreshIcon.addClass('sd-spin');
        },
        success: function (response) {
            var $select = $('#address');
            var currentVal = $select.val();
            $select.html('<option value="">Select Address</option>');

            $.each(response, function(i, addr) {
                $select.append(
                    $('<option>')
                        .val(addr.value)
                        .text(addr.label)
                        .attr('data-description', addr.description || '')
                );
            });

            if (currentVal) {
                $select.val(currentVal);
            }

            if ($.fn.selectpicker) {
                $select.selectpicker('refresh');
            }

            setTimeout(function() {
                $select.next('.dropdown-menu').find('li').each(function() {
                    var $li = $(this);
                    var $option = $select.find('option').eq($li.index());
                    var description = $option.data('description');

                    if (description) {
                        $li.off('mouseenter mouseleave');
                        $li.on('mouseenter', function() {
                            var $tooltip = $('<div class="sd_tooltip_teal show tooltip_reset">' + description + '</div>');
                            $li.append($tooltip);
                        });
                        $li.on('mouseleave', function() {
                            $li.find('.sd_tooltip_teal').remove();
                        });
                    }
                });
            }, 20);
        },
        error: function () {
            if ($.fn.selectpicker) {
                $address.selectpicker('refresh');
            }
        },
        complete: function () {
            var elapsed = Date.now() - spinStartedAt;
            var remaining = Math.max(0, 300 - elapsed);

            setTimeout(function () {
                $refreshButton.prop('disabled', false);
                $refreshIcon.removeClass('sd-spin');
            }, remaining);
        }
    });
}
$(document).on('click', '#refresh-addresses-btn', function (e) {
    e.preventDefault();
    refreshAddressOptions();
});

</script>
@endpush
