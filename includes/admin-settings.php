<?php


// Add admin settings page
add_action('admin_menu','add_extra_fee_menu', 9); 
function add_extra_fee_menu() {
    // Check if SLAP menu exists
    global $menu;
    $menuExist = false;
    foreach($menu as $item) {
        if(strtolower($item[0]) == strtolower('SLAP')) {
            $menuExist = true;
        }
    }
    if(!$menuExist) {
        add_menu_page( 'Definições', 'SLAP', 'administrator', 'slap-settings', 'pages_extra_fee');
        $extraFeeSubMenu = add_submenu_page('slap-settings', 'Extra Fee Settings', 'Extra Fee Settings', 'administrator', 'settings-extra-fee', 'displayExtraFeeSettings');
        add_action( 'load-' . $extraFeeSubMenu, 'load_admin_js' );
    } else {
        $extraFeeSubMenu = add_submenu_page('slap-settings', 'Extra Fee Settings', 'Extra Fee Settings', 'administrator', 'settings-extra-fee', 'displayExtraFeeSettings');
        add_action( 'load-' . $extraFeeSubMenu, 'load_admin_js' );
    }
}

function load_admin_js(){
    // Unfortunately we can't just enqueue our scripts here - it's too early. So register against the proper action hook to do it
    add_action( 'admin_enqueue_scripts', 'enqueue_admin_js' );
}

function enqueue_admin_js(){
    // Isn't it nice to use dependencies and the already registered core js files?
    wp_enqueue_script( 'bag-fee-script', plugin_dir_url( __FILE__) . '/assets/js/bag-fee.js', array('jquery'), '1.0' );
    wp_enqueue_style( 'bag-fee-css', plugin_dir_url( __FILE__) . '/assets/css/bag-fee.css');
}

function pages_extra_fee() {
  ?>
    <h2>SLAP</h2>
<?php
}

function displayExtraFeeSettings(){
    ?>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'extra_fee_plugin_options' );
        do_settings_sections( 'extra_fee_plugin' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
<?php
}

function extra_fee_register_settings() {
    register_setting( 'extra_fee_plugin_options', 'extra_fee_plugin_options', 'extra_fee_plugin_options_validate' );
    add_settings_section( 'extra_extra_settings', 'Taxa Extra', 'extra_fee_settings', 'extra_fee_plugin' );
}
add_action( 'admin_init', 'extra_fee_register_settings' );

function extra_fee_settings() {
    $options = get_option( 'extra_fee_plugin_options' );

    // Get available tax classes
    $all_tax_rates = [];

    // Retrieve all tax classes.
    $tax_classes = WC_Tax::get_tax_classes();
    if ( !in_array( '', $tax_classes ) ) { // Make sure "Standard rate" (empty class name) is present.
        array_unshift( $tax_classes, '' );
    }

    // For each tax class, get all rates.
    foreach ( $tax_classes as $tax_class ) {
        $taxes = WC_Tax::get_rates_for_tax_class( $tax_class );
        $all_tax_rates = array_merge( $all_tax_rates, $taxes );
    }
    
    echo '<table class="form-table"><tbody>';
    echo '<tr><th>Active?</th>
                <td>';
                $checked = "";
                if(isset($options['is_active'])){
                    if($options['is_active']) {
                        $checked = "checked";
                    }
                }
    echo             '<input class="" id="extra_fee_is_active" name="extra_fee_plugin_options[is_active]"  type="checkbox" value="1" ' . $checked . ' />
                </td>
           </tr>';
    echo '<tr><th>Charger per</th>
                <td>
                    <select class="charge" name="extra_fee_plugin_options[charge_per]" id="extra_fee_charge_per">';
                    $per_selected = "";
                    if(isset($options['charge_per'])){
                        $per_selected = $options['charge_per'];
                    }
                    echo '<option value="per_order" ' . ($per_selected=='per_order' ? "selected" : "" ) .' >Order</option>
                        <option value="per_item" ' . ($per_selected=='per_item' ? "selected" : "" ) .' >Product</option>
                        <option value="per_category" ' . ($per_selected=='per_category' ? "selected" : "" ) .' >Category (parent)</option>
                        <option value="per_tag" ' . ($per_selected=='per_tag' ? "selected" : "" ) .' >Tag (extra-fee)</option>
                    </select>
                        <ul>
                            <li><strong>1)</strong> The option "Order" charges 1 extra fee per order.</li>
                            <li><strong>2)</strong> The option "Product" charges 1 extre fee per product in cart.<br>The quantity per product is also taken under account.</li>
                            <li><strong>3)</strong> The option "Category" uses the parent category only.<br> The fee is added per parent category.</li>
                            <li><strong>4)</strong> To use the Tag option, the tag <strong>"extra-fee"</strong> must be added to the product\'s tags</li>
                        </ul>
                </td>
            </tr>';
    echo '<tr><th>Fee title</th><td><input class="regular-text" id="bg_fee_name" name="extra_fee_plugin_options[name]" type="text" value="' . esc_attr( $options['name'] ) .'" /></td></tr>';
    echo '<tr><th>Amount</th><td><input id="extra_fee_amount" name="extra_fee_plugin_options[amount]"  type="text" value="' . esc_attr( $options['amount'] ) .'" /> €</td></tr>';
    echo '<tr><th>Taxable?</th>
                <td>';
                $checked = "";
                if(isset($options['is_taxable'])){
                    if($options['is_taxable']) {
                        $checked = "checked";
                    }
                }
    echo             '<input class="" id="extra_fee_is_taxable" name="extra_fee_plugin_options[is_taxable]"  type="checkbox" value="1" ' . $checked . ' />
                </td>
           </tr>';
    echo '<tr><th><label class="" for="extra_fee_tax_class">WooCommerce tax classe</label></th>
                <td>
                    <select class="" name="extra_fee_plugin_options[tax_class]" id="extra_fee_tax_class">';
                    foreach ($all_tax_rates as $tax ){
                        $selected = "";
                        if(isset($options['tax_class'])){
                            if($tax->tax_rate_class == $options['tax_class']) {
                                $selected = "selected";
                            }
                        }
                        echo '<option value="' . $tax->tax_rate_class . '" ' . $selected . '>' . $tax->tax_rate_name . '</option>';
                    }
                    echo '</select>';
                echo '</td></tr>';
    echo '</tbody></table>';
}