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

    }

    static function updatePublicationDate($postId, $date)
    {
        $post              = get_post($postId, ARRAY_A);
        $post['post_date'] = $date;
        wp_update_post($post);
    }
}