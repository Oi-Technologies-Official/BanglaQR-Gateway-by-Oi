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

        // Check for WooCommerce order screens
        $is_order_page = false;
        $screen = get_current_screen();
        if ($screen) {
            if ($screen->id === 'shop_order' || $screen->id === 'woocommerce_page_wc-orders' || $screen->post_type === 'shop_order') {
                $is_order_page = true;
            }
        }

        if (!$is_settings_page && !$is_order_page) {
            return;
        }

        $handle = 'banglaqr-admin';

        // Enqueue styles
        wp_enqueue_style($handle, OI_BANGLAQR_URL . 'includes/css/admin.css', array(), OI_BANGLAQR_VERSION);

        // Enqueue script only on settings page to handle Repeatable QR manager
        if ($is_settings_page) {
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');

            wp_enqueue_script($handle, OI_BANGLAQR_URL . 'includes/js/admin.js', array('jquery', 'jquery-ui-sortable'), OI_BANGLAQR_VERSION, true);

            wp_localize_script($handle, 'oi_banglaqr_admin_params', array(
                'media_title' => __('Select QR Image', 'banglaqr-payment-gateway-by-oi'),
                'media_button_text' => __('Use QR Code', 'banglaqr-payment-gateway-by-oi'),
                'confirm_delete' => __('Are you sure you want to delete this QR account?', 'banglaqr-payment-gateway-by-oi'),
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
                <div class="banglaqr-admin-order-receipt-card"
                    style="margin-top: 20px; border: 1px solid #cbd5e1; border-radius: 8px; background-color: #f8fafc; padding: 15px; max-width: 420px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);">
                    <div class="banglaqr-receipt-card-header"
                        style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 12px;">
                        <span class="banglaqr-receipt-bank-name" style="font-weight: 700; color: #1e293b; font-size: 14px;">
                            <?php echo esc_html(!empty($selected_qr) ? $selected_qr : __('Bangla QR Payment Details', 'banglaqr-payment-gateway-by-oi')); ?>
                        </span>
                        <?php if ($image_url): ?>
                                <a href="<?php echo esc_url($image_url); ?>" target="_blank" class="banglaqr-view-full-link"
                                    style="color: #137833; font-weight: 600; font-size: 12px; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                                    <?php esc_html_e('View Full Image', 'banglaqr-payment-gateway-by-oi'); ?>
                                    <span class="dashicons dashicons-external"
                                        style="font-size: 14px; width: 14px; height: 14px; line-height: 14px;"></span>
                                </a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($trx_id)): ?>
                            <div class="banglaqr-admin-trx-box"
                                style="margin-bottom: 10px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div
                                        style="font-size: 10px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">
                                        <?php esc_html_e('Transaction ID', 'banglaqr-payment-gateway-by-oi'); ?></div>
                                    <div
                                        style="font-size: 14px; font-weight: 700; color: #0f172a; font-family: monospace; letter-spacing: 0.5px;">
                                        <?php echo esc_html($trx_id); ?></div>
                                </div>
                                <button type="button" class="button button-small"
                                    onclick="navigator.clipboard.writeText('<?php echo esc_js($trx_id); ?>'); this.innerText='<?php echo esc_js(__('Copied!', 'banglaqr-payment-gateway-by-oi')); ?>'; setTimeout(()=>{this.innerText='<?php echo esc_js(__('Copy', 'banglaqr-payment-gateway-by-oi')); ?>'}, 2000);"
                                    style="font-size: 11px; height: 26px; line-height: 24px; font-weight: 600;">
                                    <?php esc_html_e('Copy', 'banglaqr-payment-gateway-by-oi'); ?>
                                </button>
                            </div>
                    <?php endif; ?>

                    <?php if ($image_url): ?>
                            <div class="banglaqr-receipt-image-preview-container"
                                style="border-radius: 6px; overflow: hidden; border: 1px solid #e2e8f0; background: #fff; display: flex; align-items: center; justify-content: center; padding: 5px;">
                                <img src="<?php echo esc_url($image_url); ?>"
                                    alt="<?php esc_attr_e('Payment Receipt', 'banglaqr-payment-gateway-by-oi'); ?>"
                                    class="banglaqr-receipt-preview-img"
                                    style="max-width: 100%; height: auto; display: block; border-radius: 4px;" />
                            </div>
                    <?php elseif (empty($trx_id)): ?>
                            <div class="banglaqr-admin-order-receipt-card error-card">
                                <p class="banglaqr-receipt-missing-notice" style="margin:0; color:#b91c1c; font-size:12px;">
                                    <?php esc_html_e('Receipt attachment URL could not be resolved.', 'banglaqr-payment-gateway-by-oi'); ?>
                                </p>
                            </div>
                    <?php endif; ?>
                </div>
                <?php
    }
}
