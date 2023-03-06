<?php
require('../wp-load.php');

function array_to_csv_download($array, $filename = "export.csv", $delimiter = ";")
{
  header('Content-Type: application/csv');
  header('Content-Disposition: attachment; filename="' . $filename . '";');
  $f = fopen('php://output', 'w');
  foreach ($array as $line) {
    fputcsv($f, $line, $delimiter);
  }
}

function generate_orders_csv()
{
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
  $past_10_years_initial_date = date('Y-m-d', strtotime('-10 years', strtotime($initial_date)));
  $final_date = date('Y-m-d', strtotime($_GET['end']));
  $order_args = [
    'limit' => -1,
    'type' => 'shop_order',
    'status' => ['wc-processing', 'wc-completed', 'wc-delivered'],
    'date_created' => $initial_date . '...' . $final_date
  ];
  if (isset($_GET['exclude_affiliate']) && $_GET['exclude_affiliate'] == 'true') {
    $order_args['meta_key'] = 'v_order_affiliate_volume_type';
    $order_args['meta_compare'] = 'NOT EXISTS';
  }
  $orders = wc_get_orders($order_args);

  $csv = [];
  $csv[] = ['Is New Customer?', 'Date', 'Order ID', 'Billing Name', 'Billing Email', 'Billing State', 'Billing ZIP', 'Order Total'];
  foreach ($orders as $order) {
    $billing_email = $order->get_billing_email();
    $args = array(
      'post_type' => 'shop_order',
      'posts_per_page' => 1,
      'post_status' => array_keys(wc_get_order_statuses()),
      'orderby' => 'date',
      'order' => 'DESC',
      'meta_query' => [
        [
          'key' => '_billing_email',
          'value' => $billing_email,
          'compare' => '=='
        ]
      ],
      'date_query' => array(
        array(
          'after'     => $past_10_years_initial_date,
          'before'    => $start_date_str,
          'inclusive' => true,
        ),
      ),
    );
    $query = new WP_Query($args);
    if ($query->found_posts == 0) {
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
