<?php

require('../wp-load.php');


function update_subscription_order_by() {
  // if (!isset($_GET['limit'])) {
  //   echo 'Please Enter Limit';
  //   return;
  // }
  // if (!isset($_GET['offset'])) {
  //   echo 'Please Enter Offset';
  //   return;
  // }
  // if (!isset($_GET['status'])) {
  //   echo 'Please Enter Status';
  //   return;
  // }
  $status = $_GET['status']; // 'wc-active'
  // if (!isset($_GET['customer_user_id'])) {
  //   echo 'Please Enter Customer User Id';
  //   return;
  // }
  // $customer_user_id = (int)$_GET['customer_user_id'];
  // if (!isset($_GET['start'])) {
  //   echo 'Please Enter Start Date';
  //   return;
  // }
  // if (!isset($_GET['end'])) {
  //   echo 'Please Enter End Date';
  //   return;
  // }
  // $initial_date = date('Y-m-d', strtotime($_GET['start']));
  // $final_date = date('Y-m-d', strtotime($_GET['end']));

  $subscription_parent_ids = wcs_get_subscription_orders('ids', 'parent');
  // print_r($subscriptions_ids);
  echo 'Found '.count($subscription_parent_ids). ' Parent Orders <br>';

  $args = [
    'limit' => -1,
    'post__in'=> $subscription_parent_ids,
    'meta_key'     => 'gigfilliatewp_ordered_by',
    'meta_compare' => 'EXISTS',
  ];
  $filtered_subscription_parents = wc_get_orders($args);
  echo 'Found '.count($filtered_subscription_parents). ' Filtered Parent Orders <br>';

  foreach ($filtered_subscription_parents as $filtered_subscription_parent) {
    $parent_id = $filtered_subscription_parent->get_ID();
    $subscriptions = wcs_get_subscriptions_for_order( $parent_id, array( 'order_type' => 'any' ) );
    foreach ($subscriptions as $subscription) {
      $sub_gigfilliatewp_ordered_by = get_post_meta($subscription->get_ID(), 'gigfilliatewp_ordered_by', true);

      if (!$sub_gigfilliatewp_ordered_by) {
        $ordered_by = get_post_meta($subscription->get_ID(), 'ordered_by', true);
        if (!$ordered_by) {
          echo '<br>';
          echo $subscription->get_ID(). ' Dont have gigfilliatewp_ordered_by key';
          echo '<br>';
        }
      }
    }
  }
}

update_subscription_order_by();