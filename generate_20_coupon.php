<?php
// Generate 20% coupon for active BP's
require('wp-load.php');
$number = $_GET['number'];
$offset = $_GET['offset'];
if(!$number){
  echo 'Please enter number';
  return;
}
if(!$offset){
  $offset = 0;
}
$user_query = new WP_User_Query([
  'meta_key'     => 'v_affiliate_status',
  'meta_value'   => 'active',
  'meta_compare' => '==',
  'number' => $number,
  'offset' => $offset
]);
$users = $user_query->get_results();
if($_GET['dry_run']) {
  echo count($users);
  return;
}
$coupon_amount = 20;
$discount_type = 'percent_andor_recurring_percent';
foreach ($users as $user) {
  $coupon_code = get_user_meta($user->ID, 'nickname', true) . $coupon_amount; 
  $coupon = array(
    'post_title' => $coupon_code,
    'post_content' => '',
    'post_status' => 'publish',
    'post_author' => 1,
    'post_type' => 'shop_coupon'
  );
  $new_coupon_id = wp_insert_post($coupon);
  // I copied these coupon settings from approval process
  update_post_meta($new_coupon_id, 'discount_type', $discount_type);
  update_post_meta($new_coupon_id, 'coupon_amount', $coupon_amount);
  update_post_meta($new_coupon_id, 'individual_use', 'yes');
  update_post_meta($new_coupon_id, 'usage_count', 0);
  update_post_meta($new_coupon_id, 'usage_limit', 0);
  update_post_meta($new_coupon_id, 'free_shipping', 'no');
  update_post_meta($new_coupon_id, 'v_discount_affiliate_id', get_user_meta($user->ID, 'v_affiliate_id', true));
  update_user_meta($user->ID, 'primary_affiliate_coupon_code', $coupon_code);
  // Added the below line so we can keep track of what coupon is generated by script.
  update_post_meta($new_coupon_id, 'generated_form_script_1_10', true);
  echo $user->ID. '<br>';
}
echo 'Done';
