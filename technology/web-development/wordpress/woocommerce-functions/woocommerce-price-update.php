<?php

// WooCommerce - Apply sales price to multiple products
// Last update: 2025-02-02


function update_product_prices()
{
    // Define the category slug
    $category_slugs = array('specialty-coffees-de');

    // Define the price mapping (current price => new price) as string keys
    $price_updates = array(
        '7.00' => 7.90,
        '12.40' => 13.90,
        '24.80' => 27.80,
        '5.60' => 7.10,
        '11.15' => 12.50,
        '22.30' => 25.00,
        '11.00' => 15.00
    );

    // Convert category slugs to IDs
    $category_ids = array();
    foreach ($category_slugs as $slug) {
        $term = get_term_by('slug', $slug, 'product_cat');
        if ($term) {
            $category_ids[] = $term->term_id;
        }
    }

    if (empty($category_ids)) {
        echo 'No valid categories found.';
        return;
    }

    // Get all products in the category
    $products = get_posts(array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $category_ids,
                'operator' => 'IN'
            )
        ),
		'post_status' => array('publish', 'private')
    ));

    foreach ($products as $product) {
        $product_id = $product->ID;
        $product_obj = wc_get_product($product_id);

        if ($product_obj->is_type('variable')) {
            $variations = $product_obj->get_children();
            foreach ($variations as $variation_id) {
                update_product_price($variation_id, $price_updates);
            }
        } else {
            update_product_price($product_id, $price_updates);
        }
    }
}

function update_product_price($product_id, $price_updates)
{
    $regular_price = get_post_meta($product_id, '_regular_price', true);
    $regular_price = floatval($regular_price); // Convert regular price to float

    // Convert the regular price to cents (multiply by 100 and round)
    $regular_price_cents = round($regular_price * 100);

    // Iterate through price updates and compare after converting to cents
    foreach ($price_updates as $old_price => $new_price) {
        // Convert the old price to cents
        $old_price_cents = round($old_price * 100);
        $new_price_cents = round($new_price * 100);

        // Compare the prices in cents
        if ($regular_price_cents === $old_price_cents) {
            // Update the product with the new price in cents
            update_post_meta($product_id, '_regular_price', $new_price_cents / 100);
            update_post_meta($product_id, '_price', $new_price_cents / 100); // Ensure price update

            $product = get_post($product_id);
            echo 'Product price updated: ' . $product->ID . ' - ' . $product->post_title . ' (' . $product->post_name . ')<br>';
            echo 'Old Regular Price: ' . $regular_price . '<br>';
            echo 'New Regular Price: ' . ($new_price_cents / 100) . '<br><br>';
            return; // Exit after the first match
        }
    }

    echo 'No matching price for product ID ' . $product_id . ' (Current price: ' . $regular_price . ')<br>';
}

// Execute the function
update_product_prices();
