<?php

require('wp-load.php');

function delete_customer_addresses() {
  global $wpdb;
  $usermeta = $wpdb->prefix . "usermeta";
  // Delete all billing addresses
  $wpdb->query("DELETE FROM $usermeta WHERE meta_key LIKE 'billing_%'");
  // Delete all shipping addresses
  $wpdb->query("DELETE FROM $usermeta WHERE meta_key LIKE 'shipping_%'");
}

// Run the function when the script is loaded
delete_customer_addresses();
