<?php

class Wc_Rw_Order_Currency_Changer_Woo_Settings extends WC_Settings_Page {

    public function __construct() {
        $this->id    = 'wc_rw_order_currency_changer_settings'; // Tab identifier
        $this->label = __('Order Currency', 'wc-rw-order-currency-changer'); // Tab name
        parent::__construct();
    }

    /**
     * Retrieve the settings for the order currency changer.
     *
     * This method defines the settings structure, including titles, descriptions,
     * input types, and default values, for the plugin's admin settings page.
     *
     * @param string $current_section The current section ID (optional, default: '').
     * @return array The settings array to be displayed and saved in WooCommerce.
     */
    public function get_settings($current_section = '') {
        $settings = [
            [
                'title' => __('Target order currency ', 'wc-rw-order-currency-changer'),
                'type'  => 'title',
                'desc'  => __('Set target order currency and exchange rate.', 'wc-rw-order-currency-changer'),
                'id'    => 'wc_rw_order_currency_changer_settings_section_target_currency',
            ],
            [
                'title'    => __('Target order currency', 'wc-rw-order-currency-changer'),
                'id'       => 'wc_rw_occh_target_currency',
                'type'     => 'select',
                'options'  => get_woocommerce_currencies(), // get a list of all available currencies from WooCommerce
                'default'  => 'USD',
                'desc'     => __('Select the currency to which the order will be converted.', 'wc-rw-order-currency-changer'),
                'desc_tip' => true,
            ],
            [
                'title'    => __('Exchange rate to the base currency', 'wc-rw-order-currency-changer'),
                'id'       => 'wc_rw_occh_target_currency_rate',
                'type'     => 'number',
                'default'  => '1.0',
                'desc'     => __('Specify the exchange rate to the WooCommerce base currency.', 'wc-rw-order-currency-changer'),
                'desc_tip' => true,
                'custom_attributes' => [
                    'step' => '0.001',
                    'min'  => '0.01',
                    'max'  => '1000'
                ],
            ],
            [
                'type' => 'sectionend',
                'id'   => 'wc_rw_order_currency_changer_settings_section_target_currency',
            ],

            [
                'title' => __('Exchange rates', 'wc-rw-order-currency-changer'),
                'type'  => 'title',
                'desc'  => __('Set exchange rates for the currencies used in the e-shop.', 'wc-rw-order-currency-changer'),
                'id'    => 'wc_rw_order_currency_changer_settings_section_exchange_rates',
            ],

            [
                'title'    => __('USD', 'wc-rw-order-currency-changer'),
                'id'       => 'wc_rw_occh_rate_USD',
                'type'     => 'number',
                'default'  => '1.0',
                'desc'     => __('Specify the exchange rate to the WooCommerce base currency.', 'wc-rw-order-currency-changer'),
                'desc_tip' => true,
                'custom_attributes' => [
                    'step' => '0.001',
                    'min'  => '0.01',
                    'max'  => '1000'
                ],
            ],

            [
                'title'    => __('AED', 'wc-rw-order-currency-changer'),
                'id'       => 'wc_rw_occh_rate_AED',
                'type'     => 'number',
                'default'  => '1.0',
                'desc'     => __('Specify the exchange rate to the WooCommerce base currency.', 'wc-rw-order-currency-changer'),
                'desc_tip' => true,
                'custom_attributes' => [
                    'step' => '0.001',
                    'min'  => '0.01',
                    'max'  => '1000'
                ],
            ],
            [
                'title'    => __('SAR', 'wc-rw-order-currency-changer'),
                'id'       => 'wc_rw_occh_rate_SAR',
                'type'     => 'number',
                'default'  => '1.0',
                'desc'     => __('Specify the exchange rate to the WooCommerce base currency.', 'wc-rw-order-currency-changer'),
                'desc_tip' => true,
                'custom_attributes' => [
                    'step' => '0.001',
                    'min'  => '0.01',
                    'max'  => '1000'
                ],
            ],
            [
                'title'    => __('KZT', 'wc-rw-order-currency-changer'),
                'id'       => 'wc_rw_occh_rate_SAR',
                'type'     => 'number',
                'default'  => '1.0',
                'desc'     => __('Specify the exchange rate to the WooCommerce base currency.', 'wc-rw-order-currency-changer'),
                'desc_tip' => true,
                'custom_attributes' => [
                    'step' => '0.001',
                    'min'  => '0.01',
                    'max'  => '1000'
                ],
            ],
            [
                'type' => 'sectionend',
                'id'   => 'wc_rw_order_currency_changer_settings_section',
            ],
        ];

        return apply_filters('woocommerce_get_settings_' . $this->id, $settings);
    }

    /**
     * Save the settings after validating custom inputs.
     *
     * This method retrieves the settings, validates them using `validate_custom_settings`,
     * and saves them only if the validation passes.
     *
     * @return void
     */
    public function save() {
        $settings = $this->get_settings();
        if($this->validate_custom_settings($settings)){
            WC_Admin_Settings::save_fields($settings);
        };

    }

    /**
     * Validate custom settings provided by the user.
     *
     * This method performs validation for numeric and select fields in the provided settings.
     * Numeric fields are checked for minimum, maximum, and total digit constraints,
     * while select fields are validated against the available currencies.
     * Errors are added using `WC_Admin_Settings::add_error()` if validation fails.
     *
     * @param array $settings An array of settings to validate.
     * @return bool True if all settings are valid, false otherwise.
     */
    protected function validate_custom_settings(array $settings): bool
    {
        if (empty($settings) || !is_array($settings)) {
            WC_Admin_Settings::add_error(
                __('Something went wrong. Please restart the page and try again.', 'wc-rw-order-currency-changer')
            );
            return false;
        }

        $currencies = get_woocommerce_currencies();
        $validation_result = true;

        foreach ($settings as $setting) {
            if (!array_key_exists($setting['id'], $_POST)) {
                continue;
            }

            $value = $_POST[$setting['id']];

            // Validate numeric fields
            if (isset($setting['type']) && $setting['type'] === 'number') {
                $value = floatval($value);

                if ($value == 0) {
                    WC_Admin_Settings::add_error(sprintf(
                        __('The value for "%s" must be filled.', 'wc-rw-order-currency-changer'),
                        $setting['title']
                    ));
                    $validation_result = false;
                }

                if ($value < 0) {
                    WC_Admin_Settings::add_error(sprintf(
                        __('The value for "%s" can\'t be less than 0.', 'wc-rw-order-currency-changer'),
                        $setting['title']
                    ));
                    $validation_result = false;
                }

                if ($value > 1000) {
                    WC_Admin_Settings::add_error(sprintf(
                        __('The value for "%s" exceeded the maximum limit and has been set to 1000.', 'wc-rw-order-currency-changer'),
                        $setting['title']
                    ));
                    $validation_result = false;
                }

                if (strlen(preg_replace('/[^\d]/', '', (string)$value)) > 10) {
                    WC_Admin_Settings::add_error(sprintf(
                        __('The number of digits for "%s" exceeded the maximum limit of 10.', 'wc-rw-order-currency-changer'),
                        $setting['title']
                    ));
                    $validation_result = false;
                }
            }

            // Validate select fields
            if (isset($setting['type']) && $setting['type'] === 'select') {
                if (!array_key_exists($value, $currencies)) {
                    $_POST[$setting['id']] = 'USD';
                }
            }
        }

        return $validation_result;

    }


}
