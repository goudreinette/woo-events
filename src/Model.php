<?php namespace WooEvents;

class Model
{
    static $key = "woo-events";
    static $name = "WooEvents";
    static $defaults = [
        'key'           => 'woo-events',
        'enable'        => '',
        'has-end'       => '',
        'start-time'    => null,
        'end-time'      => null,
        'start-date'    => null,
        'end-date'      => null,
        'external-link' => '',
    ];

    static function getMeta($productId)
    {
        return get_post_meta($productId, self::$key, true);
    }

    static function update($productId, $meta)
    {
        update_post_meta($productId, self::$key, $meta);

        if ($meta['enable']) {
            self::updatePublicationDate($productId, $meta['start-date'], $meta['start-time']);
        }
    }

    static function updatePublicationDate($postId, $date, $time)
    {
        $post              = get_post($postId, ARRAY_A);
        $post['post_date'] = "$date $time";
        wp_update_post($post);
    }

    static function getCategories()
    {
        return get_categories(['taxonomy' => 'product_cat']);
    }

    static function getEvents()
    {
        return get_posts([
            'post_type'        => 'product',
            'meta_key'         => self::$key,
            'numberposts'      => -1,
            'suppress_filters' => true
        ]);
    }
}