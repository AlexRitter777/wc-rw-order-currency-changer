<?php


// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * List of options to delete.
 */
$options_to_delete = [
    'wc_rw_occh_target_currency',
    'wc_rw_occh_target_currency_rate',
    'wc_rw_occh_rate_USD',
    'wc_rw_occh_rate_AED',
    'wc_rw_occh_rate_SAR',
];

// Delete each option
foreach ($options_to_delete as $option) {
    delete_option($option);
}

/**
 * Clean up transients related to the plugin.
 */
delete_transient('wc-rw-choc-current-currency');