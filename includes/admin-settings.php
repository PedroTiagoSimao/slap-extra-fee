<?php


// Add admin settings page
add_action('admin_menu','add_bag_fee_menu', 9); 
function add_bag_fee_menu() {
    // Check if SLAP menu exists
    global $menu;
    $menuExist = false;
    foreach($menu as $item) {
        if(strtolower($item[0]) == strtolower('SLAP')) {
            $menuExist = true;
        }
    }
    if(!$menuExist) {
        add_menu_page( 'Definições', 'SLAP', 'administrator', 'slap-settings', 'pages_bag_fee');
        $bagFeeSubMenu = add_submenu_page('slap-settings', 'Definições Taxa Saco', 'Definições Taxa Saco', 'administrator', 'settings-bag-fee', 'displayBagFeeSettings');
        add_action( 'load-' . $bagFeeSubMenu, 'load_admin_js' );
    } else {
        $bagFeeSubMenu = add_submenu_page('slap-settings', 'Definições Taxa Saco', 'Definições Taxa Saco', 'administrator', 'settings-bag-fee', 'displayBagFeeSettings');
        add_action( 'load-' . $bagFeeSubMenu, 'load_admin_js' );
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

function pages_bag_fee() {
  ?>
    <h2>SLAP</h2>
<?php
}

function displayBagFeeSettings(){
    ?>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'bag_fee_plugin_options' );
        do_settings_sections( 'bag_fee_plugin' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
<?php
}

function bag_fee_register_settings() {
    register_setting( 'bag_fee_plugin_options', 'bag_fee_plugin_options', 'bag_fee_plugin_options_validate' );
    add_settings_section( 'bag_settings', 'Taxa de Saco', 'bag_fee_settings', 'bag_fee_plugin' );
}
add_action( 'admin_init', 'bag_fee_register_settings' );

function bag_fee_settings() {
    $options = get_option( 'bag_fee_plugin_options' );

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
    echo '<tr><th>Ativar</th>
                <td>';
                $checked = "";
                if(isset($options['is_active'])){
                    if($options['is_active']) {
                        $checked = "checked";
                    }
                }
    echo             '<input class="" id="bag_fee_is_active" name="bag_fee_plugin_options[is_active]"  type="checkbox" value="1" ' . $checked . ' />
                </td>
           </tr>';
    echo '<tr><th>Cobrar por cada</th>
                <td>
                    <select class="charge" name="bag_fee_plugin_options[charge_per]" id="bag_fee_charge_per">';
                    $per_selected = "";
                    if(isset($options['charge_per'])){
                        $per_selected = $options['charge_per'];
                    }
                    echo '<option value="per_order" ' . ($per_selected=='per_order' ? "selected" : "" ) .' >Encomenda</option>
                        <option value="per_item" ' . ($per_selected=='per_item' ? "selected" : "" ) .' >Produto</option>
                        <option value="per_category" ' . ($per_selected=='per_category' ? "selected" : "" ) .' >Categoria (pai)</option>
                        <option value="per_tag" ' . ($per_selected=='per_tag' ? "selected" : "" ) .' >Etiqueta (taxa)</option>
                    </select>
                        <ul>
                            <li><strong>1)</strong> A opção "Encomenda" adiciona 1 saco por cada encomenda.</li>
                            <li><strong>2)</strong> A opção "Produto" adiciona 1 saco por cada produto no carrinho.<br>A quantidade de cada produto também é considerada.</li>
                            <li><strong>3)</strong> A opção "Categoria" funciona por categoria "pai".<br>Ou seja, só é tido em conta a categoria principal onde os produtos estão inseridos.</li>
                            <li><strong>4)</strong> Para usar a opção "Etiqueta" deve adicionar a etiqueta <strong>taxa</strong> ao produto</li>
                        </ul>
                </td>
            </tr>';
    echo '<tr><th>Titulo da taxa</th><td><input class="regular-text" id="bg_fee_name" name="bag_fee_plugin_options[name]" type="text" value="' . esc_attr( $options['name'] ) .'" /></td></tr>';
    echo '<tr><th>Valor</th><td><input id="bag_fee_amount" name="bag_fee_plugin_options[amount]"  type="text" value="' . esc_attr( $options['amount'] ) .'" /> €</td></tr>';
    echo '<tr><th>Acresce IVA?</th>
                <td>';
                $checked = "";
                if(isset($options['is_taxable'])){
                    if($options['is_taxable']) {
                        $checked = "checked";
                    }
                }
    echo             '<input class="" id="bag_fee_is_taxable" name="bag_fee_plugin_options[is_taxable]"  type="checkbox" value="1" ' . $checked . ' />
                </td>
           </tr>';
    echo '<tr><th><label class="" for="bag_fee_tax_class">Classe de IVA do WooCommerce</label></th>
                <td>
                    <select class="" name="bag_fee_plugin_options[tax_class]" id="bag_fee_tax_class">';
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