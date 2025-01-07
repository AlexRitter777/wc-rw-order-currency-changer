<?php

/**
 * Class Wc_Rw_Order_Currency_Changer_Init
 *
 * This class handles currency changes for orders in WooCommerce. It modifies the order currency at checkout
 * based on predefined exchange rates and restores the original site currency after the order is placed.
 */
class Wc_Rw_Order_Currency_Changer_Init
{
    /**
     * Constructor.
     *
     * Initializes the class by adding filters and actions to handle currency changes during the checkout process
     * and after the order placement.
     */
    public function __construct()
    {
        add_filter('woocommerce_cart_totals_order_total_html',[$this, 'show_total_in_target_currency']);
        add_action('woocommerce_checkout_create_order', [$this, 'change_order_currency'], 10, 2);
        add_action('woocommerce_thankyou', [$this, 'restore_site_currency'], 10);
        add_filter('woocommerce_currency', function($currency) {

            if (is_wc_endpoint_url('order-pay')) {
                $order_id = wc_get_order_id_by_order_key($_GET['key'] ?? '');
                if ($order_id) {
                    $order = wc_get_order($order_id);
                    if ($order instanceof WC_Order) {
                        $target_currency = get_option('wc_rw_occh_target_currency', 'USD');
                        return $target_currency;
                    }
                }
            }

            return $currency;
        });
    }

    /**
     * Check if the cart's currency matches the target order currency and has a valid exchange rate.
     *
     * @return bool True if the conditions are met, false otherwise.
     */
    private function check_cart_currency(): bool
    {
        $woo_currency = get_woocommerce_currency();
        $target_order_currency = get_option('wc_rw_occh_target_currency', 'USD');
        $is_currency_valid = $woo_currency !== $target_order_currency;
        $is_currency_allowed = get_option("wc_rw_occh_rate_$woo_currency", false) !== false;

        return $is_currency_valid && $is_currency_allowed;
    }

    /**
     * Get the exchange rate for the order currency conversion.
     *
     * @return float Returns the calculated exchange rate between the current WooCommerce currency
     *               and the target order currency.
     */
    private function get_order_exchange_rate(): float
    {
        $woo_currency = get_woocommerce_currency();
        $target_order_currency_rate = get_option('wc_rw_occh_target_currency_rate', '1');
        $cart_currency_rate = get_option("wc_rw_occh_rate_$woo_currency", '1');

        return round($target_order_currency_rate/$cart_currency_rate,3);

    }

    /**
     * Calculate the total in the target currency.
     *
     * @param mixed $total The total amount in the current currency.
     * @return float Returns the total converted into the target currency.
     */
    private function get_total_in_target_currency($total): float
    {
        return round($this->get_order_exchange_rate() * (float)$total ,2);
    }


    /**
     * Modify the cart total displayed on the checkout page.
     *
     * If the target currency differs from the current WooCommerce currency,
     * displays a notice about the conversion and shows the final amount in the target currency.
     *
     * @return string Returns the modified total HTML with additional conversion information.
     */
    public function show_total_in_target_currency(): string
    {

        $total = WC()->cart->get_total('edit');

        if(is_checkout() && $this->check_cart_currency()){
            $target_order_currency = get_option('wc_rw_occh_target_currency');
            $custom_total = '<strong>' . wc_price($total) . '</strong><br><div class="wc-rw-order-currency-changer-change-info wc-rw-order-currency-changer-notice wc-rw-order-currency-changer-notice-warning"><small>' .
                esc_html__("Your order will be processed and paid in ", 'wc-rw-order-currency-changer') . $target_order_currency . '</small><br>' .
                '<small>' . esc_html__("The final amount is ", 'wc-rw-order-currency-changer') . '<strong>' . $this->get_total_in_target_currency($total) . ' ' .
                $target_order_currency . '</strong></small></div>';
        }else{
            $custom_total = '<strong>' . wc_price($total) . '</strong>';
        }

        return $custom_total;
    }

    /**
     * Change the order currency and recalculate totals during order creation.
     *
     * Converts all item totals, shipping costs, and the overall total to the target currency.
     *
     * @param WC_Order $order The WooCommerce order object.
     * @param array $data The raw posted data for the checkout form.
     * @return void
     * @throws WC_Data_Exception
     */
    public function change_order_currency(WC_Order $order, array $data) : void{

        if($this->check_cart_currency()){
            $woo_currency = get_woocommerce_currency();
            set_transient('wc-rw-choc-current-currency', $woo_currency);

            $target_order_currency = get_option('wc_rw_occh_target_currency', 'USD');
            $target_order_currency_rate = $this->get_order_exchange_rate();

            $order->set_currency($target_order_currency);

            foreach ($order->get_items() as $item_id => $item) {
                $original_price = $item->get_total();
                $item->set_total($original_price * $target_order_currency_rate);
                $item->set_subtotal($item->get_subtotal() * $target_order_currency_rate);
            }

            $order->set_shipping_total($order->get_shipping_total() * $target_order_currency_rate);

            $order->set_total($order->get_total() * $target_order_currency_rate);

            $order->save();

        }

    }

    /**
     * Restore the original site currency after order placement.
     *
     * Retrieves the original currency stored in a transient and updates WooCommerce session and Fox Currency Switcher
     * storage accordingly. The original currency is also enforced using a filter with the highest priority.
     *
     * @return void
     */
    public function restore_site_currency() {

        $original_currency = get_transient('wc-rw-choc-current-currency');

        // Set the original currency in the WooCommerce session
        WC()->session->set('currency', $original_currency);

        // Update the currency in Fox Currency Switcher storage
        if (class_exists('WOOCS_STORAGE')) {
            $woocs_storage = new WOOCS_STORAGE();
            $woocs_storage->set_val('woocs_current_currency', $original_currency);
        }

        // Force the original currency using a filter with the highest priority
        add_filter('woocommerce_currency', function () use ($original_currency) {
            return $original_currency;
        }, PHP_INT_MAX);

        delete_transient('wc-rw-choc-current-currency');

    }

}