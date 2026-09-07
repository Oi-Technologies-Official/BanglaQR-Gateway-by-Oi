/**
 * Admin Panel JS for Bangla QR Payment Method WooCommerce Gateway
 * Accordion Architecture
 */

jQuery(document).ready(function ($) {
    // Safety check
    if (typeof oi_banglaqr_admin_params === 'undefined' || !$('#woocommerce_oi_banglaqr_qrs_table').length) {
        return;
    }

    var $hiddenInput = $('#woocommerce_oi_banglaqr_qrs_table');
    var $accordionWrapper = $('#banglaqr-qr-accordion-wrapper');

    var qrs = [];

    // Parse initial QR accounts data
    try {
        var rawVal = $hiddenInput.val();
        if (rawVal) {
            qrs = JSON.parse(rawVal);
        }
    } catch (e) {
        console.error('Failed to parse QR accounts data: ', e);
    }

    if (!Array.isArray(qrs)) {
        qrs = [];
    }

    // Helper to escape attributes
    function escapeAttr(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Generate HTML for a single accordion row
    function renderAccordionItem(qr, idx) {
        var qrName = qr.qr_name || '';
        var qrCodeUrl = qr.qr_code_url || '';
        var active = (qr.is_active === 'yes');

        var checkedStr = active ? 'checked' : '';
        var isExpandedCls = (idx === 0) ? 'is-expanded' : ''; // Expand the first one by default if multiple
        if (qrs.length === 1) isExpandedCls = 'is-expanded'; // Always expand if only 1

        var logoHtml = qrCodeUrl
            ? '<img src="' + escapeAttr(qrCodeUrl) + '" />'
            : '<span class="dashicons dashicons-qr-code" style="font-size: 18px; width:18px; height:18px; color:#94a3b8;"></span>';

        var qrPreview = qrCodeUrl
            ? '<img src="' + escapeAttr(qrCodeUrl) + '" />'
            : '<span class="dashicons dashicons-qr-code" style="font-size: 24px; width:24px; height:24px; color:#94a3b8;"></span>';

        var statusBadge = active 
            ? '<span class="banglaqr-status-badge status-active"><span class="status-dot"></span> Active</span>'
            : '<span class="banglaqr-status-badge status-inactive"><span class="status-dot"></span> Inactive</span>';

        var html = '';
        html += '<div class="banglaqr-qr-accordion-item ' + isExpandedCls + '" data-index="' + idx + '">';
        
        // Accordion Header
        html += '  <div class="banglaqr-accordion-header">';
        html += '    <div class="banglaqr-sort-handle" title="Drag to reorder"><span class="dashicons dashicons-menu"></span></div>';
        html += '    <div class="banglaqr-accordion-logo">' + logoHtml + '</div>';
        html += '    <div class="banglaqr-accordion-title">' + (qrName ? escapeAttr(qrName) : 'New QR Code') + '</div>';
        html += '    <div class="banglaqr-accordion-actions">';
        html += '      <div class="banglaqr-badge-wrapper">' + statusBadge + '</div>';
        html += '      <span class="dashicons dashicons-arrow-down-alt2 banglaqr-accordion-toggle-icon"></span>';
        html += '    </div>';
        html += '  </div>';

        // Accordion Body
        html += '  <div class="banglaqr-accordion-body">';
        html += '    <div class="banglaqr-accordion-body-inner">';
        
        // Left Column: Scan Preview
        var largePreviewStyle = qrCodeUrl ? 'display: block;' : 'display: none;';
        html += '      <div class="banglaqr-large-qr-preview-wrapper" style="' + largePreviewStyle + '">';
        html += '        <div class="banglaqr-grid-field"><label>Scan Preview</label></div>';
        html += '        <div class="banglaqr-large-qr-preview-box">';
        html += '          <img class="banglaqr-large-qr-preview-img" src="' + escapeAttr(qrCodeUrl) + '" />';
        html += '        </div>';
        html += '        <span style="font-size: 11px; color: #64748b; margin-top: 6px; display: block; line-height: 1.4;">Verify QR works.</span>';
        html += '      </div>';

        // Right Column: Inputs
        html += '      <div class="banglaqr-card-inputs-wrapper">';
        
        // Name
        html += '        <div class="banglaqr-grid-field">';
        html += '          <label>QR Code Name <span class="req">*</span></label>';
        html += '          <input type="text" class="banglaqr-qr-name-input banglaqr-general-input" value="' + escapeAttr(qrName) + '" placeholder="e.g. bKash QR, Nagad QR" />';
        html += '        </div>';

        // Charge
        var qrCharge = qr.payment_charge || '0';
        html += '        <div class="banglaqr-grid-field">';
        html += '          <label>Payment Charge (%)</label>';
        html += '          <input type="text" class="banglaqr-payment-charge-input banglaqr-general-input" value="' + escapeAttr(qrCharge) + '" placeholder="e.g. 2 (leave 0 to disable)" />';
        html += '        </div>';

        // Image URL
        html += '        <div class="banglaqr-grid-field">';
        html += '          <label>QR Code Image <span class="req">*</span></label>';
        html += '          <div class="banglaqr-uploader-inline">';
        html += '            <div class="banglaqr-logo-preview-box banglaqr-qr-preview-box">' + qrPreview + '</div>';
        html += '            <input type="text" class="banglaqr-qr-code-url-input banglaqr-general-input" value="' + escapeAttr(qrCodeUrl) + '" placeholder="Paste image URL or upload" />';
        html += '            <button type="button" class="button button-secondary banglaqr-upload-logo-btn">Upload</button>';
        html += '          </div>';
        html += '        </div>';

        // Bottom Actions (Activate toggle and Delete)
        html += '        <div class="banglaqr-panel-actions">';
        html += '          <div class="banglaqr-toggle-wrapper" style="margin-right: auto;">';
        html += '            <label class="banglaqr-switch">';
        html += '              <input type="checkbox" class="banglaqr-is-active-input" ' + checkedStr + ' />';
        html += '              <span class="banglaqr-slider"></span>';
        html += '            </label>';
        html += '            <span class="banglaqr-toggle-status">' + (active ? 'Set as Active QR' : 'Set as Active QR') + '</span>';
        html += '          </div>';
        html += '          <button type="button" class="banglaqr-delete-row-btn" title="Delete QR Account">';
        html += '            <span class="dashicons dashicons-trash"></span> Delete';
        html += '          </button>';
        html += '        </div>';

        html += '      </div>'; // End Inputs wrapper
        html += '    </div>'; // End Body Inner
        html += '  </div>'; // End Body
        html += '</div>'; // End Item

        return html;
    }

    // Refresh layout in the DOM
    function renderTable() {
        // Save expansion states
        var expandedStates = [];
        $('.banglaqr-qr-accordion-item').each(function() {
            if ($(this).hasClass('is-expanded')) {
                expandedStates.push($(this).data('index'));
            }
        });

        $accordionWrapper.empty();

        if (qrs.length === 0) {
            addQrCard(true);
            return;
        }

        $.each(qrs, function (idx, qr) {
            var html = renderAccordionItem(qr, idx);
            var $item = $(html);
            
            // Restore expansion state if re-rendering
            if (expandedStates.includes(idx)) {
                $item.addClass('is-expanded');
            } else if (expandedStates.length > 0) {
                $item.removeClass('is-expanded');
            }

            $accordionWrapper.append($item);
        });

        var addBtnHtml = '<button type="button" class="banglaqr-btn-add-inline" id="banglaqr-add-qr-row-inline"><span class="dashicons dashicons-plus"></span> Add New QR Account</button>';
        $accordionWrapper.append(addBtnHtml);

        serializeData();
    }

    // Add new card data model
    function addQrCard(isDefaultTest) {
        var makeActive = qrs.length === 0 ? 'yes' : 'no';
        var newQr;

        if (isDefaultTest) {
            newQr = {
                qr_name: 'Test QR',
                qr_code_url: oi_banglaqr_admin_params.default_qr_url || '',
                payment_charge: '1',
                is_active: 'yes'
            };
        } else {
            newQr = {
                qr_name: '',
                qr_code_url: '',
                payment_charge: '0',
                is_active: makeActive
            };
        }

        qrs.push(newQr);
        
        // Collapse all others and expand new one
        $('.banglaqr-qr-accordion-item').removeClass('is-expanded');
        renderTable();
        
        // Ensure new item is expanded
        var newIdx = qrs.length - 1;
        setTimeout(function() {
            $('.banglaqr-qr-accordion-item[data-index="' + newIdx + '"]').addClass('is-expanded');
        }, 50);
    }

    // Read DOM panel inputs and serialize to JSON
    function serializeData() {
        var serialized = [];
        $accordionWrapper.find('.banglaqr-qr-accordion-item').each(function () {
            var $panel = $(this);
            var qr = {
                qr_name: $panel.find('.banglaqr-qr-name-input').val().trim(),
                qr_code_url: $panel.find('.banglaqr-qr-code-url-input').val().trim(),
                payment_charge: $panel.find('.banglaqr-payment-charge-input').val().trim() || '0',
                is_active: $panel.find('.banglaqr-is-active-input').is(':checked') ? 'yes' : 'no'
            };
            serialized.push(qr);
        });
        qrs = serialized;
        $hiddenInput.val(JSON.stringify(serialized));
    }

    // Accordion expand/collapse
    $(document).on('click', '.banglaqr-accordion-header', function (e) {
        if ($(e.target).closest('.banglaqr-sort-handle').length || $(e.target).closest('.banglaqr-badge-wrapper').length) {
            return; // Ignore drag handle or badge clicks
        }
        var $item = $(this).closest('.banglaqr-qr-accordion-item');
        $item.toggleClass('is-expanded');
    });

    // Handle bank logo media uploader frame
    $(document).on('click', '.banglaqr-upload-logo-btn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $panel = $btn.closest('.banglaqr-qr-accordion-item');
        var $previewBox = $panel.find('.banglaqr-qr-preview-box');
        var $urlInput = $panel.find('.banglaqr-qr-code-url-input');

        var fileFrame = wp.media({
            title: oi_banglaqr_admin_params.media_title,
            button: { text: oi_banglaqr_admin_params.media_button_text },
            multiple: false
        });

        fileFrame.on('select', function () {
            var attachment = fileFrame.state().get('selection').first().toJSON();
            var imageUrl = attachment.url;

            $urlInput.val(imageUrl);
            $previewBox.html('<img src="' + escapeAttr(imageUrl) + '" />');

            // Sync to header logo preview
            $panel.find('.banglaqr-accordion-logo').html('<img src="' + escapeAttr(imageUrl) + '" />');

            // Sync and show the large scanner preview
            var $largeWrapper = $panel.find('.banglaqr-large-qr-preview-wrapper');
            $largeWrapper.find('.banglaqr-large-qr-preview-img').attr('src', imageUrl);
            $largeWrapper.show();

            serializeData();
        });

        fileFrame.open();
    });

    // Delete row event
    $(document).on('click', '.banglaqr-delete-row-btn', function (e) {
        e.preventDefault();
        if (confirm(oi_banglaqr_admin_params.confirm_delete)) {
            var $panel = $(this).closest('.banglaqr-qr-accordion-item');
            var idx = parseInt($panel.data('index'), 10);

            qrs.splice(idx, 1);

            // If the deleted QR code was active, default another QR code to active
            var hasActive = false;
            $.each(qrs, function (i, q) {
                if (q.is_active === 'yes') {
                    hasActive = true;
                    return false;
                }
            });
            if (!hasActive && qrs.length > 0) {
                qrs[0].is_active = 'yes';
            }

            renderTable();
        }
    });

    // Add Row Click triggers
    $(document).on('click', '#banglaqr-add-qr-row-inline', function (e) {
        e.preventDefault();
        addQrCard();
    });

    // Dynamic Title Sync to the Tab title
    $(document).on('input', '.banglaqr-qr-name-input', function () {
        var $panel = $(this).closest('.banglaqr-qr-accordion-item');
        var name = $(this).val().trim();
        $panel.find('.banglaqr-accordion-title').text(name ? name : 'New QR Code');
    });

    // Inline URL preview sync
    $(document).on('input', '.banglaqr-qr-code-url-input', function () {
        var $panel = $(this).closest('.banglaqr-qr-accordion-item');
        var url = $(this).val().trim();
        var $previewBox = $panel.find('.banglaqr-qr-preview-box');
        var $tabPreview = $panel.find('.banglaqr-accordion-logo');

        if (url) {
            $previewBox.html('<img src="' + escapeAttr(url) + '" />');
            $tabPreview.html('<img src="' + escapeAttr(url) + '" />');

            // Sync and show the large scanner preview
            var $largeWrapper = $panel.find('.banglaqr-large-qr-preview-wrapper');
            $largeWrapper.find('.banglaqr-large-qr-preview-img').attr('src', url);
            $largeWrapper.show();
        } else {
            $previewBox.html('<span class="dashicons dashicons-qr-code" style="font-size: 24px; width:24px; height:24px; color:#94a3b8;"></span>');
            $tabPreview.html('<span class="dashicons dashicons-qr-code" style="font-size: 18px; width:18px; height:18px; color:#94a3b8;"></span>');

            var $largeWrapper = $panel.find('.banglaqr-large-qr-preview-wrapper');
            $largeWrapper.find('.banglaqr-large-qr-preview-img').attr('src', '');
            $largeWrapper.hide();
        }
    });

    // Enabled/Disable switch changes status text and tab opacity (mutually exclusive active QRs)
    $(document).on('change', '.banglaqr-is-active-input', function () {
        var $panel = $(this).closest('.banglaqr-qr-accordion-item');
        var idx = $panel.data('index');
        var isChecked = $(this).is(':checked');

        if (isChecked) {
            // Uncheck other active check boxes
            $('.banglaqr-is-active-input').each(function () {
                var $other = $(this);
                var $otherPanel = $other.closest('.banglaqr-qr-accordion-item');
                var Oidx = $otherPanel.data('index');
                if (Oidx !== idx) {
                    $other.prop('checked', false);
                    $otherPanel.find('.banglaqr-badge-wrapper').html('<span class="banglaqr-status-badge status-inactive"><span class="status-dot"></span> Inactive</span>');
                }
            });

            $panel.find('.banglaqr-badge-wrapper').html('<span class="banglaqr-status-badge status-active"><span class="status-dot"></span> Active</span>');
        } else {
            $panel.find('.banglaqr-badge-wrapper').html('<span class="banglaqr-status-badge status-inactive"><span class="status-dot"></span> Inactive</span>');
        }

        serializeData();
    });

    // Serialize when textbox values change
    $(document).on('input change', '.banglaqr-qr-accordion-item input[type="text"]', function () {
        serializeData();
    });

    // Enable dragging accordion rows
    if ($.fn.sortable) {
        $accordionWrapper.sortable({
            handle: '.banglaqr-sort-handle',
            placeholder: 'banglaqr-qr-accordion-item ui-sortable-helper',
            items: '.banglaqr-qr-accordion-item',
            axis: 'y',
            update: function () {
                var reordered = [];
                $accordionWrapper.find('.banglaqr-qr-accordion-item').each(function () {
                    var $panel = $(this);
                    var qr = {
                        qr_name: $panel.find('.banglaqr-qr-name-input').val().trim(),
                        qr_code_url: $panel.find('.banglaqr-qr-code-url-input').val().trim(),
                        payment_charge: $panel.find('.banglaqr-payment-charge-input').val().trim() || '0',
                        is_active: $panel.find('.banglaqr-is-active-input').is(':checked') ? 'yes' : 'no'
                    };
                    reordered.push(qr);
                });

                qrs = reordered;
                renderTable();
            }
        });
    }

    // Toggle status text and status badge in settings tab
    $(document).on('change', '#woocommerce_oi_banglaqr_enabled', function () {
        var isChecked = $(this).is(':checked');
        $(this).closest('.banglaqr-toggle-wrapper').find('.banglaqr-toggle-status').text(isChecked ? 'Enabled' : 'Disabled');

        // Update header active badge
        var $badge = $('.banglaqr-status-badge').first();
        if ($badge.closest('.banglaqr-header-actions').length) {
            if (isChecked) {
                $badge.removeClass('status-inactive').addClass('status-active');
                $badge.html('<span class="status-dot"></span> Gateway Active');
            } else {
                $badge.removeClass('status-active').addClass('status-inactive');
                $badge.html('<span class="status-dot"></span> Gateway Inactive');
            }
        }
    });

    // Handle gateway logo media uploader frame
    $(document).on('click', '#banglaqr-upload-gateway-logo-btn', function (e) {
        e.preventDefault();
        var $previewBox = $('#banglaqr-gateway-logo-preview');
        var $urlInput = $('#woocommerce_oi_banglaqr_gateway_logo');

        var fileFrame = wp.media({
            title: 'Select Gateway Logo',
            button: { text: 'Use Logo' },
            multiple: false
        });

        fileFrame.on('select', function () {
            var attachment = fileFrame.state().get('selection').first().toJSON();
            var imageUrl = attachment.url;

            $urlInput.val(imageUrl).trigger('change');
            $previewBox.html('<img src="' + escapeAttr(imageUrl) + '" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 4px;" />');
        });

        fileFrame.open();
    });

    $(document).on('input change', '#woocommerce_oi_banglaqr_gateway_logo', function () {
        var url = $(this).val().trim();
        var $previewBox = $('#banglaqr-gateway-logo-preview');

        if (url) {
            $previewBox.html('<img src="' + escapeAttr(url) + '" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 4px;" />');
        } else {
            $previewBox.html('<span class="dashicons dashicons-image-filter" style="color:#64748b;"></span>');
        }
    });

    // Initial render
    renderTable();
});
