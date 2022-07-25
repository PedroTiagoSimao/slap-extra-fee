<?php
/**
 * @link              https://slap.pt/
 * @since             1.0.1
 * @package           Bag_Fee
 *
 * Plugin Name: SLAP - Extra Fee
 * Plugin URI: https://slap.pt/wp-plugins/slap-extra-fee
 * Description: SLAP - Extra Fee - Allows you to create a fee that will charge the customer based on oder, products, categories or tags
 * Author: SLAP
 * Author URI: https://slap.pt
 * Version: 1.0.5
 */

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'apd_settings_link' );
function apd_settings_link( array $links ) {
    $url = get_admin_url() . "admin.php?page=settings-extra-fee";
    $settings_link = '<a href="' . $url . '">' . __('Settings', 'textdomain') . '</a>';
    $links[] = $settings_link;
    return $links;
}

 require_once (dirname(__FILE__) . '/includes/admin-settings.php');
 require_once (dirname(__FILE__) . '/includes/class-slap-extra-fee.php');

 $slap_bag_fee = new SLAP_Extra_Fee;
 $slap_bag_fee->run();