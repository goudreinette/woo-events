<?php namespace WooEvents;

use Utils\Date;
use Utils\Utils;
use Utils\WooUtils;

class Meta
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

    static function flatten()
    {
        $eventIds = Utils::array_pluck(Events::getEvents(), 'ID');

        foreach ($eventIds as $eventId) {
            $meta      = self::getMeta($eventId);
            $extraMeta = [
                'full-start-date' => WooUtils::formatDateTimeWoocommerce($meta['start-date'], $meta['start-time']),
                'full-end-date'   => WooUtils::formatDateTimeWoocommerce($meta['end-date'], $meta['end-time'])
            ];

            foreach (array_merge($meta, $extraMeta) as $subKey => $subValue) {
                $fullKey = self::$key . "-" . $subKey;
                update_post_meta($eventId, $fullKey, $subValue);
            }
        }
    }

    static function updatePublicationDate($postId, $date, $time)
    {
        $post              = get_post($postId, ARRAY_A);
        $post['post_date'] = "$date $time";
        wp_update_post($post);
    }

    static function getCategories($args = [])
    {
        return get_categories(array_merge(['taxonomy' => 'product_cat'], $args));
    }
}