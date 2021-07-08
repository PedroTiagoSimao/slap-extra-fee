<?php

class SLAP_Bag_Fee {

    public function run() {

        add_action( 'woocommerce_cart_calculate_fees', 'add_bag_fee' );

        function add_bag_fee(){

            // Set vars to prevent PHP errors on WP AJAX notice
            $amount = 0;

            // Get admin settings values
            $feeSettings = get_option('bag_fee_plugin_options');
            $is_active = isset($feeSettings['is_active'])
                            ? $feeSettings['is_active']
                            : null;
            
            $name = isset($feeSettings['name'])
                            ? $feeSettings['name']
                            : null;
            
            $amount = isset($feeSettings['amount'])
                            ? $feeSettings['amount']
                            : 0;

            $is_taxable = isset($feeSettings['is_taxable'])
                            ? $feeSettings['is_taxable']
                            : null;
            
            $tax_class = "standard";

            // Check if is taxable and set class
            if($is_active == 1){

                if($is_taxable == 1) {
                    // Define as taxable
                    $is_taxable = true;

                    // Get class, set to standard if none is set
                    $tax_class = isset($feeSettings['tax_class'])
                            ? $feeSettings['tax_class']
                            : "standard";
                } else {
                    // Define as not taxable
                    $is_taxable = false;
                }

                // Set WC add_fee
                if($amount > 0) {
                    WC()->cart->add_fee( __($name , 'woocommerce'), $amount, $is_taxable, $tax_class);
                }
            }
        }
    }
}