<?php
require('../wp-load.php');

var_dump(get_the_date('Y-m-d', 22987));

function array_to_csv_download($array, $filename = "export.csv", $delimiter=";") {
  header('Content-Type: application/csv');
  header('Content-Disposition: attachment; filename="'.$filename.'";');
  $f = fopen('php://output', 'w');
  foreach ($array as $line) {
    fputcsv($f, $line, $delimiter);
  }
}

function generate_orders_csv() {
  if (!isset($_GET['start'])) {
    echo 'Please Enter Start Date';
    return;
  }
  if (!isset($_GET['end'])) {
    echo 'Please Enter End Date';
    return;
  }
  $start_date_str = $_GET['start'];
  $initial_date = date('Y-m-d', strtotime($start_date_str));
  $final_date = date('Y-m-d', strtotime($_GET['end']));
  $orders = wc_get_orders(
    [
      'limit' => -1,
      'type' => 'shop_order',
      'status' => ['wc-processing', 'wc-completed', 'wc-delivered'],
      'date_created' => $initial_date . '...' . $final_date
    ]
  );
  $csv = [];
  $csv[] = ['Is New Customer?', 'Date', 'Order ID', 'Billing Name', 'Billing Email', 'Billing State', 'Billing ZIP', 'Order Total'];
  foreach ($orders as $order) {
    $billing_email = $order->get_billing_email();
    // echo 'billing email' . $billing_email . '<hr>';
    $is_new_customer = true;
    $customer_user = get_user_by('email', $billing_email);
    if ($customer_user) {
      // Then lookup if that order email address has been used before
      $args = [
        'numberposts' => 1,
        'meta_key' => '_customer_user',
        'meta_value' => $customer_user->ID,
        'post_type' => wc_get_order_types(),
        'post_status' => array_keys(wc_get_order_statuses()),
        'date_query' => array(
          'before' => $start_date_str, // Replace with your desired date
          'inclusive' => true,
        ),        
      ];
      $orders = get_posts($args);
      // If customer has ordered before then
      if ($orders) {
        $is_new_customer = false;
      }
    } else {
      global $wpdb;
      $table = $wpdb->prefix . 'postmeta';
      $sql = "SELECT post_id FROM $table WHERE meta_key = '_billing_email' AND meta_value = '$billing_email' ORDER BY post_id DESC";
      $post_ids = $wpdb->get_results($sql);
      foreach ($post_ids as $post_id) {
        $post_date = get_the_date('Y-m-d', (int)$post_id->post_id);
        if ($post_date < $start_date_str) {
          $is_new_customer = false;
          break;
        }        
      }
    }
    if ($is_new_customer) {
      $csv[] = [
        'Yes New Customer',
        $order->get_date_created(),
        $order->get_id(),
        $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        $order->get_billing_email(),
        $order->get_billing_state(),
        $order->get_billing_postcode(),
        $order->get_total()
      ];
    }
  }
  // var_dump($csv);
  // echo "Output radical-orders_$initial_date-$final_date.csv";
  return array_to_csv_download($csv, "radical-orders_$initial_date-$final_date.csv", ',');
}

generate_orders_csv();
