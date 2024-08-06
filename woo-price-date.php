#20% na sve modele

add_action('init', 'apply_20_percent_discount_to_all_products');

function apply_20_percent_discount_to_all_products() {
    // Proveri da li je ovo admin akcija i da li je funkcija već izvršena
    if (is_admin() && !defined('DISABLE_FUNCTION')) {
        // Postavi flag da ne bi više puta pokrenuo ovu funkciju
        define('DISABLE_FUNCTION', true);

        // Argumenti za WP_Query
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1, // Učitaj sve proizvode
            'post_status'    => 'publish',
        );

        // Kreiraj novu WP_Query instancu
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $post;
                
                $product_id = $post->ID;
                $product = wc_get_product($product_id);
                
                // Ako proizvod nije varijabilan, koristi ovo
                if ($product->is_type('simple')) {
                    $regular_price = $product->get_regular_price();
                    $sale_price = $regular_price * 0.80; // 20% popust

                    // Postavi novu cenu
                    $product->set_sale_price($sale_price);
                    $product->save();
                }
                
                // Ako proizvod je varijabilan, koristi ovo
                elseif ($product->is_type('variable')) {
                    // Dobavi sve varijacije
                    $variations = $product->get_available_variations();
                    
                    foreach ($variations as $variation) {
                        $variation_id = $variation['variation_id'];
                        $variation_product = wc_get_product($variation_id);
                        
                        $regular_price = $variation_product->get_regular_price();
                        $sale_price = $regular_price * 0.80; // 20% popust
                        
                        // Postavi novu cenu
                        $variation_product->set_sale_price($sale_price);
                        $variation_product->save();
                    }
                }
            }
        }
        
        // Vrati WP_Query na prvobitno stanje
        wp_reset_postdata();
    }
}




function set_sale_dates_for_all_products($start_date, $end_date) {
    // Konvertovanje datuma u format koji WooCommerce koristi
    $start_date_formatted = date('Y-m-d', strtotime($start_date));
    $end_date_formatted = date('Y-m-d', strtotime($end_date));
    
    // Dobavljanje svih proizvoda
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
    );

    $products = get_posts($args);

    // Iteracija kroz sve proizvode
    foreach ($products as $product) {
        $product_id = $product->ID;
        $product_obj = wc_get_product($product_id);
        
        // Proveravanje da li proizvod ima promociju
        if ($product_obj->is_on_sale()) {
            // Postavljanje datuma početka i kraja promocije
            update_post_meta($product_id, '_sale_price_dates_from', strtotime($start_date_formatted));
            update_post_meta($product_id, '_sale_price_dates_to', strtotime($end_date_formatted));
        }
    }
}

// Pozivanje funkcije sa željenim datumima
add_action('init', function() {
    set_sale_dates_for_all_products('2024-08-06', '2024-09-02');
});
