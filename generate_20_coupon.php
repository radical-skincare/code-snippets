<?php
// Generate 20% coupon for active BP's
require('wp-load.php');

function radical_generate_new_bp_customer20_coupon() {
    $site_url = get_site_url();
    $number = isset($_GET['number']) ? $_GET['number'] : 10;
    $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
    if (!$number) {
      echo 'Please enter number!';
      return;
    }
    if (!$offset) {
      $offset = 0;
    }
    $user_query = new WP_User_Query([
      // 'include' => [4221], // testing
      'meta_key'     => 'v_affiliate_status',
      'meta_value'   => 'active',
      'meta_compare' => '==',
      'number' => $number,
      'offset' => $offset
    ]);
    $users = $user_query->get_results();
    if (isset($_GET['dry_run']) && $_GET['dry_run']) {
      echo 'Dry run<br/>Number of users:' . count($users);
      return;
    }
    $coupon_amount = 20;
    $discount_type = 'percent_andor_recurring_percent';
    if ($users) {
      echo '<p>Coupons created:</p><ul>';
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
          $affiliate_id = get_user_meta($user->ID, 'v_affiliate_id', true);
          update_post_meta($new_coupon_id, 'v_discount_affiliate_id', $affiliate_id);
          update_user_meta($user->ID, 'primary_affiliate_coupon_code', $coupon_code);
          // Added the below line so we can keep track of what coupon is generated by script.
          update_post_meta($new_coupon_id, 'generated_form_script_1_10', true);
          echo '
            <li>
              <a href="' . $site_url . '/wp-admin/user-edit.php?user_id=' . $user->ID . '&action=edit" target="_blank">User ID: ' . $user->ID . '</a>
              - <a href="' . $site_url . '/wp-admin/admin.php?page=gigfilliate&tab=affiliates&affiliate_id=' . $affiliate_id . '&action=edit&action=edit" target="_blank">Affiliate ID: ' . $affiliate_id . '</a>
              - <a href="' . $site_url . '/wp-admin/post.php?post=' . $new_coupon_id . '&action=edit" target="_blank">New Coupon: ' . $coupon_code . '</a>
            </li>';
        }
      echo '</ul>';
    }
    echo 'Done';
}
radical_generate_new_bp_customer20_coupon();
