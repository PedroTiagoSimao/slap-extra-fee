<?php
/**
 * @link              https://slap.pt/
 * @since             1.0.1
 * @package           Bag_Fee
 *
 * Plugin Name: SLAP - Taxa para saco
 * Plugin URI: https://slap.pt/
 * Description: SLAP - Taxa para saco
 * Author: SLAP
 * Author URI: https://slap.pt/
 * Version: 1.0.3
 */

 require_once (dirname(__FILE__) . '/includes/admin-settings.php');
 require_once (dirname(__FILE__) . '/includes/class-slap-bag-fee.php');

 $slap_bag_fee = new SLAP_Extra_Fee;
 $slap_bag_fee->run();