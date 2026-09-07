/**
 * Frontend JS for Bangla QR Payment Method WooCommerce Gateway
 */

jQuery(document).ready(function ($) {
    // Safety check: verify parameters exist
    if (typeof oi_banglaqr_params === 'undefined') {
        return;
    }

    var selectedFile = null;
    var uploadInProgress = false;

    // Escaping helper
    function escHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function escAttr(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Append modal HTML structure to body
    function buildModalHtml() {
        if ($('#banglaqr-modal').length) {
            return; // Already built
        }

        var activeQr = oi_banglaqr_params.active_qr;
        var themeColor = oi_banglaqr_params.theme_color || '#137833';

        var html = '<div id="banglaqr-modal" class="banglaqr-modal-overlay">';
        html += '<style>';
        html += '  #banglaqr-modal {';
        html += '    --risb-primary: ' + escAttr(themeColor) + ';';
        html += '    --risb-primary-hover: ' + escAttr(themeColor) + 'dd;';
        html += '    --risb-primary-light: ' + escAttr(themeColor) + '15;';
        html += '    --risb-primary-border: ' + escAttr(themeColor) + '30;';
        html += '  }';
        html += '</style>';
        html += '  <div class="banglaqr-modal-container">';

        // Header
        html += '    <div class="banglaqr-modal-header">';
        html += '      <div>';
        html += '        <h3>' + escHtml('Bangla QR Payment') + '</h3>';
        html += '        <p class="banglaqr-modal-subtitle">' + escHtml('Scan QR & upload payment proof.') + '</p>';
        html += '      </div>';
        html += '      <button type="button" class="banglaqr-modal-close" id="banglaqr-modal-close-btn" aria-label="Close modal">';
        html += '        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejOin="round"><path d="M18 6L6 18M6 6l12 12" /></svg>';
        html += '      </button>';
        html += '    </div>';

        // Body
        html += '    <div class="banglaqr-modal-body">';
        html += '      <div id="banglaqr-error-banner" class="banglaqr-modal-error"></div>';

        if (activeQr && activeQr.qr_code_url) {
            // Get dynamic checkout total from DOM, fallback to localized total
            var total = '';
            var $domTotal = $('.order-total strong span.woocommerce-Price-amount, .order-total strong, .order-total .amount').first();
            if ($domTotal.length) {
                total = $domTotal.text().trim();
            }
            if (!total) {
                total = oi_banglaqr_params.order_total || '0';
            }
            // Strip decimals/paisa (e.g. .00)
            total = total.replace(/\.\d+(?=\s*\D*$)/, '');

            var charge = parseFloat(oi_banglaqr_params.payment_charge || '0');

            html += '      <div class="banglaqr-payable-amount-box" style="text-align:center; padding: 6px 10px; background-color: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 2px;">';
            html += '        <div style="font-size: 10px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2;">Payable Amount</div>';
            html += '        <div style="font-size: 18px; font-weight: 800; color: #137833; margin: 1px 0; line-height: 1.2;">' + escHtml(total) + '</div>';
            if (charge > 0) {
                html += '        <div style="font-size: 9px; color: #475569; font-weight: 600; line-height: 1.2;">(Includes ' + charge + '% bank charge)</div>';
            } else {
                html += '        <div style="font-size: 9px; color: #475569; font-weight: 600; line-height: 1.2;">(No extra charge)</div>';
            }
            html += '      </div>';

            // QR Code Box (Click to zoom/enlarge)
            html += '      <div class="banglaqr-qr-wrapper">';
            html += '        <div class="banglaqr-qr-box is-zoomable" id="banglaqr-qr-box" title="Click to view enlarged QR code" role="button" tabindex="0">';
            html += '          <div class="banglaqr-qr-zoom-badge">';
            html += '            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejOin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>';
            html += '          </div>';
            html += '          <img src="' + escAttr(activeQr.qr_code_url) + '" alt="' + escAttr(activeQr.qr_name) + '" />';
            html += '          <div class="banglaqr-qr-box-text">Scan Here to Pay</div>';
            html += '        </div>';
            html += '        <div class="banglaqr-qr-zoom-hint-text">Click QR code to view large size</div>';
            html += '      </div>';

            // Payment Page Banner
            html += '      <div class="banglaqr-payment-methods-banner" style="text-align:center; margin: 2px 0;">';
            html += '        <img src="' + escAttr(oi_banglaqr_params.paymentpage_img_url) + '" alt="Accepted Payment Methods" style="max-width:100%; height:auto; display:inline-block; border-radius: 4px;" />';
            html += '      </div>';

            // Instruction Alert Banner
            html += '      <div class="banglaqr-instruction-banner">';
            html += '        <p class="banglaqr-instruction-text">Scan this QR code using your bank or financial app to make payment, then upload your receipt or provide Transaction ID below.</p>';
            html += '      </div>';
        } else {
            html += '      <div class="banglaqr-modal-error" style="display:block;">';
            html += '        No active QR codes found. Please contact the site administrator.';
            html += '      </div>';
        }

        // Upload Receipt Section
        html += '      <div class="banglaqr-upload-section">';
        html += '        <label class="banglaqr-upload-label">' + escHtml('Upload Payment Screenshot / Receipt') + '</label>';
        html += '        <div id="banglaqr-dropzone" class="banglaqr-dropzone">';
        html += '          <svg class="banglaqr-upload-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejOin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" /></svg>';
        html += '          <span class="banglaqr-upload-text">Drag & drop receipt here or click to browse</span>';
        html += '          <span class="banglaqr-upload-subtext">Max size: ' + oi_banglaqr_params.text_max_file_size + ' (JPEG, PNG, WEBP, GIF)</span>';
        html += '          <input type="file" id="banglaqr-file-input" style="display:none;" accept="image/*" />';
        html += '        </div>';
        html += '        <div id="banglaqr-file-preview-container"></div>';
        html += '      </div>';

        // Transaction ID Section (Collapsible option)
        html += '      <div class="banglaqr-trx-section">';
        html += '        <div class="banglaqr-trx-toggle-wrap">';
        html += '          <button type="button" class="banglaqr-trx-toggle-btn" id="banglaqr-trx-toggle-btn">';
        html += '            <span class="banglaqr-trx-toggle-icon">';
        html += '              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejOin="round"><path d="M12 5v14M5 12h14"/></svg>';
        html += '            </span>';
        html += '            <span class="banglaqr-trx-toggle-text">Or provide your payment Transaction ID instead</span>';
        html += '          </button>';
        html += '        </div>';
        html += '        <div class="banglaqr-trx-input-container" id="banglaqr-trx-input-container" style="display:none;">';
        html += '          <label class="banglaqr-trx-label" for="banglaqr-trx-input">Payment Transaction ID / TrxID</label>';
        html += '          <div class="banglaqr-trx-input-box">';
        html += '            <svg class="banglaqr-trx-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejOin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>';
        html += '            <input type="text" id="banglaqr-trx-input" class="banglaqr-trx-input" placeholder="e.g. 9K28DF109X or Bank Ref" autocomplete="off" />';
        html += '          </div>';
        html += '          <span class="banglaqr-trx-hint">Enter the Transaction ID or reference from your bank/MFS payment receipt.</span>';
        html += '        </div>';
        html += '      </div>';

        html += '    </div>'; // Close modal-body

        // Footer
        html += '    <div class="banglaqr-modal-footer">';
        html += '      <button type="button" class="banglaqr-btn banglaqr-btn-cancel" id="banglaqr-btn-cancel">Cancel</button>';
        html += '      <button type="button" class="banglaqr-btn banglaqr-btn-submit" id="banglaqr-btn-submit">Confirm Payment</button>';
        html += '    </div>';

        html += '  </div>'; // Close modal-container

        // Enlarged QR Lightbox View
        if (activeQr && activeQr.qr_code_url) {
            html += '  <div id="banglaqr-zoom-overlay" class="banglaqr-zoom-overlay" style="display:none;">';
            html += '    <div class="banglaqr-zoom-card">';
            html += '      <div class="banglaqr-zoom-header">';
            html += '        <div class="banglaqr-zoom-title-box">';
            html += '          <span class="banglaqr-zoom-badge">' + escHtml(activeQr.qr_name || 'Bangla QR') + '</span>';
            html += '          <h4 class="banglaqr-zoom-title">Scan QR Code</h4>';
            html += '        </div>';
            html += '        <button type="button" class="banglaqr-zoom-close" id="banglaqr-zoom-close-btn" aria-label="Close enlarged QR">';
            html += '          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejOin="round"><path d="M18 6L6 18M6 6l12 12" /></svg>';
            html += '        </button>';
            html += '      </div>';
            html += '      <div class="banglaqr-zoom-img-wrap">';
            html += '        <img src="' + escAttr(activeQr.qr_code_url) + '" alt="' + escAttr(activeQr.qr_name) + '" class="banglaqr-zoom-img" />';
            html += '      </div>';
            html += '      <div class="banglaqr-zoom-footer-note">Scan this QR code using your bank or financial app to make payment.</div>';
            html += '      <button type="button" class="banglaqr-zoom-dismiss-btn" id="banglaqr-zoom-dismiss-btn">Close Full View</button>';
            html += '    </div>';
            html += '  </div>';
        }

        html += '</div>'; // Close modal-overlay

        $('body').append(html);
        setupModalEvents();
    }

    // Bind events
    function setupModalEvents() {
        // Cancel/Close modal
        $('#banglaqr-modal-close-btn, #banglaqr-btn-cancel').on('click', function (e) {
            e.preventDefault();
            if (uploadInProgress) return;
            closeModal();
        });

        // Trigger file input click when clicking dropzone
        $('#banglaqr-dropzone').on('click', function (e) {
            if (uploadInProgress) return;
            if (e.target.id !== 'banglaqr-file-input') {
                $('#banglaqr-file-input').click();
            }
        });

        // Handle file change
        $('#banglaqr-file-input').on('change', function (e) {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        // Drag and drop events
        $('#banglaqr-dropzone').on('dragover', function (e) {
            e.preventDefault();
            if (uploadInProgress) return;
            $(this).addClass('risb-dragover');
        });

        $('#banglaqr-dropzone').on('dragleave', function (e) {
            e.preventDefault();
            $(this).removeClass('risb-dragover');
        });

        $('#banglaqr-dropzone').on('drop', function (e) {
            e.preventDefault();
            if (uploadInProgress) return;
            $(this).removeClass('risb-dragover');

            var files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });

        // Open QR Zoom Lightbox
        $('#banglaqr-qr-box').on('click keydown', function (e) {
            if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') {
                return;
            }
            e.preventDefault();
            openQrZoom();
        });

        // Close QR Zoom Lightbox
        $('#banglaqr-zoom-close-btn, #banglaqr-zoom-dismiss-btn').on('click', function (e) {
            e.preventDefault();
            closeQrZoom();
        });

        $('#banglaqr-zoom-overlay').on('click', function (e) {
            if ($(e.target).closest('.banglaqr-zoom-card').length === 0) {
                closeQrZoom();
            }
        });

        // Toggle Transaction ID input field
        $('#banglaqr-trx-toggle-btn').on('click', function (e) {
            e.preventDefault();
            var $container = $('#banglaqr-trx-input-container');
            var $btn = $(this);

            if ($container.is(':visible')) {
                $container.slideUp(180);
                $btn.removeClass('is-open');
            } else {
                $container.slideDown(200, function () {
                    $('#banglaqr-trx-input').focus();
                });
                $btn.addClass('is-open');
            }
        });

        // Submit form
        $('#banglaqr-btn-submit').on('click', function (e) {
            e.preventDefault();
            if (uploadInProgress) return;

            hideError();

            var trxId = $('#banglaqr-trx-input').val() ? $.trim($('#banglaqr-trx-input').val()) : '';

            // User must provide at least one proof: File OR Transaction ID
            if (!selectedFile && !trxId) {
                showError(oi_banglaqr_params.error_no_file);
                return;
            }

            // If file is selected, validate it
            if (selectedFile) {
                if (selectedFile.size > oi_banglaqr_params.max_file_size) {
                    showError(oi_banglaqr_params.error_file_too_large);
                    return;
                }

                if (!selectedFile.type.match('image.*')) {
                    showError(oi_banglaqr_params.error_invalid_file);
                    return;
                }

                uploadFile(trxId);
            } else {
                // Transaction ID only (no file attached)
                submitWithTrxIdOnly(trxId);
            }
        });

        // Listen for ESC key to close zoom or modal
        $(document).on('keydown.banglaqr', function (e) {
            if (e.key === 'Escape') {
                if ($('#banglaqr-zoom-overlay').is(':visible')) {
                    closeQrZoom();
                } else if ($('#banglaqr-modal').hasClass('risb-active') && !uploadInProgress) {
                    closeModal();
                }
            }
        });
    }

    // QR Zoom Helpers
    function openQrZoom() {
        $('#banglaqr-zoom-overlay').fadeIn(200).addClass('is-active');
    }

    function closeQrZoom() {
        $('#banglaqr-zoom-overlay').fadeOut(150).removeClass('is-active');
    }

    // Image compression utility
    function compressImage(file, callback) {
        var reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function(event) {
            var img = new Image();
            img.src = event.target.result;
            img.onload = function() {
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');
                var MAX_WIDTH = 1200;
                var width = img.width;
                var height = img.height;

                if (width > MAX_WIDTH) {
                    height = height * (MAX_WIDTH / width);
                    width = MAX_WIDTH;
                }
                
                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob(function(blob) {
                    var newFile = new File([blob], file.name, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    callback(newFile);
                }, 'image/jpeg', 0.7);
            };
        };
    }

    // Process file validation and rendering previews
    function handleFileSelect(file) {
        hideError();

        // Check file type
        if (!file.type.match('image.*')) {
            showError(oi_banglaqr_params.error_invalid_file);
            return;
        }

        // Compress image before proceeding
        compressImage(file, function(compressedFile) {
            // Check file size on compressed file against server limits
            if (compressedFile.size > oi_banglaqr_params.max_file_size) {
                showError(oi_banglaqr_params.error_file_too_large);
                return;
            }

            selectedFile = compressedFile;

            // Render preview card
            var objectUrl = URL.createObjectURL(compressedFile);
            var sizeInMb = (compressedFile.size / (1024 * 1024)).toFixed(2) + ' MB';

            $('#banglaqr-file-preview-container').html(
                '<div class="banglaqr-file-preview-card">' +
                '  <div class="banglaqr-file-thumbnail" style="background-image: url(' + objectUrl + ')"></div>' +
                '  <div class="banglaqr-file-info">' +
                '    <div class="banglaqr-file-name" title="' + escAttr(file.name) + '">' + escHtml(file.name) + '</div>' +
                '    <div class="banglaqr-file-size">' + sizeInMb + '</div>' +
                '    <div class="banglaqr-progress-container" style="display:none;">' +
                '      <div class="banglaqr-progress-bar" id="banglaqr-progress-bar"></div>' +
                '    </div>' +
                '  </div>' +
                '  <button type="button" class="banglaqr-remove-file-btn" id="banglaqr-remove-file" aria-label="Remove file">' +
                '    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejOin="round"><path d="M18 6L6 18M6 6l12 12" /></svg>' +
                '  </button>' +
                '</div>'
            );

            // Bind delete action
            $('#banglaqr-remove-file').on('click', function (e) {
                e.preventDefault();
                if (uploadInProgress) return;
                resetFileSelector();
            });
        });
    }

    function resetFileSelector() {
        selectedFile = null;
        $('#banglaqr-file-input').val('');
        $('#banglaqr-file-preview-container').empty();
        hideError();
    }

    function showError(msg) {
        var $banner = $('#banglaqr-error-banner');
        $banner.text(msg).fadeIn(200).addClass('banglaqr-shake');
        setTimeout(function () {
            $banner.removeClass('banglaqr-shake');
        }, 400);
    }

    function hideError() {
        $('#banglaqr-error-banner').fadeOut(100).removeClass('banglaqr-shake').empty();
    }

    function openModal() {
        $('#banglaqr-modal').remove(); // Clear old structure to regenerate dynamic total and inputs
        buildModalHtml();
        resetFileSelector();

        // Show overlay and fade-in
        $('#banglaqr-modal').addClass('risb-active');
        $('body').css('overflow', 'hidden'); // block page scrolling
    }

    function closeModal() {
        $('#banglaqr-modal').removeClass('risb-active');
        $('body').css('overflow', ''); // restore scroll
    }

    // Submit with Transaction ID Only (No Image Upload)
    function submitWithTrxIdOnly(trxId) {
        uploadInProgress = true;

        var activeQrName = 'QR Payment';
        if (oi_banglaqr_params.active_qr && oi_banglaqr_params.active_qr.qr_name) {
            activeQrName = oi_banglaqr_params.active_qr.qr_name;
        }

        // Store metadata in hidden checkout inputs
        $('#oi_banglaqr_receipt_id').val('');
        $('#oi_banglaqr_transaction_id').val(trxId);
        $('#oi_banglaqr_selected_qr').val(activeQrName);

        // Render preview snippet on checkout page
        var previewMarkup = '<strong>QR Account:</strong> ' + escHtml(activeQrName) + '<br/><strong>Transaction ID:</strong> <span style="font-family:monospace; font-weight:700; color:#0f172a;">' + escHtml(trxId) + '</span> <a href="#" id="banglaqr-change-receipt-btn" style="margin-left: 10px; color: #ef4444; text-decoration: underline; font-weight: 600;">Change</a>';
        $('#banglaqr-selected-qr-preview').html(previewMarkup).show();

        var $submitBtn = $('#banglaqr-btn-submit');
        $('#banglaqr-btn-cancel').prop('disabled', true);
        $submitBtn.prop('disabled', true).addClass('loading').html('<span class="banglaqr-spinner"></span> <span>Placing Order...</span>');

        // Submit checkout form
        uploadInProgress = false;
        $('form.checkout').submit();
    }

    // AJAX Upload and Compress (With optional TrxID)
    function uploadFile(trxId) {
        if (!selectedFile) return;

        uploadInProgress = true;

        // Show progress bar
        $('.banglaqr-progress-container').show();
        $('#banglaqr-progress-bar').css('width', '0%');

        // Disable buttons
        $('#banglaqr-btn-cancel, #banglaqr-remove-file').prop('disabled', true);
        var $submitBtn = $('#banglaqr-btn-submit');
        $submitBtn.prop('disabled', true).addClass('loading');
        $submitBtn.html('<span class="banglaqr-spinner"></span> <span>Uploading Receipt...</span>');

        // Build FormData
        var formData = new FormData();
        formData.append('action', 'oi_banglaqr_upload_slip');
        formData.append('nonce', oi_banglaqr_params.upload_nonce);
        formData.append('oi_banglaqr_file', selectedFile);

        $.ajax({
            url: oi_banglaqr_params.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            var percentage = Math.round((e.loaded / e.total) * 100);
                            $('#banglaqr-progress-bar').css('width', percentage + '%');
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function (response) {
                if (response.success) {
                    var activeQrName = 'QR Payment';
                    if (oi_banglaqr_params.active_qr && oi_banglaqr_params.active_qr.qr_name) {
                        activeQrName = oi_banglaqr_params.active_qr.qr_name;
                    }

                    // Store details in checkout fields
                    $('#oi_banglaqr_receipt_id').val(response.data.attachment_id);
                    $('#oi_banglaqr_transaction_id').val(trxId || '');
                    $('#oi_banglaqr_selected_qr').val(activeQrName);

                    // Render small success snippet
                    var previewMarkup = '<strong>QR Account:</strong> ' + escHtml(activeQrName) + '<br/><strong>Receipt Uploaded:</strong> <a href="' + escAttr(response.data.url) + '" target="_blank" style="color: #137833; font-weight:600;">View Screenshot</a>';
                    if (trxId) {
                        previewMarkup += '<br/><strong>Transaction ID:</strong> <span style="font-family:monospace; font-weight:700; color:#0f172a;">' + escHtml(trxId) + '</span>';
                    }
                    previewMarkup += ' <a href="#" id="banglaqr-change-receipt-btn" style="margin-left: 10px; color: #ef4444; text-decoration: underline; font-weight: 600;">Change</a>';

                    $('#banglaqr-selected-qr-preview').html(previewMarkup).show();

                    $submitBtn.html('<span class="banglaqr-spinner"></span> <span>Placing Order...</span>');

                    // Submit checkout form
                    uploadInProgress = false;
                    $('form.checkout').submit();
                } else {
                    handleUploadError(response.data ? response.data.message : 'An error occurred during file upload.');
                }
            },
            error: function () {
                handleUploadError('Network error or server unavailable. Please try again.');
            }
        });
    }

    function handleUploadError(errMsg) {
        uploadInProgress = false;
        $('.banglaqr-progress-container').hide();
        $('#banglaqr-progress-bar').css('width', '0%');

        // Re-enable actions
        $('#banglaqr-btn-cancel, #banglaqr-remove-file').prop('disabled', false);
        var $submitBtn = $('#banglaqr-btn-submit');
        $submitBtn.prop('disabled', false).removeClass('loading');
        $submitBtn.html('Confirm Payment');

        showError(errMsg);
    }

    // Validate checkout required fields before triggering popup
    function validateCheckoutForm() {
        var errors = [];

        $('.woocommerce-NoticeGroup-checkout, .woocommerce-error').remove();
        $('form.checkout .woocommerce-invalid').removeClass('woocommerce-invalid');

        // Loop through WooCommerce required fields
        $('form.checkout .validate-required').each(function () {
            var $row = $(this);
            if (!$row.is(':visible')) {
                return;
            }

            var $input = $row.find('input, select, textarea');
            if ($input.length === 0) {
                return;
            }

            var val = $input.val();
            var labelText = $row.find('label').text().replace(/\*/g, '').trim();
            if (!labelText) {
                labelText = $input.attr('placeholder') || $input.attr('name') || 'Required field';
            }

            if ($input.is(':checkbox')) {
                if (!$input.is(':checked')) {
                    errors.push('<strong>' + escHtml(labelText) + '</strong> is a required field.');
                    $row.addClass('woocommerce-invalid');
                }
            } else {
                if (!val || val.trim() === '') {
                    errors.push('<strong>' + escHtml(labelText) + '</strong> is a required field.');
                    $row.addClass('woocommerce-invalid');
                } else {
                    if ($input.attr('type') === 'email' || ($input.attr('name') && $input.attr('name').indexOf('email') !== -1)) {
                        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
                        if (!emailReg.test(val)) {
                            errors.push('Please enter a valid email address for <strong>' + escHtml(labelText) + '</strong>.');
                            $row.addClass('woocommerce-invalid');
                        }
                    }
                }
            }
        });

        // Verify terms and conditions checkbox
        var $terms = $('#terms');
        if ($terms.length && $terms.is(':visible') && !$terms.is(':checked')) {
            errors.push('You must accept the terms and conditions.');
            $terms.closest('p').addClass('woocommerce-invalid');
        }

        if (errors.length > 0) {
            var errorHtml = '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">';
            errorHtml += '  <ul class="woocommerce-error" role="alert">';
            $.each(errors, function (idx, err) {
                errorHtml += '    <li>' + err + '</li>';
            });
            errorHtml += '  </ul>';
            errorHtml += '</div>';

            var $form = $('form.checkout');
            $form.prepend(errorHtml);

            $('html, body').animate({
                scrollTop: ($form.offset().top - 100)
            }, 300);

            return false;
        }

        return true;
    }

    // Intercept checkout submit button
    $(document).on('click', 'form.checkout #place_order', function (e) {
        var activePaymentMethod = $('input[name="payment_method"]:checked').val();

        if (activePaymentMethod === oi_banglaqr_params.gateway_id) {
            var receiptId = $('#oi_banglaqr_receipt_id').val();
            var trxId = $('#oi_banglaqr_transaction_id').val();

            // If either receipt image or transaction ID is present, allow standard form submission
            if ((receiptId && receiptId !== '') || (trxId && trxId !== '')) {
                return true;
            }

            if (!validateCheckoutForm()) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            e.preventDefault();
            e.stopPropagation();

            openModal();
            return false;
        }
    });

    // In case WooCommerce triggers submission via event
    $('form.checkout').on('checkout_place_order_' + oi_banglaqr_params.gateway_id, function () {
        var receiptId = $('#oi_banglaqr_receipt_id').val();
        var trxId = $('#oi_banglaqr_transaction_id').val();

        if ((receiptId && receiptId !== '') || (trxId && trxId !== '')) {
            return true;
        }

        if (!validateCheckoutForm()) {
            return false;
        }

        openModal();
        return false;
    });

    // Listen to "Change" receipt/TrxID selection link on checkout page
    $(document).on('click', '#banglaqr-change-receipt-btn', function (e) {
        e.preventDefault();
        $('#oi_banglaqr_receipt_id').val('');
        $('#oi_banglaqr_transaction_id').val('');
        $('#oi_banglaqr_selected_qr').val('');
        $('#banglaqr-selected-qr-preview').hide().empty();

        openModal();
    });

    // Listen to WooCommerce checkout errors to close the modal
    $(document.body).on('checkout_error', function () {
        closeModal();
        $('#banglaqr-btn-submit').prop('disabled', false).removeClass('loading').html('Confirm Payment');
        $('#banglaqr-btn-cancel, #banglaqr-remove-file').prop('disabled', false);
        $('.banglaqr-progress-container').hide();
    });
});
