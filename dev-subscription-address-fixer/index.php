<style>
  .subscription_list th, .subscription_list td{border:solid 1px #666; padding:2px 5px;}
  .subscription_list th{font-weight:bold}
  /* .subscription_list td{text-align:center} */
  .subscription_list h4,
  .subscription_list p {
    margin: 4px 0 2px;
  }
</style>
<?php
require('../wp-load.php');
if (isset($_POST['updateAddress']) && isset($_POST['sub_id'])) {
  $update_order = wc_get_order($_POST['sub_id']);
  $update_order->set_billing_first_name($_POST['billing_first_name']);
  $update_order->set_billing_last_name($_POST['billing_last_name']);
  $update_order->set_billing_address_1($_POST['billing_address_1']);
  $update_order->set_billing_address_2($_POST['billing_address_2']);
  $update_order->set_billing_city($_POST['billing_city']);
  $update_order->set_billing_state($_POST['billing_state']);
  $update_order->set_billing_postcode($_POST['billing_postcode']);
  $update_order->set_billing_email($_POST['billing_email']);
  $update_order->set_shipping_first_name($_POST['shipping_first_name']);
  $update_order->set_shipping_last_name($_POST['shipping_last_name']);
  $update_order->set_shipping_address_1($_POST['shipping_address_1']);
  $update_order->set_shipping_address_2($_POST['shipping_address_2']);
  $update_order->set_shipping_city($_POST['shipping_city']);
  $update_order->set_shipping_state($_POST['shipping_state']);
  $update_order->set_shipping_postcode($_POST['shipping_postcode']);
  if ($update_order->save()) {
    update_post_meta($_POST['sub_id'], 'gigfilliatewp_ordered_by', $_POST['order_by']);
    echo 'Sub #'.$_POST['sub_id']. 'Updated <br>';
  } else {
    echo 'Error!! Unable To Update <br>';
  }
}
function rad_formatted_billing_address($order)
{
  return
    $order->billing_address_1 . ', ' . 
    $order->billing_address_2 . ' ' .
    $order->billing_city      . ', ' .
    $order->billing_state     . ' ' .
    $order->billing_postcode;
}

function rad_formatted_shipping_address($order)
{
  return
    $order->shipping_address_1 . ', ' . 
    $order->shipping_address_2 . ' ' .
    $order->shipping_city      . ', ' .
    $order->shipping_state     . ' ' .
    $order->shipping_postcode;
}

function rad_formatted_billing_name($order)
{
  return
    $order->billing_first_name . ' ' . 
    $order->billing_last_name;
}

function rad_formatted_shipping_name($order)
{
  return
    $order->shipping_first_name . ' ' . 
    $order->shipping_last_name;
}

function rad_active_subscription_list($user_id = null) {
  $site_url = get_site_url();
  // Get all customer orders
  $subscriptions = get_posts(array(
    'numberposts' => -1,
    'post_type'   => 'shop_subscription', // Subscription post type
    'post_status' => ['wc-active', 'wc-cancelled', 'wc-on-hold'], // Subscription statuses
    'orderby' => 'post_date', // ordered by date
    'order' => 'ASC',
    'meta_query' => array(
      // array(
      //   'key' => 'gigfiliatewp_ordered_by',
      //   'compare' => 'EXISTS',
      // ),
      // 'relation' => 'AND',
      array(
        'key' => '_customer_user',
        'compare' => '=',
        'value' => $user_id
      ),
    ),
    )
  );

  // Displaying list in an html table
  echo "<table class='shop_table subscription_list'>
      <tr>
          <th>Subscription ID</th>
          <th>Parent ID</th>
          <th>Subscription</th>
          <th>Parent</th>
          <th>Subscription Status</th>
          <th>Action</th>
      </tr>
          ";
  // Going through each current customer orders
  foreach ( $subscriptions as $subscription ) {
    $subscription_id = $subscription->ID; // subscription ID
    $subscription = new WC_Subscription( $subscription_id );
    $status = wcs_get_subscription_status_name( $subscription->get_status() );
    $parent_id = $subscription->get_parent_id();
    $parent_order = wc_get_order($parent_id);

    $sub_billing_name = rad_formatted_billing_name($subscription);
    $sub_shipping_name = rad_formatted_shipping_name($subscription);
    $sub_billing_address = rad_formatted_billing_address($subscription);
    $sub_shipping_address = rad_formatted_shipping_address($subscription);
    $sub_ordered_by = get_post_meta($subscription_id, 'gigfiliatewp_ordered_by', true);

    $parent_billing_name = rad_formatted_billing_name($parent_order);
    $parent_shipping_name = rad_formatted_shipping_name($parent_order);
    $parent_billing_address = rad_formatted_billing_address($parent_order);
    $parent_shipping_address = rad_formatted_shipping_address($parent_order);
    $parent_ordered_by = get_post_meta($parent_id, 'gigfiliatewp_ordered_by', true);
    $_customer_user = get_post_meta($subscription_id, '_customer_user', true);

    if (isset($_GET['filtered'])) {
      $customer = new WC_Customer($user_id);
      if (($customer->get_billing_first_name() .' '.$customer->get_billing_last_name())  == rad_formatted_billing_name($parent_order)) {
        continue;
      }
    }
    echo "
      </tr>
        <td><a href='$site_url/wp-admin/post.php?post=$subscription_id&action=edit' target='_blank'>$subscription_id</a></td>
        <td><a href='$site_url/wp-admin/post.php?post=$parent_id&action=edit' target='_blank'>$parent_id</a></td>
        <td>
        <h4>Billing:</h4>
        <p><b>Name: </b>$sub_billing_name <br><b>Address: </b>$sub_billing_address</p>
        <hr>
        <h4>Shipping:</h4>
        <p><b>Name: </b>$sub_shipping_name <br><b>Address: </b>$sub_shipping_address</p>
        <hr>
        <p><b>Orderd By:</b> $sub_ordered_by</p>
        </td>
        <td>
        <h4>Billing:</h4>
        <p><b>Name: </b>$parent_billing_name <br><b>Address: </b>$parent_billing_address</p>
        <hr>
        <h4>Shipping:</h4>
        <p><b>Name: </b>$parent_shipping_name <br><b>Address: </b>$parent_shipping_address</p>
        <hr>
        <p><b>Orderd By:</b> $parent_ordered_by</p>
        </td>
        <td>
        $status
        </td>
        <td>
        <form method='post'>
          <input type='hidden' name='sub_id' value='".$subscription_id."'/>
          <input type='hidden' name='billing_address_1' value='".$parent_order->billing_address_1."'/>
          <input type='hidden' name='billing_address_2' value='".$parent_order->billing_address_2."'/>
          <input type='hidden' name='billing_city' value='".$parent_order->billing_city."'/>
          <input type='hidden' name='billing_state' value='".$parent_order->billing_state."'/>
          <input type='hidden' name='billing_postcode' value='".$parent_order->billing_postcode."'/>
          <input type='hidden' name='billing_email' value='".$parent_order->billing_email."'/>
          <input type='hidden' name='billing_first_name' value='".$parent_order->billing_first_name."'/>
          <input type='hidden' name='billing_last_name' value='".$parent_order->billing_last_name."'/>
          <input type='hidden' name='shipping_first_name' value='".$parent_order->shipping_first_name."'/>
          <input type='hidden' name='shipping_last_name' value='".$parent_order->shipping_last_name."'/>
          <input type='hidden' name='shipping_address_1' value='".$parent_order->shipping_address_1."'/>
          <input type='hidden' name='shipping_address_2' value='".$parent_order->shipping_address_2."'/>
          <input type='hidden' name='shipping_city' value='".$parent_order->shipping_city."'/>
          <input type='hidden' name='shipping_state' value='".$parent_order->shipping_state."'/>
          <input type='hidden' name='shipping_postcode' value='".$parent_order->shipping_postcode."'/>
          <input type='hidden' name='order_by' value='".$parent_ordered_by."'/>
          <input type='submit' name='updateAddress' value='Update Address'/>
        </form>";
        if (!$parent_ordered_by) {
          echo "<form method='post'>
          <input type='hidden' name='sub_id' value='".$subscription_id."'/>
          <input type='hidden' name='billing_address_1' value='".$parent_order->billing_address_1."'/>
          <input type='hidden' name='billing_address_2' value='".$parent_order->billing_address_2."'/>
          <input type='hidden' name='billing_city' value='".$parent_order->billing_city."'/>
          <input type='hidden' name='billing_state' value='".$parent_order->billing_state."'/>
          <input type='hidden' name='billing_postcode' value='".$parent_order->billing_postcode."'/>
          <input type='hidden' name='billing_email' value='".$parent_order->billing_email."'/>
          <input type='hidden' name='billing_first_name' value='".$parent_order->billing_first_name."'/>
          <input type='hidden' name='billing_last_name' value='".$parent_order->billing_last_name."'/>
          <input type='hidden' name='shipping_first_name' value='".$parent_order->shipping_first_name."'/>
          <input type='hidden' name='shipping_last_name' value='".$parent_order->shipping_last_name."'/>
          <input type='hidden' name='shipping_address_1' value='".$parent_order->shipping_address_1."'/>
          <input type='hidden' name='shipping_address_2' value='".$parent_order->shipping_address_2."'/>
          <input type='hidden' name='shipping_city' value='".$parent_order->shipping_city."'/>
          <input type='hidden' name='shipping_state' value='".$parent_order->shipping_state."'/>
          <input type='hidden' name='shipping_postcode' value='".$parent_order->shipping_postcode."'/>
          <input type='hidden' name='order_by' value='".get_userdata($_customer_user)->user_email."'/>
          <input type='submit' name='updateAddress' value='Update Address And Order By'/>
        </form>";
        }
        echo "</td>
      </tr>";
  }
  echo '</table>';
}

if ($user_id = $_GET['user_id']) {
  rad_active_subscription_list($user_id);
}
