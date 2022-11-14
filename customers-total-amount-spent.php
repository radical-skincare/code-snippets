
<?php
require('./wp-load.php');
$before_date = isset($_GET['before_date']) ? $_GET['before_date'] : 'September 1st, 2022';
$after_date = isset($_GET['after_date']) ? $_GET['after_date'] : 'September 1st, 2022';
$orders = get_posts([
  'post_status'=> ['wc-processing', 'wc-completed', 'wc-delivered'],
  'post_type' => ['shop_order', 'shop_subscription'],
  // 'include' => $subscription->get_related_orders(),
  'posts_per_page' => '-1',
  'date_query' => array(
    array(
      'after' => $after_date,
      'before' => $before_date,
      'inclusive' => true,
    ),
  ),
]);
?>
<?php if ($orders) { ?>
  <h3>All Orders</h3>
  <table>
    <table>
      <?php foreach ($orders as $order) { ?>
        <tr>
          <td><?php echo $order->ID; ?></td>
          <td><?php echo $order->post_date; ?></td>
          <td><?php get_post_meta($order->ID, '_billing_email', true); ?></td>
        </tr>
      <?php } ?>
    </table>
  </table>
  <h3>Total Amount Spent</h3>
  <?php
  $customers = [];
  foreach ($orders as $order) {
    $wc_order = new WC_Order( $order->ID );
    $_billing_email = get_post_meta($order->ID, '_billing_email', true);
    $total_amount_spent = $wc_order->get_total();
    if (isset($customers[$_billing_email])) {
      $customers[$_billing_email] += $total_amount_spent;
    } else {
      $customers[$_billing_email] = $total_amount_spent;
    }
  } ?>
  <table>
    <table>
      <?php foreach ($customers as $email => $total_amount_spent) { ?>
        <tr>
          <td><?php echo $email; ?></td>
          <td><?php echo $total_amount_spent; ?></td>
        </tr>
      <?php } ?>
    </table>
  </table>
<?php } else { ?>
  <p>No orders.</p>
<?php } ?>
