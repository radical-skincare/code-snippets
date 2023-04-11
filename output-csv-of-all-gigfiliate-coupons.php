<?php

include_once('wp-load.php');
include_once('wp-includes/wp-db.php');

global $wpdb;
$table = $wpdb->prefix . 'postmeta';
$sql = "SELECT post_id FROM $table WHERE meta_key = 'v_discount_affiliate_id'";
$coupon_post_ids = $wpdb->get_results($sql);

foreach ($coupon_post_ids as $coupon_post_id) {
  echo get_the_title($coupon_post_id->post_id) . ',';
}
