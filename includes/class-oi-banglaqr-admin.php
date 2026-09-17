<?php
/**
 * Admin logic and screens interface for Oi_BanglaQR
 */

defined('ABSPATH') || exit;

class Oi_BanglaQR_Admin
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_order_slip_in_admin'));
    }

    /**
     * Enqueue admin CSS and JS.
     *
     * @param string $hook Admin screen hook.
     */
    public function enqueue_admin_assets($hook)
    {
        // Enqueue only on settings page of our gateway
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $is_settings_page = isset($_GET['page']) && sanitize_text_field(wp_unslash($_GET['page'])) === 'wc-settings' && isset($_GET['section']) && sanitize_text_field(wp_unslash($_GET['section'])) === 'oi_banglaqr';

        // Check for WooCommerce order screens (Classic and HPOS)
        $is_order_page = false;
        $screen = get_current_screen();
        if ($screen) {
            if ($screen->id === 'shop_order' || $screen->id === 'woocommerce_page_wc-orders' || strpos($screen->id, 'wc-orders') !== false || (isset($screen->post_type) && $screen->post_type === 'shop_order')) {
                $is_order_page = true;
            }
        }

        if (!$is_settings_page && !$is_order_page) {
            return;
        }

        $handle = 'banglaqr-admin';

        $css_ver = file_exists(OI_BANGLAQR_PATH . 'includes/css/admin.css') ? filemtime(OI_BANGLAQR_PATH . 'includes/css/admin.css') : OI_BANGLAQR_VERSION;
        $js_ver  = file_exists(OI_BANGLAQR_PATH . 'includes/js/admin.js') ? filemtime(OI_BANGLAQR_PATH . 'includes/js/admin.js') : OI_BANGLAQR_VERSION;

        // Enqueue styles
        wp_enqueue_style($handle, OI_BANGLAQR_URL . 'includes/css/admin.css', array(), $css_ver);

        // Enqueue script only on settings page to handle Repeatable QR manager
        if ($is_settings_page) {
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');

            wp_enqueue_script($handle, OI_BANGLAQR_URL . 'includes/js/admin.js', array('jquery', 'jquery-ui-sortable'), $js_ver, true);

            wp_localize_script($handle, 'oi_banglaqr_admin_params', array(
                'media_title' => __('Select QR Image', 'banglaqr-payment-gateway-by-oi'),
                'media_button_text' => __('Use This QR Image', 'banglaqr-payment-gateway-by-oi'),
                'confirm_delete' => __('Are you sure you want to remove this QR account?', 'banglaqr-payment-gateway-by-oi'),
                'default_qr_url' => OI_BANGLAQR_URL . 'includes/img/testqr.png',
            ));
        }
    }

    /**
     * Display the uploaded bank receipt slip and/or Transaction ID in the admin order details screen.
     * Works with post-based orders and WooCommerce High-Performance Order Storage (HPOS).
     *
     * @param WC_Order $order WooCommerce order object.
     */
    public function display_order_slip_in_admin($order)
    {
        if (!$order) {
            return;
        }

        // Verify if payment method is oi_banglaqr
        if ($order->get_payment_method() !== 'oi_banglaqr') {
            return;
        }

        $receipt_id = $order->get_meta('_oi_banglaqr_receipt_id');
        $trx_id = $order->get_meta('_oi_banglaqr_transaction_id');
        $selected_qr = $order->get_meta('_oi_banglaqr_selected_qr');

        // If neither is present, fallback to order transaction_id if set
        if (empty($trx_id) && $order->get_transaction_id()) {
            $trx_id = $order->get_transaction_id();
        }

        if (empty($receipt_id) && empty($trx_id)) {
            return;
        }

        $image_url = '';
        if (!empty($receipt_id)) {
            if (is_numeric($receipt_id)) {
                $image_url = wp_get_attachment_url($receipt_id);
            } else {
                $image_url = esc_url($receipt_id);
            }
        }
        ?>
        <div class="clear"></div>
        <div class="banglaqr-admin-order-receipt-card">
            <div class="banglaqr-receipt-card-header">
                <span class="banglaqr-receipt-bank-name" style="display:inline-flex; align-items:center; gap:6px;">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h2v2h-2z"/><path d="M18 14h3v3h-3z"/><path d="M14 18h3v3h-3z"/></svg>
                    <span><?php echo esc_html(!empty($selected_qr) ? $selected_qr : __('Bangla QR Payment Details', 'banglaqr-payment-gateway-by-oi')); ?></span>
                </span>
                <?php if ($image_url): ?>
                    <a href="<?php echo esc_url($image_url); ?>" target="_blank" rel="noopener noreferrer" class="banglaqr-view-full-link">
                        <span><?php esc_html_e('View Full Image', 'banglaqr-payment-gateway-by-oi'); ?></span>
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                <?php endif; ?>
            </div>

            <?php if (!empty($trx_id)): ?>
                <div class="banglaqr-admin-trx-box">
                    <div>
                        <div class="banglaqr-admin-trx-label" style="display:flex; align-items:center; gap:4px;">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
                            <span><?php esc_html_e('Transaction ID (TrxID)', 'banglaqr-payment-gateway-by-oi'); ?></span>
                        </div>
                        <div class="banglaqr-admin-trx-val">
                            <?php echo esc_html($trx_id); ?>
                        </div>
                    </div>
                    <button type="button" class="button button-small banglaqr-copy-btn banglaqr-btn-with-icon"
                        onclick="if(navigator.clipboard){navigator.clipboard.writeText('<?php echo esc_js($trx_id); ?>');} this.querySelector('span').innerText='<?php echo esc_js(__('Copied!', 'banglaqr-payment-gateway-by-oi')); ?>'; setTimeout(()=>{this.querySelector('span').innerText='<?php echo esc_js(__('Copy', 'banglaqr-payment-gateway-by-oi')); ?>'}, 2000);">
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        <span><?php esc_html_e('Copy', 'banglaqr-payment-gateway-by-oi'); ?></span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($image_url): ?>
                <div class="banglaqr-receipt-image-preview-container">
                    <img src="<?php echo esc_url($image_url); ?>"
                        alt="<?php esc_attr_e('Payment Receipt', 'banglaqr-payment-gateway-by-oi'); ?>"
                        class="banglaqr-receipt-preview-img" />
                </div>
            <?php elseif (!empty($receipt_id)): ?>
                <div class="banglaqr-receipt-missing-box">
                    <p class="banglaqr-receipt-missing-notice" style="display:flex; align-items:center; gap:6px;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span><?php esc_html_e('The uploaded receipt screenshot could not be loaded or was removed from the media library.', 'banglaqr-payment-gateway-by-oi'); ?></span>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
