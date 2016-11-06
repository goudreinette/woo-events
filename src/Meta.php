<?php namespace WooEvents;

class Meta
{
    static $key = "woo-events";
    static $name = "WooEvents";

    static function get($productId)
    {
        return get_post_meta($productId, self::$key, true);
    }

    static function update($productId, $meta)
    {
        update_post_meta($productId, self::$key, $meta);

        if ($meta['enable']) {
            self::updatePublicationDate($productId, $meta['start-date']);
        }
    }

    static function updateExpired()
    {
        $products = self::getEvents();

        foreach ($products as $product) {
            $meta = self::get($product->ID);
            if (time() > strtotime($meta['end-date'])) {
                $expiredCategory = get_term_by('name', 'expired', 'product_cat');
                wp_set_post_terms($product->ID, $expiredCategory->term_id, 'product_cat');
            }
        }
    }

    static function updatePublicationDate($postId, $date)
    {
        $post              = get_post($postId, ARRAY_A);
        $post['post_date'] = $date;
        wp_update_post($post);
    }

    static function getEvents()
    {
        return get_posts(['post_type'        => 'product',
                          'meta_key'         => self::$key,
                          'numberposts'      => -1,
                          'suppress_filters' => true]);
    }
}