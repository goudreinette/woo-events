<?php namespace WooEvents;

use Utils\Date;
use Utils\Utils;
use Utils\WooUtils;

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

    static function flattenMeta()
    {
        $eventIds = Utils::array_pluck(self::getEvents(), 'ID');

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

    static function getEvents()
    {
        return get_posts([
            'post_type'        => 'product',
            'meta_key'         => self::$key,
            'numberposts'      => -1,
            'suppress_filters' => true
        ]);
    }

    /**
     * If an event is expired, add the expired category.
     * Else, remove it
     */
    static function updateExpired()
    {
        $expiredCategory = get_term_by('name', 'Expired', 'product_cat', ARRAY_A)['term_id'];


        /**
         * Create the term if it doesn't exist.
         */
        if (!$expiredCategory) {
            $expiredCategory = wp_insert_term('Expired', 'product_cat')['term_id'];
        }

        foreach (self::getEvents() as $event) {
            $meta       = self::getMeta($event->ID);
            $categories = wp_get_object_terms($event->ID, 'product_cat', ['fields' => 'ids']);

            if (EventUtils::isExpired($meta))
                $categories = [$expiredCategory];
            else
                $categories = array_diff($categories, [$expiredCategory]);

            wp_set_post_terms($event->ID, $categories, 'product_cat');
        }
    }
}