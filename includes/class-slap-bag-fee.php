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

            $per_restaurant = isset($feeSettings['per_restaurant'])
                                    ? $feeSettings['per_restaurant']
                                    : null;

            // Check if is taxable and set class
            if($is_active == 1){
                global $woocommerce;
                $items = $woocommerce->cart->get_cart();
                $arrRestaurants = array();
                
                // Loop cart to get restaurants
                foreach($items as $item => $values) {
                    $productid = $values['data']->get_id();
                    $variation = wc_get_product($productid);
                    // ****************************************************************************************
                    // se o produto tiver variações, ie for variable, então precisamos obter o produto parent
                    // porque o produto variable não tem categorias, vêm vazias
                    // ****************************************************************************************
                    if ($variation->get_parent_id())
                    {
                        $productid = $variation->get_parent_id();
                    }

                    // Get restaurant IDs
                    $restaurant_list_ids = wp_get_post_terms($productid,'product_cat',array('fields'=>'ids'));

                    if($restaurant_list_ids){
                        foreach ($restaurant_list_ids as $id) {
                    
                            // Get parent IDs
                            $parentcats = get_ancestors($id, 'product_cat');
                            if($parentcats) {
                                foreach ($parentcats as $cat) {
                                
                                    // Get e_restaurente ACF da categoria parent
                                    $e_restaurante = get_field( "e_restaurante", 'product_cat_' . $cat);
                                    if($e_restaurante) {
                                        foreach ($e_restaurante as $rest) {
                                            if($rest) {
        
                                                // If e_restaurante add to array if not there already
                                                if(!in_array($cat, $arrRestaurants)) {
                                                    $arrRestaurants[] = $cat;
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                // Get e_restaurente ACF da categoria se é parent
                                $e_restaurante = get_field( "e_restaurante", 'product_cat_' . $id);
                                if($e_restaurante) {
                                    foreach ($e_restaurante as $rest) {
                                        if($rest) {
                                            
                                            // If e_restaurante add to array if not there already
                                            if(!in_array($id, $arrRestaurants)) {
                                                $arrRestaurants[] = $id;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Check if charging one bag per restaurant or single bag per order
                if($per_restaurant) {
                    $restaurante_bag = count($arrRestaurants);
                } else {
                    $restaurante_bag = 1;
                }

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
                    WC()->cart->add_fee( __($name .' x ' . $restaurante_bag, 'woocommerce'), $amount*$restaurante_bag, $is_taxable, $tax_class);
                }
            }
        }
    }
}