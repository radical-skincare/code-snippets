<?php

require('../wp-load.php');

function uniq28323_formatted_billing_address($obj) {
  $address = $obj->get_billing_address_1() . ' ';
  if ($obj->get_billing_address_2()) {
    $address .= $obj->get_billing_address_2() . ' ';    
  }
  $address .= $obj->get_billing_city() . ', ' . $obj->get_billing_state() . ' ' . $obj->get_billing_postcode();
  return $address;
}

/*
 * Convert Number to Word
 */
function uniq28323_get_subscription_interval_period_text($subscription = false) {
  if (!$subscription) {
      return '';
  }
  $interval = (int)$subscription->get_billing_interval();
  $period = $subscription->get_billing_period();
  if ($interval === 1 && $period === 'month') {
      return 'Every Month';
  } else if ($interval === 2 && $period === 'month') {
      return 'Every 2nd Month';
  }
  return $interval . ' ' . $period;
}

function uniq28323_array_to_csv_download($array, $filename = "export.csv", $delimiter=";") {
  header('Content-Type: application/csv');
  header('Content-Disposition: attachment; filename="'.$filename.'";');
  $f = fopen('php://output', 'w');
  foreach ($array as $line) {
    fputcsv($f, $line, $delimiter);
  }
}

function uniq28323_generate_subscriptions_csv() {
  if (!isset($_GET['limit'])) {
    echo 'Please Enter Limit';
    return;
  }
  if (!isset($_GET['offset'])) {
    echo 'Please Enter Offset';
    return;
  }
  if (!isset($_GET['status'])) {
    echo 'Please Enter Status';
    return;
  }
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
  $args = [
    'limit' => (int)$_GET['limit'],
    // 'limit' => -1,
    'offset' => (int)$_GET['offset'],
    'type' => 'shop_subscription',
    'status' => [$_GET['status']],
    // 'meta_key' => '_customer_user',
    // 'meta_value' => $customer_user_id,
    // 'date_created' => $initial_date . '...' . $final_date
  ];
  $subscriptions = wc_get_orders($args);
  $csv = [];
  $csv[] = [
    // 'Next Payment Date',
    'Subscription ID', 'Status', 'Billing Name', 'Billing Email', 'Billing Full Address', 'Ordered By', 'Most Recent Order Affiliate Volume Type', 'Recurring Total', 'Interval'];
  // $woocommerce_currency_symbol = get_woocommerce_currency_symbol();
  foreach ($subscriptions as $subscription) {
    $subscription_id = $subscription->get_ID();
    $sub_status = $subscription->get_status();
    $total = $subscription->get_total();
    $ordered_by = get_post_meta($subscription_id, 'gigfilliatewp_ordered_by', true);
    $related_orders = $subscription->get_related_orders();
    $order_affiliate_volume_type = '';
    if ($related_orders) {
      foreach ($related_orders as $related_order_id) {
        $order_affiliate_volume_type = get_post_meta($related_order_id, 'v_order_affiliate_volume_type', true);
        break;
      }      
    }    
    $csv[] = [
      // $subscription->get_date_to_display( 'next_payment' ),
      $subscription_id,
      $sub_status,
      $subscription->get_billing_first_name() . ' '. $subscription->get_billing_last_name(),
      $subscription->get_billing_email(),
      uniq28323_formatted_billing_address($subscription),
      $ordered_by,
      $order_affiliate_volume_type,
      '$' . $total,
      uniq28323_get_subscription_interval_period_text($subscription),
    ];
  }
  return uniq28323_array_to_csv_download($csv, "radical-subscriptions_active.csv", ',');
}

uniq28323_generate_subscriptions_csv();
