<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

require('../wp-load.php');

function generate_coupon($coupon_amount, $email) {
  $coupon_code = rand() . $coupon_amount; 
  $coupon = array(
    'post_title' => $coupon_code,
    'post_content' => '',
    'post_status' => 'publish',
    'post_author' => 1,
    'post_type' => 'shop_coupon'
  );
  $new_coupon_id = wp_insert_post($coupon);
  update_post_meta($new_coupon_id, 'discount_type', 'fixed_cart');
  update_post_meta($new_coupon_id, 'coupon_amount', $coupon_amount);
  update_post_meta($new_coupon_id, 'individual_use', 'yes');
  update_post_meta($new_coupon_id, 'usage_count', 0);
  update_post_meta($new_coupon_id, 'usage_limit', 1);
  update_post_meta($new_coupon_id, 'free_shipping', 'no');
  update_post_meta($new_coupon_id, 'customer_email', [$email]);
  update_post_meta($new_coupon_id, 'date_expires', '1672473600'); //12/31/22.
  update_post_meta($new_coupon_id, 'generated_form_script_coupon_generator', true);
  echo $coupon_code;
}

function send_email($to) {
  //Not Yet Tested
  $subject = 'Your Subject';
  ob_start();
  include('email-template.php');
  $body = ob_get_contents();
  ob_end_clean();
  $headers = array('Content-Type: text/html; charset=UTF-8','From: Test <test@test.com>');
  return wp_mail( $to, $subject, $body, $headers );
}

$row = 0;
if (($handle = fopen("customers_list.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $row++;
    $email = $data[0];
    $amount = $data[1];
    if ($amount > 95 && $amount < 249) {
      echo '<b>$15</b> off coupon '.generate_coupon(15, $email);
    } else if ($amount > 250) {
      echo '<b>$40</b> off coupon '.generate_coupon(40, $email);
    }
    print_r('      <>      '.$email . '   <b>'. $amount. '</b><br>');
  }
  fclose($handle);
}