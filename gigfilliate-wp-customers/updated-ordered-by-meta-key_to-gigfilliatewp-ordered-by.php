<?php

require('../wp-load.php');

function updateGigfilliateWPCustomersOrderedByMetaKey() {
  $posts_per_page = isset($_GET['posts_per_page']) ? $_GET['posts_per_page'] : 10;
  $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;  
  $orders = get_posts([
    'post_type' => 'shop_order',
    'posts_per_page' => $posts_per_page,
    'offset' => $offset,
    'post_status' => 'any',
    'meta_key' => 'ordered_by',
    'meta_value' => '',
    'meta_compare' => 'EXISTS'
  ]);
  echo 'Total orders count: ' . count($orders) . '<br/><ul>';
    for ($i = 0; $i < count($orders); $i++) { 
      $ordered_by = get_post_meta($orders[$i]->ID, 'ordered_by', true) . '<br>';
      $result = update_post_meta($orders[$i]->ID, 'gigfilliatewp_ordered_by', $ordered_by);
      echo '<li>';
        if ($result) {
          delete_post_meta($orders[$i]->ID, 'ordered_by');
          echo 'Updated: Order #ID <a href="https://radicalskincare.com/wp-admin/post.php?post=' . $orders[$i]->ID . '&action=edit" target="_blank">' . $orders[$i]->ID . '</a>';
        } else {
          echo 'Failed: Order #ID <a href="https://radicalskincare.com/wp-admin/post.php?post=' . $orders[$i]->ID . '&action=edit" target="_blank">' . $orders[$i]->ID . '</a>';
        }
      echo '</li>';
    }
  echo '</ul>';
}

updateGigfilliateWPCustomersOrderedByMetaKey();
