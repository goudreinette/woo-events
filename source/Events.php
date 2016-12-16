<?php namespace WooEvents;

use Utils\Utils;
use Utils\WooUtils;
use Utils\Date;

class Events
{

    /**
     * @param $categories Array[String] of category names
     * @param $events     Array of events
     * @return Filtered Array of Events
     */
    static function selectEventsByCategories($categories, $events)
    {
        return array_values(array_filter($events, function ($event) use ($categories) {
            return count(array_intersect($event['product_cats'], $categories)) > 0;
        }));
    }

    /**
     * @param $filter 'Show' or 'Only' or 'Hide'
     * @param $events Array of events with end-date
     * @return Filtered Array of events
     */
    static function filterExpiredEvents($filter, $events)
    {
        return array_values(array_filter($events, function ($event) use ($filter) {
            $isExpired = self::isExpired($event);

            switch ($filter) {
                case 'Only':
                    return $isExpired;
                case 'Hide':
                    return !$isExpired;
                case 'Show':
                    return true;
                default:
                    return true;
            }
        }));
    }

    /**
     * @param $endDate string
     * @return bool Whether the event is expired
     */
    static function isExpired($event)
    {
        return time() > strtotime($event['end-date'] . " " . $event['end-time'] . " +12 hours");
    }

    /**
     * @param $events Array of events
     * @param $order  'Ascending' or 'Descending'
     * @return Sorted Array of events
     */
    static function sortEvents($events, $order)
    {
        $orderModifier = $order == 'Ascending' ? 1 : -1;

        usort($events, function ($a, $b) use ($orderModifier) {
            return (strtotime($a['start-date']) - strtotime($b['start-date'])) * $orderModifier;
        });

        return $events;
    }

    /**
     * @param $posts Array of posts that have woo-events meta
     * @return Array of extended events
     */
    static function prepareEvents($posts)
    {
        $result = array_map('self::getEvent', $posts);
        return $result;
    }

    static function getEvent($post)
    {
        $meta       = Meta::getMeta($post->ID);
        $eventArray = array_merge((array)$post, $meta);
        $product    = wc_get_product($eventArray['ID']);

        $eventArray['start-date-only'] = Date::formatDate($eventArray['start-date']);
        $eventArray['start-date']      = WooUtils::formatDateTimeWoocommerce($meta['start-date'], $meta['start-time']);
        $eventArray['end-date']        = WooUtils::formatDateTimeWoocommerce($meta['end-date'], $meta['end-time']);
        $eventArray['price']           = $product->get_price_html();
        $eventArray['image']           = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'medium')[0];
        $eventArray['featured']        = WooUtils::featuredText($product);
        $eventArray['post_excerpt']    = substr($product->post->post_excerpt, 0, 100) . "...";
        $eventArray['product_cats']    = self::getEventCategories($post->ID);
        $eventArray['product_cat']     = $eventArray['product_cats']['0'];
        $eventArray['permalink']       = get_permalink($post->ID);
        $eventArray['add_to_cart_url'] = $product->add_to_cart_url();

        return $eventArray;
    }

    static function getEventCategories($postId)
    {
        $checked     = Utils::array_pluck(wp_get_post_terms($postId, 'product_cat'), 'term_id');
        $ancestorIds = Utils::array_flatmap('Utils\WooUtils::categoryLegacy', $checked);

        if (count($ancestorIds) > 0) {
            $ancestors = Meta::getCategories(['include' => $ancestorIds]);
            $names     = Utils::array_pluck($ancestors, 'cat_name');
            return array_unique($names);
        } else {
            return [];
        }
    }

    static function getEvents()
    {
        return get_posts([
            'post_type'        => 'product',
            'meta_key'         => Meta::$key,
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
            $meta       = Meta::getMeta($event->ID);
            $categories = wp_get_object_terms($event->ID, 'product_cat', ['fields' => 'ids']);

            if (Events::isExpired($meta))
                $categories = [$expiredCategory];
            else
                $categories = array_diff($categories, [$expiredCategory]);

            wp_set_post_terms($event->ID, $categories, 'product_cat');
        }
    }
}
