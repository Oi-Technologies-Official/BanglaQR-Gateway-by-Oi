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
                'label' => __('Enable BanglaQR Gateway by Oi', 'banglaqr-payment-gateway-by-oi'),
                'default' => 'no',
            ),
            'title' => array(
                'title' => __('Payment Title', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'text',
                'description' => __('The payment method title shown to your customers at checkout.', 'banglaqr-payment-gateway-by-oi'),
                'default' => __('Bangla QR Payment', 'banglaqr-payment-gateway-by-oi'),
                'desc_tip' => true,
            ),
            'gateway_logo' => array(
                'title' => __('Gateway Logo', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'text',
                'description' => __('A brand icon or logo displayed beside the payment title on checkout.', 'banglaqr-payment-gateway-by-oi'),
                'default' => OI_BANGLAQR_URL . 'includes/img/banglaqrlogo.png',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Payment Description', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'textarea',
                'description' => __('Helpful payment instructions displayed when customers select this gateway.', 'banglaqr-payment-gateway-by-oi'),
                'default' => __('Scan the QR code with any banking or MFS app (bKash, Nagad, Rocket, CellFin, etc.) to complete your payment.', 'banglaqr-payment-gateway-by-oi'),
                'desc_tip' => true,
            ),
            'qrs_table' => array(
                'title' => __('QR Accounts', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'qrs_table',
                'description' => __('Add your payment QR codes and choose which account is active on checkout.', 'banglaqr-payment-gateway-by-oi'),
                'default' => array(
                    array(
                        'qr_name' => 'BanglaQR (Primary)',
                        'qr_code_url' => OI_BANGLAQR_URL . 'includes/img/testqr.png',
                        'payment_charge' => '1',
                        'is_active' => 'yes',
                    )
                ),
            ),
            'theme_color' => array(
                'title' => __('Theme Accent Color', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'color',
                'description' => __('Choose the accent color for popup buttons and highlights to match your store theme.', 'banglaqr-payment-gateway-by-oi'),
                'default' => '#137833',
                'desc_tip' => true,
            ),
            'order_status' => array(
                'title' => __('Default Order Status', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'select',
                'description' => __('The status assigned to new orders once a customer submits their payment confirmation.', 'banglaqr-payment-gateway-by-oi'),
                'default' => 'on-hold',
                'options' => array(
                    'on-hold'    => __('On-Hold (Recommended)', 'banglaqr-payment-gateway-by-oi'),
                    'processing' => __('Processing', 'banglaqr-payment-gateway-by-oi'),
                    'completed'  => __('Completed', 'banglaqr-payment-gateway-by-oi'),
                    'pending'    => __('Pending Payment', 'banglaqr-payment-gateway-by-oi'),
                ),
            ),
            'receipt_rule' => array(
                'title' => __('Receipt Screenshot Rule', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'select',
                'description' => __('Choose whether customers must attach a payment screenshot before placing their order.', 'banglaqr-payment-gateway-by-oi'),
                'default' => 'optional',
                'options' => array(
                    'mandatory' => __('Mandatory (Must upload)', 'banglaqr-payment-gateway-by-oi'),
                    'optional'  => __('Optional (Recommended)', 'banglaqr-payment-gateway-by-oi'),
                    'hidden'    => __('Hidden (Do not show)', 'banglaqr-payment-gateway-by-oi'),
                ),
            ),
            'trxid_rule' => array(
                'title' => __('Transaction ID Rule', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'select',
                'description' => __('Choose whether customers must enter their payment Transaction ID before placing their order.', 'banglaqr-payment-gateway-by-oi'),
                'default' => 'optional',
                'options' => array(
                    'mandatory' => __('Mandatory (Must enter)', 'banglaqr-payment-gateway-by-oi'),
                    'optional'  => __('Optional (Recommended)', 'banglaqr-payment-gateway-by-oi'),
                    'hidden'    => __('Hidden (Do not show)', 'banglaqr-payment-gateway-by-oi'),
                ),
            ),
            'enable_manual_payment' => array(
                'title' => __('Manual Mobile Banking', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'checkbox',
                'label' => __('Show direct mobile banking numbers for customers who prefer sending money manually', 'banglaqr-payment-gateway-by-oi'),
                'default' => 'no',
            ),
            'manual_bkash' => array(
                'title' => __('bKash Number', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'text',
                'description' => __('Your bKash personal or merchant account number.', 'banglaqr-payment-gateway-by-oi'),
                'default' => '',
                'desc_tip' => true,
            ),
            'manual_nagad' => array(
                'title' => __('Nagad Number', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'text',
                'description' => __('Your Nagad personal or merchant account number.', 'banglaqr-payment-gateway-by-oi'),
                'default' => '',
                'desc_tip' => true,
            ),
            'manual_rocket' => array(
                'title' => __('Rocket Number', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'text',
                'description' => __('Your Rocket personal or merchant account number.', 'banglaqr-payment-gateway-by-oi'),
                'default' => '',
                'desc_tip' => true,
            ),
            'manual_upay' => array(
                'title' => __('Upay Number', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'text',
                'description' => __('Your Upay personal or merchant account number.', 'banglaqr-payment-gateway-by-oi'),
                'default' => '',
                'desc_tip' => true,
            ),
            'manual_cellfin' => array(
                'title' => __('CellFin Number', 'banglaqr-payment-gateway-by-oi'),
                'type' => 'text',
                'description' => __('Your CellFin account or card number.', 'banglaqr-payment-gateway-by-oi'),
                'default' => '',
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
        
        $order_status = $this->get_option('order_status', 'on-hold');
        $receipt_rule = $this->get_option('receipt_rule', 'optional');
        $trxid_rule = $this->get_option('trxid_rule', 'optional');
        
        $enable_manual_payment = $this->get_option('enable_manual_payment', 'no');
        $manual_bkash = $this->get_option('manual_bkash', '');
        $manual_nagad = $this->get_option('manual_nagad', '');
        $manual_rocket = $this->get_option('manual_rocket', '');
        $manual_upay = $this->get_option('manual_upay', '');
        $manual_cellfin = $this->get_option('manual_cellfin', '');
        
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
        $active_qr_name = __('None Selected', 'banglaqr-payment-gateway-by-oi');
        $active_qr_fee = '0';
        foreach ($qrs as $q) {
            if (isset($q['is_active']) && $q['is_active'] === 'yes') {
                $active_qr_name = !empty($q['qr_name']) ? $q['qr_name'] : __('Active QR', 'banglaqr-payment-gateway-by-oi');
                $active_qr_fee = isset($q['payment_charge']) ? $q['payment_charge'] : '0';
                break;
            }
        }

        $configured_manual_count = 0;
        if (!empty($manual_bkash)) $configured_manual_count++;
        if (!empty($manual_nagad)) $configured_manual_count++;
        if (!empty($manual_rocket)) $configured_manual_count++;
        if (!empty($manual_upay)) $configured_manual_count++;
        if (!empty($manual_cellfin)) $configured_manual_count++;
        ?>
        <div class="banglaqr-admin-dashboard-wrap">
            <!-- Header Section with Live Status and Quick Metrics -->
            <div class="banglaqr-dashboard-header">
                <div class="banglaqr-header-top">
                    <div class="banglaqr-header-info">
                        <div class="banglaqr-logo-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 15h.008v.008H15V15zm0 2.25h.008v.008H15v-.008zM17.25 15h.008v.008H17.25V15zm0 2.25h.008v.008H17.25v-.008zm-2.25 2.25h.008v.008H15v-.008zm2.25 0h.008v.008H17.25v-.008zM19.5 15h.008v.008H19.5V15zm0 2.25h.008v.008H19.5v-.008zm-2.25-4.5h.008v.008H17.25v-.008zm2.25 0h.008v.008H19.5v-.008z" />
                            </svg>
                        </div>
                        <div class="banglaqr-header-title-box">
                            <h1 class="banglaqr-header-title"><?php esc_html_e('BanglaQR Settings', 'banglaqr-payment-gateway-by-oi'); ?></h1>
                            <p class="banglaqr-header-subtitle"><?php esc_html_e('Configure your QR accounts, transaction processing fees, and checkout payment rules.', 'banglaqr-payment-gateway-by-oi'); ?></p>
                        </div>
                    </div>
                    <div class="banglaqr-header-actions">
                        <span class="banglaqr-status-badge <?php echo $enabled === 'yes' ? 'status-active' : 'status-inactive'; ?>">
                            <span class="status-dot"></span>
                            <?php echo $enabled === 'yes' ? esc_html__('Gateway Active', 'banglaqr-payment-gateway-by-oi') : esc_html__('Gateway Inactive', 'banglaqr-payment-gateway-by-oi'); ?>
                        </span>
                    </div>
                </div>

                <!-- Quick Configuration Metrics Bar -->
                <div class="banglaqr-quick-metrics-row">
                    <div class="banglaqr-metric-pill">
                        <span class="banglaqr-metric-label"><?php esc_html_e('Active QR', 'banglaqr-payment-gateway-by-oi'); ?></span>
                        <span class="banglaqr-metric-value"><?php echo esc_html($active_qr_name); ?></span>
                        <span class="banglaqr-metric-badge"><?php echo esc_html($active_qr_fee); ?>% <?php esc_html_e('Fee', 'banglaqr-payment-gateway-by-oi'); ?></span>
                    </div>
                    <div class="banglaqr-metric-pill">
                        <span class="banglaqr-metric-label"><?php esc_html_e('Receipt Upload', 'banglaqr-payment-gateway-by-oi'); ?></span>
                        <span class="banglaqr-metric-value"><?php echo esc_html(ucfirst($receipt_rule)); ?></span>
                    </div>
                    <div class="banglaqr-metric-pill">
                        <span class="banglaqr-metric-label"><?php esc_html_e('Transaction ID', 'banglaqr-payment-gateway-by-oi'); ?></span>
                        <span class="banglaqr-metric-value"><?php echo esc_html(ucfirst($trxid_rule)); ?></span>
                    </div>
                    <div class="banglaqr-metric-pill">
                        <span class="banglaqr-metric-label"><?php esc_html_e('Manual Accounts', 'banglaqr-payment-gateway-by-oi'); ?></span>
                        <span class="banglaqr-metric-value">
                            <?php echo $enable_manual_payment === 'yes' ? esc_html(sprintf(__('%d Active', 'banglaqr-payment-gateway-by-oi'), $configured_manual_count)) : esc_html__('Disabled', 'banglaqr-payment-gateway-by-oi'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Unified Settings Sections -->
            <div class="banglaqr-settings-panel">
                
                <!-- Section 1: General Gateway Settings -->
                <div class="banglaqr-settings-section">
                    <div class="banglaqr-section-header">
                        <div class="banglaqr-section-icon-badge badge-green">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        </div>
                        <div class="banglaqr-section-header-text">
                            <h2><?php esc_html_e('General Gateway Settings', 'banglaqr-payment-gateway-by-oi'); ?></h2>
                            <p><?php esc_html_e('Branding, accent theme color, and customer-facing checkout presentation.', 'banglaqr-payment-gateway-by-oi'); ?></p>
                        </div>
                    </div>
                    <div class="banglaqr-section-body">
                        <div class="banglaqr-grid-2col">
                            
                            <!-- Left Column: Branding & Controls -->
                            <div class="banglaqr-card-subpanel">
                                <h3 class="banglaqr-subpanel-title">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/></svg>
                                    <span><?php esc_html_e('Branding & Availability', 'banglaqr-payment-gateway-by-oi'); ?></span>
                                </h3>

                                <!-- Enable/Disable Gateway Toggle Card -->
                                <div class="banglaqr-toggle-card">
                                    <div class="banglaqr-toggle-wrapper">
                                        <label class="banglaqr-switch">
                                            <input type="checkbox" name="woocommerce_oi_banglaqr_enabled" id="woocommerce_oi_banglaqr_enabled" value="yes" <?php checked($enabled, 'yes'); ?> />
                                            <span class="banglaqr-slider"></span>
                                        </label>
                                    </div>
                                    <div class="banglaqr-toggle-card-text">
                                        <label for="woocommerce_oi_banglaqr_enabled" class="banglaqr-toggle-card-label">
                                            <?php esc_html_e('Enable Gateway on Checkout', 'banglaqr-payment-gateway-by-oi'); ?>
                                        </label>
                                        <p class="banglaqr-field-tip">
                                            <?php esc_html_e('Make this payment method active and visible to customers.', 'banglaqr-payment-gateway-by-oi'); ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Gateway Logo -->
                                <div class="banglaqr-grid-field">
                                    <label for="woocommerce_oi_banglaqr_gateway_logo" class="banglaqr-field-label-with-icon">
                                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        <span><?php esc_html_e('Gateway Logo', 'banglaqr-payment-gateway-by-oi'); ?></span>
                                    </label>
                                    <div class="banglaqr-uploader-inline">
                                        <div class="banglaqr-logo-preview-box" id="banglaqr-gateway-logo-preview">
                                            <?php if ($gateway_logo): ?>
                                                <img src="<?php echo esc_url($gateway_logo); ?>" alt="Gateway Logo" />
                                            <?php else: ?>
                                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#94a3b8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                            <?php endif; ?>
                                        </div>
                                        <input type="text" name="woocommerce_oi_banglaqr_gateway_logo" id="woocommerce_oi_banglaqr_gateway_logo" value="<?php echo esc_attr($gateway_logo); ?>" placeholder="<?php esc_attr_e('Paste logo image URL or upload', 'banglaqr-payment-gateway-by-oi'); ?>" class="banglaqr-general-input" />
                                        <button type="button" class="button button-secondary banglaqr-btn-with-icon" id="banglaqr-upload-gateway-logo-btn">
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                            <span><?php esc_html_e('Upload', 'banglaqr-payment-gateway-by-oi'); ?></span>
                                        </button>
                                    </div>
                                    <p class="banglaqr-field-tip">
                                        <?php esc_html_e('Displayed beside the gateway title on the checkout page for trust recognition.', 'banglaqr-payment-gateway-by-oi'); ?>
                                    </p>
                                </div>

                                <!-- Theme Accent Color -->
                                <div class="banglaqr-grid-field">
                                    <label for="woocommerce_oi_banglaqr_theme_color" class="banglaqr-field-label-with-icon">
                                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a7 7 0 0 0 0 14 3 3 0 0 0 3-3c0-1.5-1-2-1-3.5a1.5 1.5 0 0 1 3 0A7 7 0 0 0 12 2z"/></svg>
                                        <span><?php esc_html_e('Theme Accent Color', 'banglaqr-payment-gateway-by-oi'); ?></span>
                                    </label>
                                    <div class="banglaqr-color-control-wrapper">
                                        <div class="banglaqr-color-swatch-indicator" id="banglaqr-theme-color-swatch" style="background-color: <?php echo esc_attr($theme_color); ?>;"></div>
                                        <input type="text" id="banglaqr-theme-color-hex" value="<?php echo esc_attr($theme_color); ?>" class="banglaqr-color-hex-badge" readonly />
                                        <input type="color" name="woocommerce_oi_banglaqr_theme_color" id="woocommerce_oi_banglaqr_theme_color" value="<?php echo esc_attr($theme_color); ?>" class="banglaqr-color-native-input" />
                                        <label for="woocommerce_oi_banglaqr_theme_color" class="button button-secondary banglaqr-btn-with-icon banglaqr-color-trigger-btn">
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 12 4-4a2 2 0 1 0-3-3l-4 4"/><path d="m12 15-7 7H2v-3l7-7"/></svg>
                                            <span><?php esc_html_e('Change Color', 'banglaqr-payment-gateway-by-oi'); ?></span>
                                        </label>
                                    </div>
                                    <p class="banglaqr-field-tip">
                                        <?php esc_html_e('Accent color used for checkout modal banners, buttons, and status indicators.', 'banglaqr-payment-gateway-by-oi'); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Right Column: Checkout Presentation -->
                            <div class="banglaqr-card-subpanel">
                                <h3 class="banglaqr-subpanel-title">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h6"/></svg>
                                    <span><?php esc_html_e('Checkout Presentation', 'banglaqr-payment-gateway-by-oi'); ?></span>
                                </h3>

                                <!-- Gateway Title -->
                                <div class="banglaqr-grid-field">
                                    <label for="woocommerce_oi_banglaqr_title" class="banglaqr-field-label-with-icon">
                                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                                        <span><?php esc_html_e('Checkout Title', 'banglaqr-payment-gateway-by-oi'); ?> <span class="req">*</span></span>
                                    </label>
                                    <input type="text" name="woocommerce_oi_banglaqr_title" id="woocommerce_oi_banglaqr_title" value="<?php echo esc_attr($title); ?>" placeholder="e.g. Bangla QR Payment" required class="banglaqr-general-input" />
                                    <p class="banglaqr-field-tip">
                                        <?php esc_html_e('The payment gateway name shown in the WooCommerce checkout payment list.', 'banglaqr-payment-gateway-by-oi'); ?>
                                    </p>
                                </div>

                                <!-- Gateway Description -->
                                <div class="banglaqr-grid-field">
                                    <label for="woocommerce_oi_banglaqr_description" class="banglaqr-field-label-with-icon">
                                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        <span><?php esc_html_e('Checkout Description', 'banglaqr-payment-gateway-by-oi'); ?></span>
                                    </label>
                                    <textarea name="woocommerce_oi_banglaqr_description" id="woocommerce_oi_banglaqr_description" rows="4" placeholder="<?php esc_attr_e('e.g. Scan the QR code using your bKash, Nagad, Rocket, CellFin, or mobile banking app to pay securely.', 'banglaqr-payment-gateway-by-oi'); ?>" class="banglaqr-general-textarea"><?php echo esc_textarea($description); ?></textarea>
                                    <p class="banglaqr-field-tip">
                                        <?php esc_html_e('Informative guidance displayed below the title when shoppers select this payment method.', 'banglaqr-payment-gateway-by-oi'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- End General Settings Section -->

                <!-- Section 2: Checkout Rules & Status (3-Card Matrix) -->
                <div class="banglaqr-settings-section">
                    <div class="banglaqr-section-header">
                        <div class="banglaqr-section-icon-badge badge-blue">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <div class="banglaqr-section-header-text">
                            <h2><?php esc_html_e('Checkout Rules & Order Status', 'banglaqr-payment-gateway-by-oi'); ?></h2>
                            <p><?php esc_html_e('Define order status and payment verification requirements for shoppers.', 'banglaqr-payment-gateway-by-oi'); ?></p>
                        </div>
                    </div>
                    <div class="banglaqr-section-body">
                        <div class="banglaqr-rules-grid">
                            
                            <!-- Rule Card 1: Default Order Status -->
                            <div class="banglaqr-rule-card">
                                <div class="banglaqr-rule-card-header">
                                    <div class="banglaqr-rule-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    </div>
                                    <label for="woocommerce_oi_banglaqr_order_status" class="banglaqr-rule-title">
                                        <?php esc_html_e('Default Order Status', 'banglaqr-payment-gateway-by-oi'); ?>
                                    </label>
                                </div>
                                <select name="woocommerce_oi_banglaqr_order_status" id="woocommerce_oi_banglaqr_order_status" class="banglaqr-general-input banglaqr-rule-select">
                                    <option value="on-hold" <?php selected($order_status, 'on-hold'); ?>><?php esc_html_e('On-Hold (Recommended)', 'banglaqr-payment-gateway-by-oi'); ?></option>
                                    <option value="processing" <?php selected($order_status, 'processing'); ?>><?php esc_html_e('Processing', 'banglaqr-payment-gateway-by-oi'); ?></option>
                                    <option value="completed" <?php selected($order_status, 'completed'); ?>><?php esc_html_e('Completed', 'banglaqr-payment-gateway-by-oi'); ?></option>
                                    <option value="pending" <?php selected($order_status, 'pending'); ?>><?php esc_html_e('Pending Payment', 'banglaqr-payment-gateway-by-oi'); ?></option>
                                </select>
                                <p class="banglaqr-field-tip">
                                    <?php esc_html_e('Status assigned immediately after customer confirms payment in the checkout popup.', 'banglaqr-payment-gateway-by-oi'); ?>
                                </p>
                            </div>

                            <!-- Rule Card 2: Receipt Screenshot Rule -->
                            <div class="banglaqr-rule-card">
                                <div class="banglaqr-rule-card-header">
                                    <div class="banglaqr-rule-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                    </div>
                                    <label for="woocommerce_oi_banglaqr_receipt_rule" class="banglaqr-rule-title">
                                        <?php esc_html_e('Receipt Screenshot Rule', 'banglaqr-payment-gateway-by-oi'); ?>
                                    </label>
                                </div>
                                <select name="woocommerce_oi_banglaqr_receipt_rule" id="woocommerce_oi_banglaqr_receipt_rule" class="banglaqr-general-input banglaqr-rule-select">
                                    <option value="optional" <?php selected($receipt_rule, 'optional'); ?>><?php esc_html_e('Optional (Recommended)', 'banglaqr-payment-gateway-by-oi'); ?></option>
                                    <option value="mandatory" <?php selected($receipt_rule, 'mandatory'); ?>><?php esc_html_e('Mandatory (Must upload)', 'banglaqr-payment-gateway-by-oi'); ?></option>
                                    <option value="hidden" <?php selected($receipt_rule, 'hidden'); ?>><?php esc_html_e('Hidden (Do not show)', 'banglaqr-payment-gateway-by-oi'); ?></option>
                                </select>
                                <p class="banglaqr-field-tip">
                                    <?php esc_html_e('Allows customers to upload their payment screenshot or bank receipt as proof.', 'banglaqr-payment-gateway-by-oi'); ?>
                                </p>
                            </div>

                            <!-- Rule Card 3: Transaction ID Rule -->
                            <div class="banglaqr-rule-card">
                                <div class="banglaqr-rule-card-header">
                                    <div class="banglaqr-rule-icon">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
                                    </div>
                                    <label for="woocommerce_oi_banglaqr_trxid_rule" class="banglaqr-rule-title">
                                        <?php esc_html_e('Transaction ID Rule', 'banglaqr-payment-gateway-by-oi'); ?>
                                    </label>
                                </div>
                                <select name="woocommerce_oi_banglaqr_trxid_rule" id="woocommerce_oi_banglaqr_trxid_rule" class="banglaqr-general-input banglaqr-rule-select">
                                    <option value="optional" <?php selected($trxid_rule, 'optional'); ?>><?php esc_html_e('Optional (Recommended)', 'banglaqr-payment-gateway-by-oi'); ?></option>
                                    <option value="mandatory" <?php selected($trxid_rule, 'mandatory'); ?>><?php esc_html_e('Mandatory (Must enter)', 'banglaqr-payment-gateway-by-oi'); ?></option>
                                    <option value="hidden" <?php selected($trxid_rule, 'hidden'); ?>><?php esc_html_e('Hidden (Do not show)', 'banglaqr-payment-gateway-by-oi'); ?></option>
                                </select>
                                <p class="banglaqr-field-tip">
                                    <?php esc_html_e('Allows shoppers to submit their 8-10 digit bank/MFS transaction identifier.', 'banglaqr-payment-gateway-by-oi'); ?>
                                </p>
                            </div>

                        </div>
                    </div>
                </div> <!-- End Checkout Rules Section -->

                <!-- Section 3: Manual Payment Accounts (Branded Cards) -->
                <div class="banglaqr-settings-section">
                    <div class="banglaqr-section-header">
                        <div class="banglaqr-section-icon-badge badge-amber">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        </div>
                        <div class="banglaqr-section-header-text">
                            <h2><?php esc_html_e('Manual Payment Accounts', 'banglaqr-payment-gateway-by-oi'); ?></h2>
                            <p><?php esc_html_e('Provide direct mobile wallet numbers for customers who prefer sending money manually.', 'banglaqr-payment-gateway-by-oi'); ?></p>
                        </div>
                    </div>
                    <div class="banglaqr-section-body">
                        
                        <!-- Master Enable Switch Card -->
                        <div class="banglaqr-toggle-card banglaqr-mfs-master-toggle">
                            <div class="banglaqr-toggle-wrapper">
                                <label class="banglaqr-switch">
                                    <input type="checkbox" name="woocommerce_oi_banglaqr_enable_manual_payment" id="woocommerce_oi_banglaqr_enable_manual_payment" value="yes" <?php checked($enable_manual_payment, 'yes'); ?> />
                                    <span class="banglaqr-slider"></span>
                                </label>
                            </div>
                            <div class="banglaqr-toggle-card-text">
                                <label for="woocommerce_oi_banglaqr_enable_manual_payment" class="banglaqr-toggle-card-label">
                                    <?php esc_html_e('Enable Manual Payment Accounts', 'banglaqr-payment-gateway-by-oi'); ?>
                                </label>
                                <p class="banglaqr-field-tip">
                                    <?php esc_html_e('Shows a "Prefer manual payment?" dropdown in the checkout modal with copyable numbers.', 'banglaqr-payment-gateway-by-oi'); ?>
                                </p>
                            </div>
                        </div>

                        <!-- 5 Branded Provider Cards Grid -->
                        <div class="banglaqr-mfs-grid">
                            
                            <!-- bKash Card -->
                            <div class="banglaqr-mfs-card mfs-card-bkash">
                                <div class="banglaqr-mfs-card-header">
                                    <div class="banglaqr-mfs-badge">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                        <span>bKash</span>
                                    </div>
                                    <span class="banglaqr-mfs-type-tag"><?php esc_html_e('Merchant / Personal', 'banglaqr-payment-gateway-by-oi'); ?></span>
                                </div>
                                <div class="banglaqr-mfs-input-box">
                                    <input type="text" name="woocommerce_oi_banglaqr_manual_bkash" id="woocommerce_oi_banglaqr_manual_bkash" value="<?php echo esc_attr($manual_bkash); ?>" placeholder="017xxxxxxxx" class="banglaqr-general-input banglaqr-mfs-input" />
                                </div>
                            </div>

                            <!-- Nagad Card -->
                            <div class="banglaqr-mfs-card mfs-card-nagad">
                                <div class="banglaqr-mfs-card-header">
                                    <div class="banglaqr-mfs-badge">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                        <span>Nagad</span>
                                    </div>
                                    <span class="banglaqr-mfs-type-tag"><?php esc_html_e('Merchant / Personal', 'banglaqr-payment-gateway-by-oi'); ?></span>
                                </div>
                                <div class="banglaqr-mfs-input-box">
                                    <input type="text" name="woocommerce_oi_banglaqr_manual_nagad" id="woocommerce_oi_banglaqr_manual_nagad" value="<?php echo esc_attr($manual_nagad); ?>" placeholder="018xxxxxxxx" class="banglaqr-general-input banglaqr-mfs-input" />
                                </div>
                            </div>

                            <!-- Rocket Card -->
                            <div class="banglaqr-mfs-card mfs-card-rocket">
                                <div class="banglaqr-mfs-card-header">
                                    <div class="banglaqr-mfs-badge">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                        <span>Rocket</span>
                                    </div>
                                    <span class="banglaqr-mfs-type-tag"><?php esc_html_e('12-digit Account', 'banglaqr-payment-gateway-by-oi'); ?></span>
                                </div>
                                <div class="banglaqr-mfs-input-box">
                                    <input type="text" name="woocommerce_oi_banglaqr_manual_rocket" id="woocommerce_oi_banglaqr_manual_rocket" value="<?php echo esc_attr($manual_rocket); ?>" placeholder="019xxxxxxxx" class="banglaqr-general-input banglaqr-mfs-input" />
                                </div>
                            </div>

                            <!-- Upay Card -->
                            <div class="banglaqr-mfs-card mfs-card-upay">
                                <div class="banglaqr-mfs-card-header">
                                    <div class="banglaqr-mfs-badge">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                        <span>Upay</span>
                                    </div>
                                    <span class="banglaqr-mfs-type-tag"><?php esc_html_e('Merchant / Personal', 'banglaqr-payment-gateway-by-oi'); ?></span>
                                </div>
                                <div class="banglaqr-mfs-input-box">
                                    <input type="text" name="woocommerce_oi_banglaqr_manual_upay" id="woocommerce_oi_banglaqr_manual_upay" value="<?php echo esc_attr($manual_upay); ?>" placeholder="016xxxxxxxx" class="banglaqr-general-input banglaqr-mfs-input" />
                                </div>
                            </div>

                            <!-- CellFin Card -->
                            <div class="banglaqr-mfs-card mfs-card-cellfin">
                                <div class="banglaqr-mfs-card-header">
                                    <div class="banglaqr-mfs-badge">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                        <span>CellFin</span>
                                    </div>
                                    <span class="banglaqr-mfs-type-tag"><?php esc_html_e('Account / Card / Phone', 'banglaqr-payment-gateway-by-oi'); ?></span>
                                </div>
                                <div class="banglaqr-mfs-input-box">
                                    <input type="text" name="woocommerce_oi_banglaqr_manual_cellfin" id="woocommerce_oi_banglaqr_manual_cellfin" value="<?php echo esc_attr($manual_cellfin); ?>" placeholder="CellFin number or card" class="banglaqr-general-input banglaqr-mfs-input" />
                                </div>
                            </div>

                        </div>
                    </div>
                </div> <!-- End Manual Payment Accounts Section -->

                <!-- Section 4: Manage QR Accounts -->
                <div class="banglaqr-settings-section">
                    <div class="banglaqr-section-header">
                        <div class="banglaqr-section-icon-badge badge-purple">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h2v2h-2z"/><path d="M18 14h3v3h-3z"/><path d="M14 18h3v3h-3z"/><path d="M20 20h1v1h-1z"/></svg>
                        </div>
                        <div class="banglaqr-section-header-text">
                            <h2><?php esc_html_e('Manage QR Accounts', 'banglaqr-payment-gateway-by-oi'); ?></h2>
                            <p><?php esc_html_e('Add, reorder, and activate QR accounts. The active account will be displayed in the checkout popup.', 'banglaqr-payment-gateway-by-oi'); ?></p>
                        </div>
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
        $decoded = json_decode($raw_value, true);
        if (!is_array($decoded)) {
            $decoded = json_decode(html_entity_decode($raw_value), true);
        }

        $sanitized_qrs = array();
        $has_active = false;
        if (is_array($decoded)) {
            foreach ($decoded as $qr) {
                $qr_name = sanitize_text_field(isset($qr['qr_name']) ? $qr['qr_name'] : '');
                $qr_code_url = esc_url_raw(isset($qr['qr_code_url']) ? $qr['qr_code_url'] : '');

                // Skip saving completely empty entries
                if (empty($qr_name) && empty($qr_code_url)) {
                    continue;
                }

                $charge = isset($qr['payment_charge']) ? floatval($qr['payment_charge']) : 0;
                $charge = max(0, min(100, $charge));

                $is_active = (isset($qr['is_active']) && $qr['is_active'] === 'yes' && !$has_active) ? 'yes' : 'no';
                if ($is_active === 'yes') {
                    $has_active = true;
                }

                $sanitized_qrs[] = array(
                    'qr_name' => $qr_name,
                    'qr_code_url' => $qr_code_url,
                    'payment_charge' => (string)$charge,
                    'is_active' => $is_active,
                );
            }
        }

        // If none is active but accounts exist, make the first one active
        if (!$has_active && !empty($sanitized_qrs)) {
            $sanitized_qrs[0]['is_active'] = 'yes';
        }

        return $sanitized_qrs;
    }

    /**
     * Enqueue CSS and JS assets on the checkout page.
     */
    public function enqueue_checkout_assets()
    {
        if (!is_checkout() || !WC()->cart || !$this->is_available()) {
            return;
        }

        $handle = 'banglaqr-frontend';

        $css_ver = file_exists(OI_BANGLAQR_PATH . 'includes/css/banglaqr-frontend.css') ? filemtime(OI_BANGLAQR_PATH . 'includes/css/banglaqr-frontend.css') : OI_BANGLAQR_VERSION;
        $js_ver  = file_exists(OI_BANGLAQR_PATH . 'includes/js/banglaqr-frontend.js') ? filemtime(OI_BANGLAQR_PATH . 'includes/js/banglaqr-frontend.js') : OI_BANGLAQR_VERSION;

        wp_enqueue_style($handle, OI_BANGLAQR_URL . 'includes/css/banglaqr-frontend.css', array(), $css_ver);
        wp_enqueue_script($handle, OI_BANGLAQR_URL . 'includes/js/banglaqr-frontend.js', array('jquery'), $js_ver, true);

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

        // Formatted total respecting store decimal settings
        $total_amount = WC()->cart->total;
        $formatted_total = html_entity_decode(wp_strip_all_tags(wc_price($total_amount)));

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
            'error_no_file' => __('Please upload your payment screenshot or enter your Transaction ID to confirm your order.', 'banglaqr-payment-gateway-by-oi'),
            'error_invalid_file' => __('Please upload a valid image file (JPEG, PNG, or WebP).', 'banglaqr-payment-gateway-by-oi'),
            'error_file_too_large' => sprintf(__('This image is too large. Maximum allowed size is %s.', 'banglaqr-payment-gateway-by-oi'), $allowed_max_size_mb . 'MB'),
            'i18n_required_field' => __('%s is required.', 'banglaqr-payment-gateway-by-oi'),
            'i18n_valid_email' => __('Please enter a valid email address for %s.', 'banglaqr-payment-gateway-by-oi'),
            'i18n_terms' => __('Please check the box to agree to the website terms and conditions.', 'banglaqr-payment-gateway-by-oi'),
            'receipt_rule' => isset($settings['receipt_rule']) ? $settings['receipt_rule'] : 'optional',
            'trxid_rule' => isset($settings['trxid_rule']) ? $settings['trxid_rule'] : 'optional',
            'enable_manual_payment' => isset($settings['enable_manual_payment']) ? $settings['enable_manual_payment'] : 'no',
            'manual_bkash' => isset($settings['manual_bkash']) ? $settings['manual_bkash'] : '',
            'manual_nagad' => isset($settings['manual_nagad']) ? $settings['manual_nagad'] : '',
            'manual_rocket' => isset($settings['manual_rocket']) ? $settings['manual_rocket'] : '',
            'manual_upay' => isset($settings['manual_upay']) ? $settings['manual_upay'] : '',
            'manual_cellfin' => isset($settings['manual_cellfin']) ? $settings['manual_cellfin'] : '',
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
                    <input type="hidden" name="oi_banglaqr_confirmed" id="oi_banglaqr_confirmed" value="0" />
                    <div id="banglaqr-selected-qr-preview" class="banglaqr-selected-qr-preview"></div>
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
            $receipt_rule = $this->get_option('receipt_rule', 'optional');
            $trxid_rule = $this->get_option('trxid_rule', 'optional');

            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $has_receipt = !empty($_POST['oi_banglaqr_receipt_id']);
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $has_trx_id = !empty($_POST['oi_banglaqr_transaction_id']) && trim(sanitize_text_field(wp_unslash($_POST['oi_banglaqr_transaction_id']))) !== '';

            if ($receipt_rule === 'mandatory' && !$has_receipt) {
                wc_add_notice(__('Please upload your payment receipt screenshot to complete this order.', 'banglaqr-payment-gateway-by-oi'), 'error');
            }

            if ($trxid_rule === 'mandatory' && !$has_trx_id) {
                wc_add_notice(__('Please enter your payment Transaction ID (TrxID) to complete this order.', 'banglaqr-payment-gateway-by-oi'), 'error');
            }

            // Fallback: If both are optional, require at least one (unless both are hidden)
            if ($receipt_rule !== 'hidden' || $trxid_rule !== 'hidden') {
                if ($receipt_rule !== 'mandatory' && $trxid_rule !== 'mandatory') {
                    if (!$has_receipt && !$has_trx_id) {
                        wc_add_notice(__('Please provide either a payment receipt screenshot or your Transaction ID to complete this order.', 'banglaqr-payment-gateway-by-oi'), 'error');
                    }
                }
            }
        }
    }

    /**
     * Handle AJAX file upload for payment receipt.
     */
    public function ajax_upload_slip()
    {
        // Security check
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_key($_POST['nonce']), 'oi_banglaqr_upload_slip_action')) {
            wp_send_json_error(array('message' => __('Your checkout session expired. Please refresh the page and try again.', 'banglaqr-payment-gateway-by-oi')));
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        // Ensure session cookie and token exist for guest uploads
        $session_token = '';
        if (WC()->session) {
            if (!WC()->session->has_session()) {
                WC()->session->set_customer_session_cookie(true);
            }
            $session_token = WC()->session->get('oi_banglaqr_session_token');
            if (empty($session_token)) {
                $session_token = wp_generate_password(32, false);
                WC()->session->set('oi_banglaqr_session_token', $session_token);
            }
        }

        // 1. Process Base64 payload (bypasses PHP upload_max_filesize completely)
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (!empty($_POST['image_base64'])) {
            $base64_data = wp_unslash($_POST['image_base64']);
            if (preg_match('/^data:image\/(jpeg|jpg|png|webp|gif);base64,([A-Za-z0-9+\/=\s]+)$/', $base64_data, $matches)) {
                $ext = strtolower($matches[1]);
                if ($ext === 'jpeg') {
                    $ext = 'jpg';
                }
                $decoded = base64_decode($matches[2]);

                if ($decoded !== false) {
                    $tmp_name = wp_tempnam('receipt');
                    file_put_contents($tmp_name, $decoded);
                    $wp_filetype = wp_check_filetype_and_ext($tmp_name, 'receipt.' . $ext);
                    
                    if (empty($wp_filetype['ext']) || empty($wp_filetype['type']) || !in_array($wp_filetype['type'], array('image/jpeg', 'image/png', 'image/webp', 'image/gif'))) {
                        @unlink($tmp_name);
                        wp_send_json_error(array('message' => __('Please upload a valid image file (JPEG, PNG, or WebP).', 'banglaqr-payment-gateway-by-oi')));
                    }
                    $ext = $wp_filetype['ext'];
                    @unlink($tmp_name);

                    $raw_name = !empty($_POST['image_name']) ? sanitize_file_name(wp_unslash($_POST['image_name'])) : 'receipt.jpg';
                    $clean_name = preg_replace('/\.[^.]+$/', '', $raw_name);
                    if (empty($clean_name)) {
                        $clean_name = 'receipt';
                    }
                    $filename_to_save = 'receipt_' . wp_generate_password(8, false) . '.' . $ext;

                    $upload = wp_upload_bits($filename_to_save, null, $decoded);

                    if (!empty($upload['error'])) {
                        wp_send_json_error(array('message' => $upload['error']));
                    }

                    $filename = $upload['file'];
                    $wp_filetype = wp_check_filetype($filename, null);

                    $attachment = array(
                        'post_mime_type' => !empty($wp_filetype['type']) ? $wp_filetype['type'] : 'image/jpeg',
                        'post_title'     => $clean_name,
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    );

                    $attachment_id = wp_insert_attachment($attachment, $filename);

                    if (is_wp_error($attachment_id)) {
                        wp_send_json_error(array('message' => __('We could not save your receipt image. Please try uploading again.', 'banglaqr-payment-gateway-by-oi')));
                    }

                    $attachment_data = wp_generate_attachment_metadata($attachment_id, $filename);
                    wp_update_attachment_metadata($attachment_id, $attachment_data);

                    // Security mark to link receipt to customer session
                    update_post_meta($attachment_id, '_oi_banglaqr_pending_upload', '1');
                    if (!empty($session_token)) {
                        update_post_meta($attachment_id, '_oi_banglaqr_uploader_token', $session_token);
                    }

                    wp_send_json_success(array(
                        'id'            => $attachment_id,
                        'attachment_id' => $attachment_id,
                        'url'           => wp_get_attachment_url($attachment_id)
                    ));
                }
            }
        }

        // 2. Fallback: Standard $_FILES handling
        if (empty($_FILES['oi_banglaqr_file']) || !empty($_FILES['oi_banglaqr_file']['error'])) {
            $error_message = __('Please select a receipt image file to upload.', 'banglaqr-payment-gateway-by-oi');
            if (!empty($_FILES['oi_banglaqr_file']['error'])) {
                $error_code = intval($_FILES['oi_banglaqr_file']['error']);
                if ($error_code === UPLOAD_ERR_INI_SIZE || $error_code === UPLOAD_ERR_FORM_SIZE) {
                    $error_message = __('This image file is too large. Please choose a smaller photo or screenshot.', 'banglaqr-payment-gateway-by-oi');
                }
            }
            wp_send_json_error(array('message' => $error_message));
        }

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
            wp_send_json_error(array('message' => __('We could not save your receipt image. Please try uploading again.', 'banglaqr-payment-gateway-by-oi')));
        }

        $attachment_data = wp_generate_attachment_metadata($attachment_id, $filename);
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        // Security mark to link receipt to customer session
        update_post_meta($attachment_id, '_oi_banglaqr_pending_upload', '1');
        if (!empty($session_token)) {
            update_post_meta($attachment_id, '_oi_banglaqr_uploader_token', $session_token);
        }

        wp_send_json_success(array(
            'id'            => $attachment_id,
            'attachment_id' => $attachment_id,
            'url'           => wp_get_attachment_url($attachment_id)
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

            // Security check: Only update post parent if the ID belongs to an attachment
            if ($receipt_id > 0) {
                $receipt_post = get_post($receipt_id);
                if ($receipt_post && $receipt_post->post_type === 'attachment' && (int)$receipt_post->post_parent === 0) {
                    // Verify the attachment was uploaded via our gateway
                    if (get_post_meta($receipt_id, '_oi_banglaqr_pending_upload', true) === '1') {
                        $uploader_token = get_post_meta($receipt_id, '_oi_banglaqr_uploader_token', true);
                        $session_token  = WC()->session ? WC()->session->get('oi_banglaqr_session_token') : '';
                        
                        $is_valid = false;
                        if (!empty($uploader_token) && !empty($session_token) && hash_equals((string)$uploader_token, (string)$session_token)) {
                            $is_valid = true;
                        } elseif (empty($uploader_token)) {
                            $is_valid = true;
                        }

                        if ($is_valid) {
                            // Set the attachment as media parent of this order
                            wp_update_post(array(
                                'ID' => $receipt_id,
                                'post_parent' => $order_id,
                            ));
                            // Remove pending mark so cleanup cron will not delete it
                            delete_post_meta($receipt_id, '_oi_banglaqr_pending_upload');
                            delete_post_meta($receipt_id, '_oi_banglaqr_uploader_token');
                        }
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
            $note_details[] = __('Receipt: Attached', 'banglaqr-payment-gateway-by-oi');
        }

        $order_note = __('Awaiting payment verification for Bangla QR.', 'banglaqr-payment-gateway-by-oi');
        if (!empty($note_details)) {
            $order_note .= ' (' . implode(' | ', $note_details) . ')';
        }

        // Set order status based on admin configuration
        $order_status = $this->get_option('order_status', 'on-hold');
        $order->update_status($order_status, $order_note);

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
