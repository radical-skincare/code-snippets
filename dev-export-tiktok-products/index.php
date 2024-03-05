<?php
require('../../wp-load.php');

function radical_array_to_csv_download($array, $filename = "export.csv", $delimiter=";") {
  header('Content-Type: application/csv');
  header('Content-Disposition: attachment; filename="'.$filename.'";');
  $f = fopen('php://output', 'w');
  foreach ($array as $line) {
    fputcsv($f, $line, $delimiter);
  }
}

function radical_export_tittok_products() {
  // $products = get_posts(
  //   [
  //     'limit' => -1,
  //     'type' => 'product',
  //   ]
  // );
  $csv = [];
  $headers = array(
    'Category', 'Brand', 'Product Name', 'Product Description', 'Package Weight(lb)',
    'Package Length(inch)', 'Package Width(inch)', 'Package Height(inch)', 'Delivery options',
    'Identifier Code Type', 'Identifier Code', 'Variation 1', 'Variation 2', 'Variant Image',
    'Retail Price (Local Currency)', 'Quantity in Radical Skincare', 'Seller SKU', 'Main Product Image',
    'Product Image 2', 'Product Image 3', 'Product Image 4', 'Product Image 5', 'Product Image 6',
    'Product Image 7', 'Product Image 8', 'Product Image 9', 'Size Chart', 'Skin Type', 'Country Of Origin',
    'Shelf Life', 'Alcohol Or Aerosol', 'Allergen Information', 'Net Weight', 'Volume', 'Ingredients',
    'Quantity Per Pack', 'Skincare Benefits', 'Body Care Benefits', 'Cautions/Warnings', 'Manufacturer',
    'Drug Labeling', 'US Certificate of Conformity', 'Declaration of Conformity', 'Cosmetics Packaging Labeling',
    'Product Status'
  );
  $csv[] = $headers;
  $products = wc_get_products(array('status' => 'publish'));
  foreach ($products as $product) {
    // Get product categories
    $categories = array();
    $category_ids = $product->get_category_ids();
    foreach ($category_ids as $category_id) {
      $category = get_term($category_id, 'product_cat');
      if ($category && !is_wp_error($category)) {
        $categories[] = $category->name;
      }
    }
    // variations - $product->get_children()
    var_dump(get_the_post_thumbnail_url($product->get_image_id()));
    wp_die();
    $product_gallery_iamges = $product->get_gallery_image_ids();
    $csv[] = [
      implode(', ', $categories), // Category
      'Radical Skincare', // Brand
      $product->get_name(), // Product Name
      $product->get_short_description(), // Product Description
      $product->get_weight(), // Package Weight(lb)
      $product->get_length(), // Package Length(inch)
      $product->get_width(), // Package Width(inch)
      $product->get_height(), // Package Height(inch)
      '', // Delivery options
      'SKU', // Identifier Code Type
      $product->get_sku(), // Identifier Code
      '', // TODO: $product->get_meta('variation_1'), // Variation 1
      '', // TODO: $product->get_meta('variation_2'), // Variation 2
      $product->get_meta('_thumbnail_id'), // Variant Image
      $product->get_price(), // Retail Price (Local Currency)
      $product->get_stock_quantity(), // Quantity in Radical Skincare
      $product->get_sku(), // Seller SKU
      get_the_post_thumbnail_url($product->get_image_id()), // Main Product Image
      // Additional product images (adjust as needed)
      $product_gallery_iamges && isset($product_gallery_iamges[0]) ? $product_gallery_iamges[0] : '', // Product Image 2
      $product_gallery_iamges && isset($product_gallery_iamges[1]) ? $product_gallery_iamges[1] : '', // Product Image 3
      $product_gallery_iamges && isset($product_gallery_iamges[2]) ? $product_gallery_iamges[2] : '', // Product Image 4
      $product_gallery_iamges && isset($product_gallery_iamges[3]) ? $product_gallery_iamges[3] : '', // Product Image 5
      $product_gallery_iamges && isset($product_gallery_iamges[4]) ? $product_gallery_iamges[4] : '', // Product Image 6
      $product_gallery_iamges && isset($product_gallery_iamges[5]) ? $product_gallery_iamges[5] : '', // Product Image 7
      $product_gallery_iamges && isset($product_gallery_iamges[6]) ? $product_gallery_iamges[6] : '', // Product Image 8
      $product_gallery_iamges && isset($product_gallery_iamges[7]) ? $product_gallery_iamges[7] : '', // Product Image 9
      // TODO...
      $product->get_meta('size_chart'), // Size Chart
      $product->get_meta('skin_type'), // Skin Type
      'USA', // Country Of Origin
      $product->get_meta('shelf_life'), // Shelf Life
      $product->get_meta('alcohol_or_aerosol'), // Alcohol Or Aerosol
      $product->get_meta('allergen_information'), // Allergen Information
      $product->get_meta('net_weight'), // Net Weight
      $product->get_meta('volume'), // Volume
      $product->get_meta('ingredients'), // Ingredients
      $product->get_meta('quantity_per_pack'), // Quantity Per Pack
      $product->get_meta('skincare_benefits'), // Skincare Benefits
      $product->get_meta('body_care_benefits'), // Body Care Benefits
      $product->get_meta('cautions_warnings'), // Cautions/Warnings
      $product->get_meta('manufacturer'), // Manufacturer
      $product->get_meta('drug_labeling'), // Drug Labeling
      $product->get_meta('us_certificate_of_conformity'), // US Certificate of Conformity
      $product->get_meta('declaration_of_conformity'), // Declaration of Conformity
      $product->get_meta('cosmetics_packaging_labeling'), // Cosmetics Packaging Labeling
      $product->get_status(), // Product Status
    ];
  }
  return radical_array_to_csv_download($csv, "radical-tiktok-products.csv", ',');
}

radical_export_tittok_products();
