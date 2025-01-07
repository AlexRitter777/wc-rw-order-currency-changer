# WooCommerce RW Order Currency Changer

## Description
The **WooCommerce RW Order Currency Changer** plugin enables the ability to process WooCommerce orders in a target currency. This includes recalculating order totals, updating order currency metadata, and restoring the original currency after the order is placed.

## Tested Plugins
The plugin has been tested and is fully compatible with the following plugins:
1. **Platiti UniAdapter Plugin** (version **99.7**)
2. **WooCommerce Fox Currency Switcher PRO** (version **2.4.2.3**)

## Special Configuration for Platiti UniAdapter Plugin
To ensure compatibility with the **Platiti UniAdapter Plugin**, the following modification must be applied:

### File:
`plugins\WooCommerceAdapter\lib\WooCommerceAdapter.php`

### Modification:
Locate the `getOrderToPayInfo` method and replace the line:
```php
$orderToPayInfo->currency = get_woocommerce_currency();
With:
$orderToPayInfo->currency = $order->get_currency();

This change ensures that the plugin respects the currency set for the specific WooCommerce order.

