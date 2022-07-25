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
            
            $feeTitle = isset($feeSettings['name'])
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

            $charge_per = isset($feeSettings['charge_per'])
                                ? $feeSettings['charge_per']
                                : null;

            // Tags
            $tagCount = 0;

            if($is_active == 1){
                global $woocommerce;
                $items = $woocommerce->cart->get_cart();
                $arrCategories = array();
                
                // Loop cart to get categories
                foreach($items as $item => $values) {
                    $productid = $values['data']->get_id();
                    $variation = wc_get_product($productid);
                    // ****************************************************************************************
                    // if product variable, get parent because variable does not have category
                    // ****************************************************************************************
                    if ($variation->get_parent_id())
                    {
                        $productid = $variation->get_parent_id();
                    }

                    // Get category IDs
                    $category_list_ids = wp_get_post_terms($productid,'product_cat',array('fields'=>'ids'));

                    if($category_list_ids){
                        foreach ($category_list_ids as $id) {
                            // Get parent IDs
                            $parentcats = get_ancestors($id, 'product_cat');
                            if($parentcats) {
                                foreach ($parentcats as $cat) {
                                        if(!in_array($cat, $arrCategories)) {
                                        $arrCategories[] = $cat;
                                    }
                                }
                            } else {  
                                if(!in_array($id, $arrCategories)) {
                                    $arrCategories[] = $id;
                                }
                            }
                        }
                    }

                    // Get product tags
                    $tags = wp_get_post_terms( $productid, 'product_tag' );
                    if ( ! empty( $tags ) && ! is_wp_error( $tags ) ){
                        foreach ( $tags as $tag ) {
                            if($tag->name == 'taxa'){
                                $tagCount++;
                            }
                        }
                    }
                }

                // Charge per order
                if($charge_per == 'per_order') {
                    $bag = 1;
                }

                // Charge per item
                if($charge_per == 'per_item') {
                    $bag = $woocommerce->cart->get_cart_contents_count();
                }

                // Charge per per_category
                if($charge_per == 'per_category') {
                    $bag = count($arrCategories);
                }

                // Charge per per_tag
                if($charge_per == 'per_tag') {
                    $bag = $tagCount;
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

                echo '<br>charge per: ' . $charge_per .'<br>';
                echo 'bags: ' . $bag;

                // Set WC add_fee
                if($amount > 0) {
                    WC()->cart->add_fee( __($feeTitle .' x ' . $bag, 'woocommerce'), $amount*$bag, $is_taxable, $tax_class);
                }
            }
        }
    }
}