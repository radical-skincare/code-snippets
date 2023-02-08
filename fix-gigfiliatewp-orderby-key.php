<?php

include_once('wp-load.php');
include_once("wp-includes/wp-db.php");
$sql = "SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = 'gigfiliatewp_ordered_by' AND `meta_value` LIKE '%<br>%'";
$results = $wpdb->get_results($sql);
foreach ($results as $key => $result) {
  $gigfiliatewp_ordered_by = get_post_meta($result->post_id, 'gigfiliatewp_ordered_by', true);
  $gigfiliatewp_ordered_by = strip_tags($gigfiliatewp_ordered_by);
  update_post_meta($result->post_id, 'gigfiliatewp_ordered_by', $gigfiliatewp_ordered_by);
  echo '<p>Updated ' . $result->post_id .' <br/>';
}
