@props([
    'name',
    'id' => null,
    'value' => null,
    'label' => null,
    'placeholder' => '',
    'required' => false,
    'class' => '',
    'autocomplete' => 'new-password',
])

@php
    $componentId = $id ?? $name;
    $displayId = $componentId . '_display';
    $safeId = str_replace(['-', '.'], '_', $componentId);
@endphp

<div class="mb-3">
    @if($label)
    <label for="{{ $displayId }}" class="form-label">
        {{ $label }}
        @if($required)<span class="text-danger">*</span>@endif
    </label>
    @endif

    <input type="hidden" id="{{ $componentId }}" name="{{ $name }}" value="{{ old($name, $value) }}">
    <div class="input-group">
        <input
            type="text"
            id="{{ $displayId }}"
            class="form-control {{ $class }}"
            placeholder="{{ $placeholder }}"
            autocomplete="{{ $autocomplete }}"
            spellcheck="false"
            inputmode="text"
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
            style="border-right:none !important; border-radius:8px 0 0 8px !important;"
        >
        <span class="input-group-text" id="{{ $componentId }}_toggle" title="Show/Hide password"
              style="cursor:pointer; background:linear-gradient(135deg, #0d3d56 0%, #1496bb 100%); border:2px solid #17A2B8; border-left:none; color:#fff; font-size:18px; min-width:48px; display:flex; align-items:center; justify-content:center; border-radius:0 8px 8px 0 !important;">
            <i class="fa fa-eye" id="{{ $componentId }}_toggle_icon"></i>
        </span>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    var $display = $('#{{ $displayId }}');
    var $hidden = $('#{{ $componentId }}');

    if (!$display.length || !$hidden.length) {
        return;
    }

    var revealed_{{ $safeId }} = false;

    function mask_{{ $safeId }}(value) {
        if (revealed_{{ $safeId }}) {
            return value || '';
        }
        return value ? '*'.repeat(value.length) : '';
    }

    function setCaret_{{ $safeId }}(el, pos) {
        try {
            el.setSelectionRange(pos, pos);
        } catch (e) {
            // Ignore unsupported browsers.
        }
    }

    function applyChange_{{ $safeId }}(nextValue, caretPos) {
        $hidden.val(nextValue);
        $display.val(mask_{{ $safeId }}(nextValue));
        setCaret_{{ $safeId }}($display.get(0), caretPos);
    }

    function insertText_{{ $safeId }}(text, start, end) {
        var raw = $hidden.val() || '';
        var next = raw.slice(0, start) + text + raw.slice(end);
        applyChange_{{ $safeId }}(next, start + text.length);
    }

    function deleteRange_{{ $safeId }}(start, end, forward) {
        var raw = $hidden.val() || '';
        var next = raw;
        var caret = start;

        if (start === end) {
            if (forward) {
                next = raw.slice(0, start) + raw.slice(end + 1);
                caret = start;
            } else if (start > 0) {
                next = raw.slice(0, start - 1) + raw.slice(end);
                caret = start - 1;
            }
        } else {
            next = raw.slice(0, start) + raw.slice(end);
            caret = start;
        }

        applyChange_{{ $safeId }}(next, caret);
    }

    $display.val(mask_{{ $safeId }}($hidden.val()));

    $('#{{ $componentId }}_toggle').on('click', function() {
        revealed_{{ $safeId }} = !revealed_{{ $safeId }};
        var $icon = $('#{{ $componentId }}_toggle_icon');
        var $btn = $(this);
        if (revealed_{{ $safeId }}) {
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
            $btn.css('background', 'linear-gradient(135deg, #1496bb 0%, #17A2B8 100%)');
            $display.val($hidden.val());
        } else {
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
            $btn.css('background', 'linear-gradient(135deg, #0d3d56 0%, #1496bb 100%)');
            $display.val(mask_{{ $safeId }}($hidden.val()));
        }
    });

    var supportsBeforeInput = 'onbeforeinput' in document.createElement('input');

    if (supportsBeforeInput) {
        $display.on('beforeinput', function(e) {
            var originalEvent = e.originalEvent || {};
            var inputType = originalEvent.inputType || '';
            var data = originalEvent.data || '';
            var start = this.selectionStart || 0;
            var end = this.selectionEnd || 0;

            if (inputType === 'insertText') {
                e.preventDefault();
                insertText_{{ $safeId }}(data, start, end);
                return;
            }

            if (inputType === 'insertFromPaste') {
                var paste = '';
                if (originalEvent.clipboardData && originalEvent.clipboardData.getData) {
                    paste = originalEvent.clipboardData.getData('text');
                } else if (window.clipboardData && window.clipboardData.getData) {
                    paste = window.clipboardData.getData('Text');
                }
                e.preventDefault();
                insertText_{{ $safeId }}(paste, start, end);
                return;
            }

            if (inputType === 'deleteContentBackward') {
                e.preventDefault();
                deleteRange_{{ $safeId }}(start, end, false);
                return;
            }

            if (inputType === 'deleteContentForward') {
                e.preventDefault();
                deleteRange_{{ $safeId }}(start, end, true);
                return;
            }

            if (inputType === 'deleteByCut') {
                e.preventDefault();
                deleteRange_{{ $safeId }}(start, end, false);
            }
        });

        return;
    }

    $display.on('keydown', function(e) {
        var start = this.selectionStart || 0;
        var end = this.selectionEnd || 0;

        if (e.key === 'Backspace') {
            e.preventDefault();
            deleteRange_{{ $safeId }}(start, end, false);
            return;
        }

        if (e.key === 'Delete') {
            e.preventDefault();
            deleteRange_{{ $safeId }}(start, end, true);
            return;
        }

        if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            insertText_{{ $safeId }}(e.key, start, end);
        }
    });

    $display.on('paste', function(e) {
        var clipboard = (e.originalEvent && e.originalEvent.clipboardData) || window.clipboardData;
        var paste = clipboard && clipboard.getData ? clipboard.getData('text') : '';
        var start = this.selectionStart || 0;
        var end = this.selectionEnd || 0;
        e.preventDefault();
        insertText_{{ $safeId }}(paste, start, end);
    });

    $display.on('cut', function(e) {
        var start = this.selectionStart || 0;
        var end = this.selectionEnd || 0;
        e.preventDefault();
        deleteRange_{{ $safeId }}(start, end, false);
    });
});
</script>
@endpush
