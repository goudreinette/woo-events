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
    }

    static function updateExpired()
    {

    }

    static function updatePublicationDate()
    {

    }
}