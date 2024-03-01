<?php

require('./../wp-load.php');

function radical_array_to_csv_download($array, $filename = "export.csv", $delimiter=";") {
  header('Content-Type: application/csv');
  header('Content-Disposition: attachment; filename="'.$filename.'";');
  $f = fopen('php://output', 'w');
  foreach ($array as $line) {
    fputcsv($f, $line, $delimiter);
  }
}

function radical_generate_orders_csv() {
  if (!isset($_GET['start'])) {
    echo 'Please Enter Start Date';
    return;
  }
  if (!isset($_GET['end'])) {
    echo 'Please Enter End Date';
    return;
  }
  $initial_date = date('Y-m-d', strtotime($_GET['start']));
  $final_date = date('Y-m-d', strtotime($_GET['end']));
  $orders = wc_get_orders(
    [
      'limit' => -1,
      'type' => 'shop_order',
      'status' => ['wc-processing', 'wc-completed', 'wc-delivered'],
      'date_created' => $initial_date . '...' . $final_date,
      'meta_key' => 'v_order_affiliate_remote_order_id',
      'meta_compare' => '='
    ]
  );
  $csv = [];
  $csv[] = ['Date', 'Order ID', 'Billing Name', 'Billing Email', 'Billing State', 'Billing ZIP', 'Order Total', 'Brand Partner ID', 'Brand Partner Volume Type', 'Subscription Relationship'];
  $wc_subscriptions_exists = class_exists('WC_Subscriptions');
  foreach ($orders as $order) {
    $has_hydrating_cleanser = false;
    foreach ($order->get_items() as $item_id => $item) {
      $product = $item->get_product();
      $slug = $product->get_slug();
      if ($slug === 'hydrating-cleanser') {
        $has_hydrating_cleanser = true;
        break;
      }
    }
    if (!$has_hydrating_cleanser) {
      continue;
    }
    $post_id = $product->get_id();
    $sub_relationship = '';
    if ($wc_subscriptions_exists) {
      if (wcs_order_contains_subscription( $order['id'], 'renewal')) {
        $sub_relationship = 'Renewal Order';
      } elseif (wcs_order_contains_subscription( $order['id'], 'resubscribe')) {
        $sub_relationship = 'Resubscribe Order';
      } elseif (wcs_order_contains_subscription( $order['id'], 'parent')) {
        $sub_relationship = 'Parent Order';
      }
    }
    $csv[] = [
      $order->get_date_created(),
      $order->get_id(),
      $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
      $order->get_billing_email(),
      $order->get_billing_state(),
      $order->get_billing_postcode(),
      $order->get_total(),
      get_post_meta($post_id, 'v_order_affiliate_id', true),
      get_post_meta($post_id, 'v_order_affiliate_volume_type', true),
      $sub_relationship
    ];
  }
  return radical_array_to_csv_download($csv, "radical-orders_hydrating-cleanser_$initial_date-$final_date.csv", ',');
}

radical_generate_orders_csv();
