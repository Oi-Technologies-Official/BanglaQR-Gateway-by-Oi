<?php
/**
 * Gateway class for Oi_BanglaQR
 */

defined('ABSPATH') || exit;

class Oi_BanglaQR_Gateway extends WC_Payment_Gateway
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->id = 'oi_banglaqr';
        $this->has_fields = true;
        $this->method_title = __('Bangla QR Payment', 'banglaqr-payment-gateway-by-oi');
        $this->method_description = __('Receive payments through Bangladeshi Bank/Mobile QR codes (Bkash, Nagad, Rocket, Bank QR, etc.).', 'banglaqr-payment-gateway-by-oi');

        // Load settings form fields and settings
        $this->init_form_fields();
        $this->init_settings();

        // Get option values
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');

        // Set the gateway icon URL for WooCommerce admin payments list
        $logo_url = $this->get_option('gateway_logo', '');
        $this->icon = $logo_url ? $logo_url : OI_BANGLAQR_URL . 'includes/img/banglaqrlogo.png';

        // Action hooks
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_checkout_assets'));
        add_action('woocommerce_checkout_process', array($this, 'validate_checkout_fields'));

        // AJAX hooks for slip upload
        add_action('wp_ajax_oi_banglaqr_upload_slip', array($this, 'ajax_upload_slip'));
        add_action('wp_ajax_nopriv_oi_banglaqr_upload_slip', array($this, 'ajax_upload_slip'));
    }

    /**
     * Get gateway ID.
     *
     * @return string
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Define the gateway settings fields.
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'checkbox',
                'label' => __('Enable Bangla QR Payment Gateway', 'banglaqr-payment-gateway-by-oi'),
                'default' => 'no',
            ),
            'title' => array(
                'title' => __('Title', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'text',
                'description' => __('This controls the payment method title which the user sees during checkout.', 'banglaqr-payment-gateway-by-oi'),
                'default' => __('Bangla QR Payment', 'banglaqr-payment-gateway-by-oi'),
                'desc_tip' => true,
            ),
            'gateway_logo' => array(
                'title' => __('Gateway QR', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'text',
                'description' => __('Upload a custom QR to show next to the title on checkout.', 'banglaqr-payment-gateway-by-oi'),
                'default' => OI_BANGLAQR_URL . 'includes/img/banglaqrlogo.png',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Description', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'banglaqr-payment-gateway-by-oi'),
                'default' => __('Scan the Bangla QR code through your bank or mobile financial services app (Bkash, Nagad, Rocket, etc.) to complete payment.', 'banglaqr-payment-gateway-by-oi'),
                'desc_tip' => true,
            ),
            'qrs_table' => array(
                'title' => __('QR Configuration', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'qrs_table',
                'description' => __('Add, configure, and set active QR codes.', 'banglaqr-payment-gateway-by-oi'),
                'default' => array(
                    array(
                        'qr_name' => 'Test QR',
                        'qr_code_url' => OI_BANGLAQR_URL . 'includes/img/testqr.png',
                        'payment_charge' => '1',
                        'is_active' => 'yes',
                    )
                ),
            ),
            'theme_color' => array(
                'title' => __('Theme Color', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'color',
                'description' => __('Primary color for checkout popup buttons.', 'banglaqr-payment-gateway-by-oi'),
                'default' => '#137833',
                'desc_tip' => true,
            ),
        );
    }

    /**
     * Render the settings page with a custom tabbed dashboard.
     */
    public function admin_options()
    {
        wp_enqueue_media();

        $enabled = $this->get_option('enabled', 'no');
        $title = $this->get_option('title', 'Bangla QR Payment');
        $gateway_logo = $this->get_option('gateway_logo');
        if (!$gateway_logo) {
            $gateway_logo = OI_BANGLAQR_URL . 'includes/img/banglaqrlogo.png';
        }
        $description = $this->get_option('description', '');
        $theme_color = $this->get_option('theme_color', '#137833');
        $qrs = $this->get_option('qrs_table', array());

        if (!is_array($qrs) || empty($qrs)) {
            $qrs = array(
                array(
                    'qr_name' => 'Test QR',
                    'qr_code_url' => OI_BANGLAQR_URL . 'includes/img/testqr.png',
                    'payment_charge' => '1',
                    'is_active' => 'yes',
                )
            );
        }
        ?>
                <div class="banglaqr-admin-dashboard-wrap">
                    <!-- Header Section -->
                    <div class="banglaqr-dashboard-header">
                        <div class="banglaqr-header-info">
                            <div class="banglaqr-logo-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                    stroke="currentColor" style="width:24px; height:24px;">
                                    <path stroke-linecap="round" stroke-linejOin="round"
                                        d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                    <path stroke-linecap="round" stroke-linejOin="round"
                                        d="M15 15h.008v.008H15V15zm0 2.25h.008v.008H15v-.008zM17.25 15h.008v.008H17.25V15zm0 2.25h.008v.008H17.25v-.008zm-2.25 2.25h.008v.008H15v-.008zm2.25 0h.008v.008H17.25v-.008zM19.5 15h.008v.008H19.5V15zm0 2.25h.008v.008H19.5v-.008zm-2.25-4.5h.008v.008H17.25v-.008zm2.25 0h.008v.008H19.5v-.008z" />
                                </svg>
                            </div>
                            <div>
                                <h1
                                    style="margin:0 !important; font-size: 20px !important; font-weight: 700 !important; color:#0f172a !important; line-height: 1.3 !important; padding:0 !important;">
                                    <?php esc_html_e('BanglaQR Settings', 'banglaqr-payment-gateway-by-oi'); ?></h1>
                                <p style="margin:4px 0 0 0 !important; color:#64748b; font-size: 13px !important;">
                                    <?php esc_html_e('Configure gateway options and manage customer upload receipts.', 'banglaqr-payment-gateway-by-oi'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="banglaqr-header-actions">
                            <span class="banglaqr-status-badge <?php echo $enabled === 'yes' ? 'status-active' : 'status-inactive'; ?>">
                                <span class="status-dot"></span>
                                <?php echo $enabled === 'yes' ? esc_html__('Gateway Active', 'banglaqr-payment-gateway-by-oi') : esc_html__('Gateway Inactive', 'banglaqr-payment-gateway-by-oi'); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Unified Settings Sections -->
                    <div class="banglaqr-settings-panel" style="display: block; padding-top: 0;">
                        
                        <!-- General Settings Section -->
                        <div class="banglaqr-settings-section">
                            <div class="banglaqr-section-header">
                                <h2><?php esc_html_e('General Gateway Settings', 'banglaqr-payment-gateway-by-oi'); ?></h2>
                                <p><?php esc_html_e('Configure core gateway titles, logo, and active status.', 'banglaqr-payment-gateway-by-oi'); ?></p>
                            </div>
                            <div class="banglaqr-section-body" style="display: flex; gap: 24px;">
                                
                                <!-- Left Column: Gateway Enable and Logo Settings -->
                                <div class="banglaqr-card-inputs-wrapper" style="width: 320px; flex-shrink: 0; display: flex; flex-direction: column; gap: 16px;">
                                    
                                    <!-- Enable/Disable Gateway -->
                                    <div class="banglaqr-form-field-row" style="margin-bottom: 0; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9; display: flex; gap: 16px; align-items: center;">
                                        <div class="banglaqr-toggle-wrapper">
                                            <label class="banglaqr-switch">
                                                <input type="checkbox" name="woocommerce_oi_banglaqr_enabled" id="woocommerce_oi_banglaqr_enabled" value="yes" <?php checked($enabled, 'yes'); ?> />
                                                <span class="banglaqr-slider"></span>
                                            </label>
                                            <span class="banglaqr-toggle-status"><?php echo $enabled === 'yes' ? 'Enabled' : 'Disabled'; ?></span>
                                        </div>
                                        <div class="banglaqr-toggle-desc" style="display: flex; flex-direction: column;">
                                            <label for="woocommerce_oi_banglaqr_enabled" class="banglaqr-field-title-label" style="font-weight: 600; font-size: 13px; color: var(--banglaqr-text);"><?php esc_html_e('Enable Gateway', 'banglaqr-payment-gateway-by-oi'); ?></label>
                                            <p style="margin:2px 0 0 0; color:#64748b; font-size:12px;">
                                                <?php esc_html_e('Show gateway on checkout.', 'banglaqr-payment-gateway-by-oi'); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Gateway QR URL -->
                                    <div class="banglaqr-grid-field" style="margin-top: 4px;">
                                        <label for="woocommerce_oi_banglaqr_gateway_logo" style="font-weight: 600; font-size: 13px; color: #1e293b; display: block; margin-bottom: 6px;"><?php esc_html_e('Gateway QR Image', 'banglaqr-payment-gateway-by-oi'); ?></label>
                                        <div class="banglaqr-uploader-inline" style="display: flex; gap: 8px; align-items: center; width: 100%;">
                                            <div class="banglaqr-logo-preview-box" id="banglaqr-gateway-logo-preview" style="width: 38px; height: 38px; border: 1px solid #cbd5e1; border-radius: 8px; background-color: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 4px; box-sizing: border-box; flex-shrink: 0;">
                                                <?php if ($gateway_logo): ?>
                                                        <img src="<?php echo esc_url($gateway_logo); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 4px;" />
                                                <?php else: ?>
                                                        <span class="dashicons dashicons-image-filter" style="color:#64748b;"></span>
                                                <?php endif; ?>
                                            </div>
                                            <input type="text" name="woocommerce_oi_banglaqr_gateway_logo" id="woocommerce_oi_banglaqr_gateway_logo" value="<?php echo esc_attr($gateway_logo); ?>" placeholder="<?php esc_attr_e('QR URL or upload', 'banglaqr-payment-gateway-by-oi'); ?>" style="flex: 1; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 13px; height: 38px; box-sizing: border-box; margin: 0 !important;" />
                                            <button type="button" class="button button-secondary" id="banglaqr-upload-gateway-logo-btn" style="height: 38px; border-radius: 8px; margin: 0 !important; font-weight: 600; font-size: 12px; padding: 0 16px; flex-shrink: 0;"><?php esc_html_e('Upload', 'banglaqr-payment-gateway-by-oi'); ?></button>
                                        </div>
                                        <p class="banglaqr-field-tip" style="margin:4px 0 0 0; color:#64748b; font-size:12px;">
                                            <?php esc_html_e('QR next to title on checkout.', 'banglaqr-payment-gateway-by-oi'); ?>
                                        </p>
                                    </div>
                                    
                                    <!-- Theme Color -->
                                    <div class="banglaqr-grid-field" style="margin-top: 4px;">
                                        <label for="woocommerce_oi_banglaqr_theme_color" style="font-weight: 600; font-size: 13px; color: #1e293b; display: block; margin-bottom: 6px;"><?php esc_html_e('Theme Color', 'banglaqr-payment-gateway-by-oi'); ?></label>
                                        <input type="color" name="woocommerce_oi_banglaqr_theme_color" id="woocommerce_oi_banglaqr_theme_color" value="<?php echo esc_attr($theme_color); ?>" style="width: 100%; max-width: 80px; height: 38px; padding: 0; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer;" />
                                        <p style="margin:4px 0 0 0; color:#64748b; font-size:12px;">
                                            <?php esc_html_e('Primary color for checkout popup buttons.', 'banglaqr-payment-gateway-by-oi'); ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Right Column: Gateway Title and Description -->
                                <div class="banglaqr-card-inputs-wrapper" style="flex: 1;">
                                    
                                    <!-- Gateway Title -->
                                    <div class="banglaqr-grid-field">
                                        <label for="woocommerce_oi_banglaqr_title" style="font-weight: 600; font-size: 13px; color: #1e293b; display: block; margin-bottom: 6px;"><?php esc_html_e('Gateway Title (Checkout Display)', 'banglaqr-payment-gateway-by-oi'); ?> <span class="req">*</span></label>
                                        <input type="text" name="woocommerce_oi_banglaqr_title" id="woocommerce_oi_banglaqr_title" value="<?php echo esc_attr($title); ?>" placeholder="e.g. Bangla QR Payment" required class="banglaqr-general-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 13px; height: 38px; box-sizing: border-box;" />
                                        <p class="banglaqr-field-tip" style="margin:4px 0 0 0; color:#64748b; font-size:12px;">
                                            <?php esc_html_e('This is the checkout payment method title seen by customers.', 'banglaqr-payment-gateway-by-oi'); ?>
                                        </p>
                                    </div>

                                    <!-- Gateway Description -->
                                    <div class="banglaqr-grid-field" style="margin-top: 12px;">
                                        <label for="woocommerce_oi_banglaqr_description" style="font-weight: 600; font-size: 13px; color: #1e293b; display: block; margin-bottom: 6px;"><?php esc_html_e('Gateway Description', 'banglaqr-payment-gateway-by-oi'); ?></label>
                                        <textarea name="woocommerce_oi_banglaqr_description" id="woocommerce_oi_banglaqr_description" rows="3" placeholder="e.g. Scan the QR code to make payment..." class="banglaqr-general-textarea" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:10px 14px; font-size:13px; font-family:inherit; box-sizing:border-box;"><?php echo esc_textarea($description); ?></textarea>
                                        <p class="banglaqr-field-tip" style="margin:4px 0 0 0; color:#64748b; font-size:12px;">
                                            <?php esc_html_e('The description content shown to customers under the gateway title on checkout (HTML tags are supported).', 'banglaqr-payment-gateway-by-oi'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- End General Settings Section -->

                        <!-- QR Accounts Section -->
                        <div class="banglaqr-settings-section">
                            <div class="banglaqr-section-header">
                                <h2><?php esc_html_e('Manage QR Accounts', 'banglaqr-payment-gateway-by-oi'); ?></h2>
                                <p><?php esc_html_e('Add QR codes that customers can scan. Only one QR can be active at a time.', 'banglaqr-payment-gateway-by-oi'); ?></p>
                            </div>
                            <div class="banglaqr-section-body">
                                <!-- Accordion Container -->
                                <div id="banglaqr-qr-accordion-wrapper" class="banglaqr-qr-accordion-wrapper">
                                    <!-- Populated dynamically by admin.js -->
                                </div>
                                
                                <!-- Hidden JSON field for QRs -->
                                <input type="hidden" name="woocommerce_oi_banglaqr_qrs_table" id="woocommerce_oi_banglaqr_qrs_table" value="<?php echo esc_attr(wp_json_encode($qrs)); ?>" />
                            </div>
                        </div> <!-- End QR Accounts Section -->

                    </div>
                </div>
                <?php
    }

    /**
     * Validate and sanitize the QR table field.
     *
     * @param string $key Settings key.
     * @param string $value Field value.
     * @return array Sanitized array of QR codes.
     */
    public function validate_qrs_table_field($key, $value)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $raw_value = isset($_POST[$this->get_field_key($key)]) ? wp_unslash($_POST[$this->get_field_key($key)]) : '';
        $decoded = json_decode(html_entity_decode(stripslashes($raw_value)), true);

        $sanitized_qrs = array();
        if (is_array($decoded)) {
            foreach ($decoded as $qr) {
                $qr_name = sanitize_text_field(isset($qr['qr_name']) ? $qr['qr_name'] : '');
                $qr_code_url = esc_url_raw(isset($qr['qr_code_url']) ? $qr['qr_code_url'] : '');

                // Skip saving completely empty entries to allow default QR fallback
                if (empty($qr_name) && empty($qr_code_url)) {
                    continue;
                }

                $sanitized_qrs[] = array(
                    'qr_name' => $qr_name,
                    'qr_code_url' => $qr_code_url,
                    'payment_charge' => sanitize_text_field(isset($qr['payment_charge']) ? $qr['payment_charge'] : '0'),
                    'is_active' => isset($qr['is_active']) && $qr['is_active'] === 'yes' ? 'yes' : 'no',
                );
            }
        }
        return $sanitized_qrs;
    }

    /**
     * Enqueue CSS and JS assets on the checkout page.
     */
    public function enqueue_checkout_assets()
    {
        if (!is_checkout() || !$this->is_available()) {
            return;
        }

        $handle = 'banglaqr-frontend';

        wp_enqueue_style($handle, OI_BANGLAQR_URL . 'includes/css/banglaqr-frontend.css', array(), OI_BANGLAQR_VERSION);
        wp_enqueue_script($handle, OI_BANGLAQR_URL . 'includes/js/banglaqr-frontend.js', array('jquery'), OI_BANGLAQR_VERSION, true);

        // Find active QR code from settings
        $settings = $this->settings;
        $qrs_table = isset($settings['qrs_table']) ? $settings['qrs_table'] : array();

        if (!is_array($qrs_table) || empty($qrs_table)) {
            $qrs_table = array(
                array(
                    'qr_name' => 'Test QR',
                    'qr_code_url' => OI_BANGLAQR_URL . 'includes/img/testqr.png',
                    'payment_charge' => '1',
                    'is_active' => 'yes',
                )
            );
        }

        $active_qr = null;
        if (is_array($qrs_table) && !empty($qrs_table)) {
            foreach ($qrs_table as $qr) {
                if (isset($qr['is_active']) && $qr['is_active'] === 'yes') {
                    $active_qr = $qr;
                    break;
                }
            }
            // Fallback: if none is active, use the first one
            if (!$active_qr && !empty($qrs_table)) {
                $active_qr = $qrs_table[0];
            }
        }

        $charge_percent = ($active_qr && isset($active_qr['payment_charge'])) ? floatval($active_qr['payment_charge']) : 0;

        // Calculate dynamic total including payment charge rounded to nearest integer (do not display decimal/fractional paisa values)
        $rounded_total = round(WC()->cart->get_total('edit'));
        $formatted_total = html_entity_decode(wp_strip_all_tags(wc_price($rounded_total)));

        // Dynamically set max file size based on server limit and our 5MB default
        $max_upload_size = wp_max_upload_size();
        $allowed_max_size = min(5 * 1024 * 1024, $max_upload_size);
        $allowed_max_size_mb = max(1, round($allowed_max_size / (1024 * 1024)));
        
        // Localize configuration data
        wp_localize_script($handle, 'oi_banglaqr_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'upload_nonce' => wp_create_nonce('oi_banglaqr_upload_slip_action'),
            'active_qr' => $active_qr,
            'gateway_id' => $this->id,
            'theme_color' => isset($settings['theme_color']) ? $settings['theme_color'] : '#137833',
            'max_file_size' => $allowed_max_size,
            'text_max_file_size' => $allowed_max_size_mb . 'MB',
            'paymentpage_img_url' => OI_BANGLAQR_URL . 'includes/img/banglaqr-paymentpage.png',
            'order_total' => $formatted_total,
            'payment_charge' => $charge_percent,
            'error_no_file' => __('Please upload your payment receipt or enter your payment Transaction ID to confirm your order.', 'banglaqr-payment-gateway-by-oi'),
            'error_invalid_file' => __('Invalid file format. Only JPEG, PNG, WEBP, and GIF images are allowed.', 'banglaqr-payment-gateway-by-oi'),
            // translators: %s is the max file size text (e.g. 5MB)
            'error_file_too_large' => sprintf(__('The selected file is too large. Maximum size allowed is %s.', 'banglaqr-payment-gateway-by-oi'), $allowed_max_size_mb . 'MB'),
        ));
    }

    /**
     * Get gateway icon logo.
     * Show the uploaded logo directly after the title on the checkout page.
     *
     * @return string
     */
    public function get_icon()
    {
        $logo_url = $this->icon;
        $icon = '';
        if ($logo_url) {
            $icon = '<img class="banglaqr-checkout-gateway-logo" src="' . esc_url($logo_url) . '" alt="' . esc_attr($this->get_title()) . '" style="max-height: 30px; max-width: 100px; margin-left: 5px; vertical-align: middle; display: inline-block;" />';
        }
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
    }

    /**
     * Render the payment fields on Checkout.
     */
    public function payment_fields()
    {
        // Output description if set
        if ($this->description) {
            echo wp_kses_post(wpautop($this->description));
        }

        // Output hidden checkout inputs which will hold the slip metadata
        ?>
                <div id="banglaqr-checkout-fields-container">
                    <input type="hidden" name="oi_banglaqr_receipt_id" id="oi_banglaqr_receipt_id" value="" />
                    <input type="hidden" name="oi_banglaqr_transaction_id" id="oi_banglaqr_transaction_id" value="" />
                    <input type="hidden" name="oi_banglaqr_selected_qr" id="oi_banglaqr_selected_qr" value="" />
                    <div id="banglaqr-selected-qr-preview" class="banglaqr-selected-qr-preview"
                        style="display:none; padding: 12px; border: 1px dashed #137833; border-radius: 8px; background-color: #f0fdf4; margin-top: 10px; font-size: 13px;">
                    </div>
                </div>
                <?php
    }

    /**
     * Server-side validation of checkout fields when order is submitted.
     */
    public function validate_checkout_fields()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (isset($_POST['payment_method']) && sanitize_text_field(wp_unslash($_POST['payment_method'])) === $this->id) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $has_receipt = !empty($_POST['oi_banglaqr_receipt_id']);
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $has_trx_id = !empty($_POST['oi_banglaqr_transaction_id']) && trim(sanitize_text_field(wp_unslash($_POST['oi_banglaqr_transaction_id']))) !== '';

            if (!$has_receipt && !$has_trx_id) {
                wc_add_notice(__('Please upload your payment receipt or enter your payment Transaction ID to complete the order via Bangla QR Payment.', 'banglaqr-payment-gateway-by-oi'), 'error');
            }
        }
    }

    /**
     * Handle AJAX file upload for payment receipt.
     */
    public function ajax_upload_slip()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_key($_POST['nonce']), 'oi_banglaqr_upload_slip_action')) {
            wp_send_json_error(array('message' => __('Invalid security token. Please refresh the page and try again.', 'banglaqr-payment-gateway-by-oi')));
        }

        if (empty($_FILES['oi_banglaqr_file']) || !empty($_FILES['oi_banglaqr_file']['error'])) {
            $error_message = __('No file uploaded or file error.', 'banglaqr-payment-gateway-by-oi');
            if (!empty($_FILES['oi_banglaqr_file']['error'])) {
                $error_code = intval($_FILES['oi_banglaqr_file']['error']);
                if ($error_code === UPLOAD_ERR_INI_SIZE || $error_code === UPLOAD_ERR_FORM_SIZE) {
                    $error_message = __('The uploaded file exceeds the maximum allowed upload size on this server (upload_max_filesize).', 'banglaqr-payment-gateway-by-oi');
                }
            }
            wp_send_json_error(array('message' => $error_message));
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $file = $_FILES['oi_banglaqr_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

        $overrides = array(
            'test_form' => false,
            'mimes' => array(
                'jpg|jpeg|jpe' => 'image/jpeg',
                'gif'          => 'image/gif',
                'png'          => 'image/png',
                'webp'         => 'image/webp'
            ),
        );

        $uploaded_file = wp_handle_upload($file, $overrides);

        if (isset($uploaded_file['error'])) {
            wp_send_json_error(array('message' => $uploaded_file['error']));
        }

        $filename = $uploaded_file['file'];
        $attachment = array(
            'post_mime_type' => $uploaded_file['type'],
            'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        $attachment_id = wp_insert_attachment($attachment, $filename);

        if (is_wp_error($attachment_id)) {
            wp_send_json_error(array('message' => __('Failed to create attachment.', 'banglaqr-payment-gateway-by-oi')));
        }

        $attachment_data = wp_generate_attachment_metadata($attachment_id, $filename);
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        // Security mark to prevent IDOR during checkout
        update_post_meta($attachment_id, '_oi_banglaqr_pending_upload', '1');

        wp_send_json_success(array(
            'id'  => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id)
        ));
    }

    /**
     * Process WooCommerce Payment.
     *
     * @param int $order_id Order ID.
     * @return array
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        $receipt_id = 0;
        $trx_id = '';
        $selected_qr = '';

        // Save receipt and selected QR metadata to the order
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (!empty($_POST['oi_banglaqr_receipt_id'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $receipt_id = absint(wp_unslash($_POST['oi_banglaqr_receipt_id']));
            $order->update_meta_data('_oi_banglaqr_receipt_id', $receipt_id);

            // Security check: Only update post parent if the ID belongs to an attachment to prevent IDOR
            if ($receipt_id > 0) {
                $receipt_post = get_post($receipt_id);
                if ($receipt_post && $receipt_post->post_type === 'attachment' && $receipt_post->post_parent == 0) {
                    // Verify the attachment was uploaded via our gateway
                    if (get_post_meta($receipt_id, '_oi_banglaqr_pending_upload', true) === '1') {
                        // Set the attachment as media parent of this order
                        wp_update_post(array(
                            'ID' => $receipt_id,
                            'post_parent' => $order_id,
                        ));
                        // Remove pending mark
                        delete_post_meta($receipt_id, '_oi_banglaqr_pending_upload');
                    }
                }
            }
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (!empty($_POST['oi_banglaqr_transaction_id'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $trx_id = sanitize_text_field(wp_unslash($_POST['oi_banglaqr_transaction_id']));
            $order->update_meta_data('_oi_banglaqr_transaction_id', $trx_id);
            $order->set_transaction_id($trx_id);
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (!empty($_POST['oi_banglaqr_selected_qr'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $selected_qr = sanitize_text_field(wp_unslash($_POST['oi_banglaqr_selected_qr']));
            $order->update_meta_data('_oi_banglaqr_selected_qr', $selected_qr);
        }

        // Build informative order note
        $note_details = array();
        if (!empty($selected_qr)) {
            // translators: %s is the QR account name.
            $note_details[] = sprintf(__('QR: %s', 'banglaqr-payment-gateway-by-oi'), $selected_qr);
        }
        if (!empty($trx_id)) {
            // translators: %s is the transaction ID.
            $note_details[] = sprintf(__('TrxID: %s', 'banglaqr-payment-gateway-by-oi'), $trx_id);
        }
        if ($receipt_id) {
            $note_details[] = __('Receipt Image: Attached', 'banglaqr-payment-gateway-by-oi');
        }

        $order_note = __('Awaiting Bangla QR payment verification.', 'banglaqr-payment-gateway-by-oi');
        if (!empty($note_details)) {
            $order_note .= ' (' . implode(' | ', $note_details) . ')';
        }

        // Set order status to on-hold (awaiting verification)
        $order->update_status('on-hold', $order_note);

        // Reduce stock levels
        wc_reduce_stock_levels($order_id);

        // Clear cart
        WC()->cart->empty_cart();

        // Return thank you redirect
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }

}
