<?php
require('../wp-load.php');
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
    'date_created' => $initial_date . '...' . $final_date
  ]
);

$csv = [];
$csv[] = ['Date', 'Order ID', 'Billing Name', 'Billing Email', 'Billing State', 'Billing ZIP', 'Order Total'];
foreach ($orders as $order) {
  $csv[] = [
    $order->get_date_created(),
    $order->get_id(),
    $order->get_billing_first_name(). ' '. $order->get_billing_last_name(),
    $order->get_billing_email(),
    $order->get_billing_state(),
    $order->get_billing_postcode(),
    $order->get_total()
  ];
}
 
$fp = fopen('orders.csv', 'w');
foreach ($csv as $fields) {
  fputcsv($fp, $fields);
}
fclose($fp);
echo 'Generated';