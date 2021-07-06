<?php

class SLAP_Bag_Fee {

    public function run() {

        add_action( 'woocommerce_cart_calculate_fees', 'add_bag_fee' );

        function add_bag_fee(){

            // Set vars to prevent PHP errors on WP AJAX notice
            $amount = 0;
            $is_taxable = false;
            $name = "";

            // Get admin settings
            $feeSettings = get_option('bag_fee_plugin_options');
            $name = $feeSettings['name'];
            $amount = $feeSettings['amount'];
            $is_taxable = $feeSettings['is_taxable'];
            $tax_class = "standard";

            // Check if is taxable and set class
            if($is_taxable == 1) {
                $taxable = true;
                $tax_class = $feeSettings['tax_class'];;
            } else {
                $taxable = false;
            }

            if($amount > 0) {
                WC()->cart->add_fee( __($name , 'woocommerce'), $amount, $taxable, $tax_class);
            }

        }
    }
}