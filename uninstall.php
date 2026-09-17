<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package BanglaQR_Payment_Gateway_By_Oi
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete the plugin options
delete_option('woocommerce_oi_banglaqr_settings');