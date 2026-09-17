/**
 * Frontend JS for Bangla QR Payment Method WooCommerce Gateway
 */

jQuery(document).ready(function ($) {
    // Safety check: verify parameters exist
    if (typeof oi_banglaqr_params === 'undefined') {
        return;
    }

    var selectedFile = null;
    var selectedBase64 = null;
    var uploadInProgress = false;
    var currentObjectUrl = null;
    var $lastActiveElement = null;

    // Escaping helper
    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function escAttr(str) {
        if (!str) return '';
        return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Get dynamic checkout total from DOM, fallback to localized total
    function getCurrentOrderTotal() {
        var total = '';
        var $domTotal = $('.order-total strong span.woocommerce-Price-amount, .order-total strong, .order-total .amount').first();
        if ($domTotal.length) {
            total = $domTotal.text().trim();
        }
        if (!total) {
            total = oi_banglaqr_params.order_total || '0';
        }
        return total;
    }

    // Update payable amount in modal without rebuilding HTML
    function updateModalAmounts() {
        var total = getCurrentOrderTotal();
        $('#banglaqr-modal-payable-val').text(total);
    }

    // Append modal HTML structure to body
    function buildModalHtml() {
        if ($('#banglaqr-modal').length) {
            return; // Already built
        }

        var activeQr = oi_banglaqr_params.active_qr;
        var themeColor = oi_banglaqr_params.theme_color || '#137833';
        var total = getCurrentOrderTotal();
        var charge = parseFloat(oi_banglaqr_params.payment_charge || '0');

        var html = '<div id="banglaqr-modal" class="banglaqr-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="banglaqr-modal-title">';
        html += '<style>';
        html += '  #banglaqr-modal {';
        html += '    --banglaqr-modal-primary: ' + escAttr(themeColor) + ';';
        html += '    --banglaqr-modal-primary-hover: ' + escAttr(themeColor) + 'dd;';
        html += '    --banglaqr-modal-primary-light: ' + escAttr(themeColor) + '15;';
        html += '    --banglaqr-modal-primary-border: ' + escAttr(themeColor) + '30;';
        html += '  }';
        html += '</style>';
        html += '  <div class="banglaqr-modal-container" role="document">';

        // Header
        html += '    <div class="banglaqr-modal-header">';
        html += '      <div>';
        html += '        <h3 id="banglaqr-modal-title">' + escHtml('Bangla QR Payment') + '</h3>';
        html += '        <p class="banglaqr-modal-subtitle">' + escHtml('Scan QR & submit payment proof') + '</p>';
        html += '      </div>';
        html += '      <div style="display:flex; align-items:center; gap: 12px;">';
        html += '        <div class="banglaqr-countdown-timer" id="banglaqr-countdown-timer">';
        html += '          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
        html += '          <span id="banglaqr-timer-text">15:00</span>';
        html += '        </div>';
        html += '        <button type="button" class="banglaqr-modal-close" id="banglaqr-modal-close-btn" aria-label="Close modal">';
        html += '          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12" /></svg>';
        html += '        </button>';
        html += '      </div>';
        html += '    </div>';

        // Body
        html += '    <div class="banglaqr-modal-body">';
        html += '      <div id="banglaqr-error-banner" class="banglaqr-modal-error" role="alert" aria-live="polite"></div>';

        if (activeQr && activeQr.qr_code_url) {
            html += '      <div class="banglaqr-payable-amount-box">';
            html += '        <div class="banglaqr-payable-label">Payable Amount</div>';
            html += '        <div class="banglaqr-payable-value" id="banglaqr-modal-payable-val">' + escHtml(total) + '</div>';
            if (charge > 0) {
                html += '        <div class="banglaqr-payable-note">(Includes ' + charge + '% bank transaction charge)</div>';
            } else {
                html += '        <div class="banglaqr-payable-note">(No extra charge)</div>';
            }
            html += '      </div>';

            // QR Code Box (Click to zoom/enlarge)
            html += '      <div class="banglaqr-qr-wrapper">';
            html += '        <div class="banglaqr-qr-box is-zoomable" id="banglaqr-qr-box" title="Click to view enlarged QR code" role="button" tabindex="0" aria-label="Enlarge QR Code">';
            html += '          <div class="banglaqr-qr-zoom-badge">';
            html += '            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>';
            html += '          </div>';
            html += '          <img src="' + escAttr(activeQr.qr_code_url) + '" alt="' + escAttr(activeQr.qr_name || 'Bangla QR Code') + '" />';
            html += '          <div class="banglaqr-qr-box-text">Scan Here to Pay</div>';
            html += '        </div>';
            html += '        <div class="banglaqr-qr-zoom-hint-text">Click QR code to view large size</div>';
            html += '      </div>';

            // Payment Page Banner
            if (oi_banglaqr_params.paymentpage_img_url) {
                html += '      <div class="banglaqr-payment-methods-banner">';
                html += '        <img src="' + escAttr(oi_banglaqr_params.paymentpage_img_url) + '" alt="Accepted Payment Methods" />';
                html += '      </div>';
            }

            // Instruction Alert Banner
            html += '      <div class="banglaqr-instruction-banner">';
            html += '        <p class="banglaqr-instruction-text">Scan this QR code using your bank app to make a payment. Then, upload your receipt or enter the Transaction ID below.</p>';
            html += '      </div>';

            if (oi_banglaqr_params.enable_manual_payment === 'yes') {
                var manualAccounts = [
                    { name: 'bKash', number: oi_banglaqr_params.manual_bkash },
                    { name: 'Nagad', number: oi_banglaqr_params.manual_nagad },
                    { name: 'Rocket', number: oi_banglaqr_params.manual_rocket },
                    { name: 'Upay', number: oi_banglaqr_params.manual_upay },
                    { name: 'CellFin', number: oi_banglaqr_params.manual_cellfin }
                ];
                
                var hasManual = false;
                var accountsHtml = '';
                manualAccounts.forEach(function(acc) {
                    if (acc.number && acc.number.trim() !== '') {
                        hasManual = true;
                        var brandClass = 'banglaqr-brand-' + acc.name.toLowerCase();
                        accountsHtml += '        <div class="banglaqr-manual-account-item ' + brandClass + '">';
                        accountsHtml += '          <span class="banglaqr-manual-account-name">' + escHtml(acc.name) + '</span>';
                        accountsHtml += '          <div class="banglaqr-manual-account-number-wrap">';
                        accountsHtml += '            <span class="banglaqr-manual-account-number">' + escHtml(acc.number) + '</span>';
                        accountsHtml += '            <button type="button" class="banglaqr-manual-copy-btn" data-number="' + escAttr(acc.number) + '" title="Copy Number">';
                        accountsHtml += '              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';
                        accountsHtml += '              <span>Copy</span>';
                        accountsHtml += '            </button>';
                        accountsHtml += '          </div>';
                        accountsHtml += '        </div>';
                    }
                });
                
                var manualHtml = '      <div class="banglaqr-trx-toggle-wrap" style="margin-top:15px; margin-bottom: 5px;">';
                manualHtml += '        <button type="button" class="banglaqr-trx-toggle-btn" id="banglaqr-mfs-toggle-btn" aria-expanded="false" aria-controls="banglaqr-mfs-accounts-container">';
                manualHtml += '          <span class="banglaqr-trx-toggle-icon">';
                manualHtml += '            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>';
                manualHtml += '          </span>';
                manualHtml += '          <span class="banglaqr-trx-toggle-text">Pay using mobile banking accounts (bKash, Nagad, etc.)</span>';
                manualHtml += '        </button>';
                manualHtml += '      </div>';
                manualHtml += '      <div class="banglaqr-manual-accounts" id="banglaqr-mfs-accounts-container" style="display:none; margin-top: 10px;">';
                manualHtml += accountsHtml;
                manualHtml += '      </div>';
                
                if (hasManual) {
                    html += manualHtml;
                }
            }
        } else {
            html += '      <div class="banglaqr-modal-error" style="display:block;">';
            html += '        No active QR codes found. Please contact the site administrator.';
            html += '      </div>';
        }

        // Upload Receipt Section
        if (oi_banglaqr_params.receipt_rule !== 'hidden') {
            var receiptLabel = 'Upload Payment Screenshot / Receipt';
            if (oi_banglaqr_params.receipt_rule === 'mandatory') {
                receiptLabel += ' <span style="color:#ef4444;">*</span>';
            }
            html += '      <div class="banglaqr-upload-section">';
            html += '        <label class="banglaqr-upload-label" for="banglaqr-file-input">' + receiptLabel + '</label>';
            html += '        <div id="banglaqr-dropzone" class="banglaqr-dropzone" tabindex="0" role="button" aria-label="Upload payment screenshot">';
            html += '          <svg class="banglaqr-upload-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" /></svg>';
            html += '          <span class="banglaqr-upload-text">Drag & drop receipt here or click to browse</span>';
            html += '          <span class="banglaqr-upload-subtext">Max size: ' + oi_banglaqr_params.text_max_file_size + ' (JPEG, PNG, WEBP, GIF)</span>';
            html += '          <input type="file" id="banglaqr-file-input" style="display:none;" accept="image/jpeg,image/png,image/webp,image/gif" />';
            html += '        </div>';
            html += '        <div id="banglaqr-file-preview-container"></div>';
            html += '      </div>';
        }

        // Transaction ID Section
        if (oi_banglaqr_params.trxid_rule !== 'hidden') {
            var trxIdLabel = 'Payment Transaction ID / TrxID';
            if (oi_banglaqr_params.trxid_rule === 'mandatory') {
                trxIdLabel += ' <span style="color:#ef4444;">*</span>';
            }
            var hideToggle = (oi_banglaqr_params.receipt_rule === 'hidden');
            
            html += '      <div class="banglaqr-trx-section">';
            if (!hideToggle) {
                html += '        <div class="banglaqr-trx-toggle-wrap">';
                html += '          <button type="button" class="banglaqr-trx-toggle-btn" id="banglaqr-trx-toggle-btn" aria-expanded="false" aria-controls="banglaqr-trx-input-container">';
                html += '            <span class="banglaqr-trx-toggle-icon">';
                html += '              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>';
                html += '            </span>';
                html += '            <span class="banglaqr-trx-toggle-text">Or provide your payment Transaction ID instead</span>';
                html += '          </button>';
                html += '        </div>';
            }
            
            var containerStyle = hideToggle ? 'display:block;' : 'display:none;';
            html += '        <div class="banglaqr-trx-input-container" id="banglaqr-trx-input-container" style="' + containerStyle + '">';
            html += '          <label class="banglaqr-trx-label" for="banglaqr-trx-input">' + trxIdLabel + '</label>';
            html += '          <div class="banglaqr-trx-input-box">';
            html += '            <svg class="banglaqr-trx-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>';
            html += '            <input type="text" id="banglaqr-trx-input" class="banglaqr-trx-input" placeholder="e.g. 9K28DF109X or Bank Ref" autocomplete="off" />';
            html += '          </div>';
            html += '          <span class="banglaqr-trx-hint">Enter the Transaction ID or reference number from your receipt.</span>';
            html += '        </div>';
            html += '      </div>';
        }

        html += '    </div>'; // Close modal-body

        // Footer
        html += '    <div class="banglaqr-modal-footer">';
        html += '      <button type="button" class="banglaqr-btn banglaqr-btn-cancel" id="banglaqr-btn-cancel">Cancel</button>';
        html += '      <button type="button" class="banglaqr-btn banglaqr-btn-submit" id="banglaqr-btn-submit">Confirm Payment</button>';
        html += '    </div>';

        html += '  </div>'; // Close modal-container

        // Enlarged QR Lightbox View
        if (activeQr && activeQr.qr_code_url) {
            html += '  <div id="banglaqr-zoom-overlay" class="banglaqr-zoom-overlay" style="display:none;" role="dialog" aria-modal="true" aria-label="Enlarged QR Code">';
            html += '    <div class="banglaqr-zoom-card">';
            html += '      <div class="banglaqr-zoom-header">';
            html += '        <div class="banglaqr-zoom-title-box">';
            html += '          <span class="banglaqr-zoom-badge">' + escHtml(activeQr.qr_name || 'Bangla QR') + '</span>';
            html += '          <h4 class="banglaqr-zoom-title">Scan QR Code</h4>';
            html += '        </div>';
            html += '        <button type="button" class="banglaqr-zoom-close" id="banglaqr-zoom-close-btn" aria-label="Close enlarged QR">';
            html += '          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12" /></svg>';
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

    // Helper for safe copy to clipboard with fallback
    function copyTextToClipboard(text, callback) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                if (callback) callback();
            }).catch(function () {
                fallbackCopyText(text, callback);
            });
        } else {
            fallbackCopyText(text, callback);
        }
    }

    function fallbackCopyText(text, callback) {
        var tempInput = document.createElement('textarea');
        tempInput.value = text;
        tempInput.style.position = 'fixed';
        tempInput.style.left = '-9999px';
        tempInput.style.top = '0';
        document.body.appendChild(tempInput);
        tempInput.focus();
        tempInput.select();
        try {
            document.execCommand('copy');
            if (callback) callback();
        } catch (err) {
            console.error('Fallback copy failed', err);
        }
        document.body.removeChild(tempInput);
    }

    // Bind events
    function setupModalEvents() {
        // Copy to clipboard with fallback
        $('.banglaqr-manual-copy-btn').on('click', function(e) {
            e.preventDefault();
            var btn = $(this);
            var num = String(btn.data('number') || '');
            copyTextToClipboard(num, function() {
                var originalHtml = btn.html();
                btn.html('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Copied</span>');
                btn.addClass('copied');
                setTimeout(function() {
                    btn.html(originalHtml);
                    btn.removeClass('copied');
                }, 2000);
            });
        });

        // Countdown Timer logic is now handled by startTimer() when modal opens

        // TrxID Smart Validation (Alphanumeric only, uppercase)
        $('#banglaqr-trx-input').on('input', function() {
            var val = $(this).val();
            var sanitized = val.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            if (val !== sanitized) {
                $(this).val(sanitized);
            }
        });

        // Cancel/Close modal
        $('#banglaqr-modal-close-btn, #banglaqr-btn-cancel').on('click', function (e) {
            e.preventDefault();
            if (uploadInProgress) return;
            closeModal();
        });

        // Trigger file input click when clicking dropzone
        $('#banglaqr-dropzone').on('click keydown', function (e) {
            if (uploadInProgress) return;
            if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') {
                return;
            }
            if (e.target.id !== 'banglaqr-file-input') {
                e.preventDefault();
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
            $(this).addClass('is-dragover');
        });

        $('#banglaqr-dropzone').on('dragleave', function (e) {
            e.preventDefault();
            $(this).removeClass('is-dragover');
        });

        $('#banglaqr-dropzone').on('drop', function (e) {
            e.preventDefault();
            if (uploadInProgress) return;
            $(this).removeClass('is-dragover');

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
                $btn.removeClass('is-open').attr('aria-expanded', 'false');
            } else {
                $container.slideDown(200, function () {
                    $('#banglaqr-trx-input').focus();
                });
                $btn.addClass('is-open').attr('aria-expanded', 'true');
            }
        });

        // Toggle MFS accounts container
        $('#banglaqr-mfs-toggle-btn').on('click', function (e) {
            e.preventDefault();
            var $container = $('#banglaqr-mfs-accounts-container');
            var $btn = $(this);

            if ($container.is(':visible')) {
                $container.slideUp(180);
                $btn.removeClass('is-open').attr('aria-expanded', 'false');
            } else {
                $container.slideDown(200);
                $btn.addClass('is-open').attr('aria-expanded', 'true');
            }
        });

        // Submit form inside modal
        $('#banglaqr-btn-submit').on('click', function (e) {
            e.preventDefault();
            if (uploadInProgress) return;

            hideError();

            var trxId = $('#banglaqr-trx-input').val() ? $.trim($('#banglaqr-trx-input').val()) : '';

            // Validate required fields based on rules
            if (oi_banglaqr_params.receipt_rule === 'mandatory' && !selectedFile) {
                showError('Please upload your payment receipt screenshot to complete the order.');
                return;
            }

            if (oi_banglaqr_params.trxid_rule === 'mandatory' && !trxId) {
                showError('Please enter your payment Transaction ID to complete the order.');
                return;
            }

            if (oi_banglaqr_params.receipt_rule !== 'hidden' || oi_banglaqr_params.trxid_rule !== 'hidden') {
                if (oi_banglaqr_params.receipt_rule !== 'mandatory' && oi_banglaqr_params.trxid_rule !== 'mandatory') {
                    if (!selectedFile && !trxId) {
                        showError(oi_banglaqr_params.error_no_file);
                        return;
                    }
                }
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

        // Focus trap & ESC key handling
        $(document).on('keydown.banglaqr', function (e) {
            if (e.key === 'Escape') {
                if ($('#banglaqr-zoom-overlay').is(':visible')) {
                    closeQrZoom();
                } else if ($('#banglaqr-modal').hasClass('is-active') && !uploadInProgress) {
                    closeModal();
                }
            } else if (e.key === 'Tab' && $('#banglaqr-modal').hasClass('is-active')) {
                // Focus trap within modal
                var $focusable = $('#banglaqr-modal').find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').filter(':visible');
                if ($focusable.length === 0) return;

                var $first = $focusable.first();
                var $last = $focusable.last();

                if (e.shiftKey) {
                    if (document.activeElement === $first[0]) {
                        e.preventDefault();
                        $last.focus();
                    }
                } else {
                    if (document.activeElement === $last[0]) {
                        e.preventDefault();
                        $first.focus();
                    }
                }
            }
        });
    }

    // QR Zoom Helpers
    function openQrZoom() {
        $('#banglaqr-zoom-overlay').fadeIn(200).addClass('is-active');
        $('#banglaqr-zoom-close-btn').focus();
    }

    function closeQrZoom() {
        $('#banglaqr-zoom-overlay').fadeOut(150).removeClass('is-active');
        $('#banglaqr-qr-box').focus();
    }

    // Image compression utility with aggressive lightweight optimization (guaranteed < 1MB)
    function compressImage(file, callback) {
        var reader = new FileReader();

        reader.onerror = function () {
            handleUploadError(oi_banglaqr_params.error_invalid_file || 'Failed to read image file.');
        };

        reader.onload = function (event) {
            var img = new Image();

            img.onerror = function () {
                handleUploadError(oi_banglaqr_params.error_invalid_file || 'Failed to decode image.');
            };

            img.onload = function () {
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');
                var MAX_DIM = 1000;
                var width = img.width;
                var height = img.height;

                if (width > height) {
                    if (width > MAX_DIM) {
                        height = Math.round(height * (MAX_DIM / width));
                        width = MAX_DIM;
                    }
                } else {
                    if (height > MAX_DIM) {
                        width = Math.round(width * (MAX_DIM / height));
                        height = MAX_DIM;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                // Fill with white background to prevent transparent PNG screenshots from having black background
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, width, height);
                ctx.drawImage(img, 0, 0, width, height);

                function exportBlob(quality) {
                    canvas.toBlob(function (blob) {
                        if (!blob) {
                            handleUploadError('Image processing failed. Please try another image.');
                            return;
                        }

                        // If still larger than 1MB and quality can be reduced, compress further
                        if (blob.size > 1024 * 1024 && quality > 0.4) {
                            exportBlob(quality - 0.15);
                            return;
                        }

                        // Align filename with jpeg output to avoid MIME mismatch rejection in WordPress
                        var baseName = file.name.replace(/\.[^/.]+$/, "");
                        var newFile = new File([blob], baseName + '.jpg', {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });

                        var base64Data = canvas.toDataURL('image/jpeg', quality);
                        callback(newFile, base64Data);
                    }, 'image/jpeg', quality);
                }

                exportBlob(0.65);
            };

            img.src = event.target.result;
        };

        reader.readAsDataURL(file);
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
        compressImage(file, function (compressedFile, base64Data) {
            // Check file size on compressed file against server limits
            if (compressedFile.size > oi_banglaqr_params.max_file_size) {
                showError(oi_banglaqr_params.error_file_too_large);
                return;
            }

            selectedFile = compressedFile;
            selectedBase64 = base64Data;

            // Revoke old object URL if exists to prevent memory leaks
            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
            }
            currentObjectUrl = URL.createObjectURL(compressedFile);
            var sizeInMb = (compressedFile.size / (1024 * 1024)).toFixed(2) + ' MB';

            $('#banglaqr-file-preview-container').html(
                '<div class="banglaqr-file-preview-card">' +
                '  <div class="banglaqr-file-thumbnail" id="banglaqr-file-thumbnail-btn" style="background-image: url(' + currentObjectUrl + '); cursor: pointer;" title="Click to enlarge"></div>' +
                '  <div class="banglaqr-file-info">' +
                '    <div class="banglaqr-file-name" title="' + escAttr(file.name) + '">' + escHtml(file.name) + '</div>' +
                '    <div class="banglaqr-file-size">' + sizeInMb + '</div>' +
                '    <div class="banglaqr-progress-container" style="display:none;">' +
                '      <div class="banglaqr-progress-bar" id="banglaqr-progress-bar"></div>' +
                '    </div>' +
                '  </div>' +
                '  <button type="button" class="banglaqr-remove-file-btn" id="banglaqr-remove-file" aria-label="Remove file">' +
                '    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12" /></svg>' +
                '  </button>' +
                '</div>'
            );

            // Bind delete action
            $('#banglaqr-remove-file').on('click', function (e) {
                e.preventDefault();
                if (uploadInProgress) return;
                resetFileSelector();
            });

            // Bind zoom action
            $('#banglaqr-file-thumbnail-btn').on('click', function (e) {
                e.preventDefault();
                var zoomHtml = '  <div id="banglaqr-receipt-zoom-overlay" class="banglaqr-zoom-overlay is-active" style="display:flex; z-index: 1000000;" role="dialog" aria-modal="true">';
                zoomHtml += '    <div class="banglaqr-zoom-card">';
                zoomHtml += '      <div class="banglaqr-zoom-header">';
                zoomHtml += '        <div class="banglaqr-zoom-title-box"><h4 class="banglaqr-zoom-title">Payment Receipt</h4></div>';
                zoomHtml += '        <button type="button" class="banglaqr-zoom-close" id="banglaqr-receipt-zoom-close-btn">';
                zoomHtml += '          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12" /></svg>';
                zoomHtml += '        </button>';
                zoomHtml += '      </div>';
                zoomHtml += '      <div class="banglaqr-zoom-img-wrap">';
                zoomHtml += '        <img src="' + currentObjectUrl + '" class="banglaqr-zoom-img" />';
                zoomHtml += '      </div>';
                zoomHtml += '      <button type="button" class="banglaqr-zoom-dismiss-btn" id="banglaqr-receipt-zoom-dismiss-btn">Close Full View</button>';
                zoomHtml += '    </div>';
                zoomHtml += '  </div>';

                $('body').append(zoomHtml);

                $('#banglaqr-receipt-zoom-close-btn, #banglaqr-receipt-zoom-dismiss-btn, #banglaqr-receipt-zoom-overlay').on('click', function(e) {
                    if (e.target.id === 'banglaqr-receipt-zoom-overlay' || e.currentTarget.tagName === 'BUTTON') {
                        $('#banglaqr-receipt-zoom-overlay').remove();
                    }
                });
            });
        });
    }

    function resetFileSelector() {
        selectedFile = null;
        selectedBase64 = null;
        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
            currentObjectUrl = null;
        }
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

    var timeLeft = 15 * 60;

    function startTimer() {
        if (window.banglaqrTimerInterval) {
            clearInterval(window.banglaqrTimerInterval);
        }
        
        timeLeft = 15 * 60; // reset to 15 mins
        var timerDisplay = $('#banglaqr-timer-text');
        var timerContainer = $('#banglaqr-countdown-timer');
        
        timerDisplay.text('15:00');
        timerContainer.removeClass('danger');
        $('#banglaqr-btn-submit').prop('disabled', false).css({ 'opacity': '1', 'cursor': 'pointer' });
        $('#banglaqr-file-input').prop('disabled', false);
        $('#banglaqr-trx-input').prop('disabled', false);

        window.banglaqrTimerInterval = setInterval(function() {
            timeLeft--;
            var minutes = Math.floor(timeLeft / 60);
            var seconds = timeLeft % 60;
            
            var displayStr = (minutes < 10 ? '0' + minutes : minutes) + ':' + (seconds < 10 ? '0' + seconds : seconds);
            timerDisplay.text(displayStr);
            
            if (timeLeft <= 60) {
                timerContainer.addClass('danger');
            }
            
            if (timeLeft <= 0) {
                clearInterval(window.banglaqrTimerInterval);
                showError('Session expired. Please refresh the page and try again.');
                $('#banglaqr-btn-submit').prop('disabled', true).css({ 'opacity': '0.5', 'cursor': 'not-allowed' });
                $('#banglaqr-file-input').prop('disabled', true);
                $('#banglaqr-trx-input').prop('disabled', true);
            }
        }, 1000);
    }

    function openModal() {
        $lastActiveElement = document.activeElement;
        buildModalHtml();
        updateModalAmounts();
        startTimer();

        // Show overlay
        $('#banglaqr-modal').addClass('is-active');
        $('body').css('overflow', 'hidden'); // block background scrolling

        // Focus close button
        setTimeout(function () {
            $('#banglaqr-modal-close-btn').focus();
        }, 50);
    }

    function closeModal() {
        if (window.banglaqrTimerInterval) {
            clearInterval(window.banglaqrTimerInterval);
        }

        $('#banglaqr-modal').removeClass('is-active');
        $('body').css('overflow', ''); // restore scroll

        if ($lastActiveElement && $($lastActiveElement).is(':visible')) {
            $($lastActiveElement).focus();
        }
    }

    function playSuccessAnimationAndSubmit() {
        var successHtml = '<div class="banglaqr-success-container">';
        successHtml += '  <div class="banglaqr-success-icon-wrap">';
        successHtml += '    <div class="banglaqr-success-icon">';
        successHtml += '      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        successHtml += '    </div>';
        successHtml += '  </div>';
        successHtml += '  <div class="banglaqr-success-title">Payment Submitted!</div>';
        successHtml += '  <div class="banglaqr-success-subtitle">Please wait while we process your order.</div>';
        successHtml += '</div>';

        $('#banglaqr-modal .banglaqr-modal-container').html(successHtml);

        setTimeout(function() {
            uploadInProgress = false;
            closeModal();
            $('form.checkout').submit();
        }, 2500);
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
        $('#oi_banglaqr_confirmed').val('1');

        // Render preview snippet on checkout page
        var previewMarkup = '<strong>QR Account:</strong> ' + escHtml(activeQrName) + '<br/><strong>Transaction ID:</strong> <span style="font-family:monospace; font-weight:700; color:#0f172a;">' + escHtml(trxId) + '</span> <a href="#" id="banglaqr-change-receipt-btn" style="margin-left: 10px; color: #ef4444; text-decoration: underline; font-weight: 600;">Change</a>';
        $('#banglaqr-selected-qr-preview').html(previewMarkup).show();

        var $submitBtn = $('#banglaqr-btn-submit');
        $('#banglaqr-btn-cancel').prop('disabled', true);
        $submitBtn.prop('disabled', true).addClass('loading').html('<span class="banglaqr-spinner"></span> <span>Placing Order...</span>');

        // Play animation and submit
        playSuccessAnimationAndSubmit();
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

        // Send base64 payload to bypass PHP upload_max_filesize restriction
        if (selectedBase64) {
            formData.append('image_base64', selectedBase64);
            formData.append('image_name', selectedFile ? selectedFile.name : 'receipt.jpg');
        } else if (selectedFile) {
            formData.append('oi_banglaqr_file', selectedFile);
        }

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
                if (response && response.success && response.data) {
                    var activeQrName = 'QR Payment';
                    if (oi_banglaqr_params.active_qr && oi_banglaqr_params.active_qr.qr_name) {
                        activeQrName = oi_banglaqr_params.active_qr.qr_name;
                    }

                    // Read attachment ID robustly (supporting both 'attachment_id' and 'id')
                    var attachmentId = response.data.attachment_id || response.data.id || '';

                    // Store details in checkout fields
                    $('#oi_banglaqr_receipt_id').val(attachmentId);
                    $('#oi_banglaqr_transaction_id').val(trxId || '');
                    $('#oi_banglaqr_selected_qr').val(activeQrName);
                    $('#oi_banglaqr_confirmed').val('1');

                    // Render small success snippet on checkout page
                    var previewMarkup = '<strong>QR Account:</strong> ' + escHtml(activeQrName) + '<br/><strong>Receipt Uploaded:</strong> <a href="' + escAttr(response.data.url) + '" target="_blank" rel="noopener noreferrer" style="color: #137833; font-weight:600;">View Screenshot</a>';
                    if (trxId) {
                        previewMarkup += '<br/><strong>Transaction ID:</strong> <span style="font-family:monospace; font-weight:700; color:#0f172a;">' + escHtml(trxId) + '</span>';
                    }
                    previewMarkup += ' <a href="#" id="banglaqr-change-receipt-btn" style="margin-left: 10px; color: #ef4444; text-decoration: underline; font-weight: 600;">Change</a>';

                    $('#banglaqr-selected-qr-preview').html(previewMarkup).show();

                    $submitBtn.html('<span class="banglaqr-spinner"></span> <span>Placing Order...</span>');

                    playSuccessAnimationAndSubmit();
                } else {
                    handleUploadError(response && response.data && response.data.message ? response.data.message : 'An error occurred while uploading the file.');
                }
            },
            error: function () {
                handleUploadError('Network error. Please check your connection and try again.');
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

            var reqMsg = (oi_banglaqr_params.i18n_required_field || '%s is a required field.').replace('%s', '<strong>' + escHtml(labelText) + '</strong>');

            if ($input.is(':checkbox')) {
                if (!$input.is(':checked')) {
                    errors.push(reqMsg);
                    $row.addClass('woocommerce-invalid');
                }
            } else {
                if (!val || val.trim() === '') {
                    errors.push(reqMsg);
                    $row.addClass('woocommerce-invalid');
                } else {
                    if ($input.attr('type') === 'email' || ($input.attr('name') && $input.attr('name').indexOf('email') !== -1)) {
                        var emailReg = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailReg.test(val)) {
                            var emailMsg = (oi_banglaqr_params.i18n_valid_email || 'Please enter a valid email address for %s.').replace('%s', '<strong>' + escHtml(labelText) + '</strong>');
                            errors.push(emailMsg);
                            $row.addClass('woocommerce-invalid');
                        }
                    }
                }
            }
        });

        // Verify terms and conditions checkbox
        var $terms = $('#terms');
        if ($terms.length && $terms.is(':visible') && !$terms.is(':checked')) {
            errors.push(oi_banglaqr_params.i18n_terms || 'You must accept the terms and conditions.');
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

    // Reset confirmation if customer switches payment method
    $(document).on('change', 'input[name="payment_method"]', function () {
        if ($(this).val() !== oi_banglaqr_params.gateway_id) {
            $('#oi_banglaqr_confirmed').val('0');
        }
    });

    // Intercept checkout submit button
    $(document).on('click', 'form.checkout #place_order', function (e) {
        var activePaymentMethod = $('input[name="payment_method"]:checked').val();

        if (activePaymentMethod === oi_banglaqr_params.gateway_id) {
            var isConfirmed = $('#oi_banglaqr_confirmed').val() === '1';
            var receiptId = $('#oi_banglaqr_receipt_id').val();
            var trxId = $('#oi_banglaqr_transaction_id').val();

            // If confirmed via modal or values are already set, allow standard form submission
            if (isConfirmed || (receiptId && receiptId !== '') || (trxId && trxId !== '')) {
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
        var isConfirmed = $('#oi_banglaqr_confirmed').val() === '1';
        var receiptId = $('#oi_banglaqr_receipt_id').val();
        var trxId = $('#oi_banglaqr_transaction_id').val();

        if (isConfirmed || (receiptId && receiptId !== '') || (trxId && trxId !== '')) {
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
        $('#oi_banglaqr_confirmed').val('0');
        $('#banglaqr-selected-qr-preview').hide().empty();

        openModal();
    });

    // Listen to WooCommerce checkout errors to reset buttons
    $(document.body).on('checkout_error', function () {
        $('#banglaqr-btn-submit').prop('disabled', false).removeClass('loading').html('Confirm Payment');
        $('#banglaqr-btn-cancel, #banglaqr-remove-file').prop('disabled', false);
        $('.banglaqr-progress-container').hide();
    });
});
