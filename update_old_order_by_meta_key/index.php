<?php
require('./../wp-load.php');

function update_order_by_meta_key() {
  global $wpdb;
  $postmeta = $wpdb->prefix . "postmeta";
  $wpdb->query("UPDATE $postmeta WHERE meta_key = 'gigfilliatewp_ordered_by' SET meta_key = 'gig_ordered_by'");
  $wpdb->query("UPDATE $postmeta WHERE meta_key = 'ordered_by' SET meta_key = 'gig_ordered_by'");
}

// Run the function when the script is loaded
update_order_by_meta_key();