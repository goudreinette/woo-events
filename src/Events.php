<?php namespace WooEvents;

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
            return in_array($event['product_cat'], $categories);
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
            $isExpired = self::isExpired($event['end-date']);

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
    static function isExpired($endDate)
    {
        return time() > strtotime("$endDate +12 hours");
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
        return array_map('self::getEvent', $posts);
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
        $eventArray['product_cat']     = wp_get_post_terms($post->ID, 'product_cat')[0]->name;
        $eventArray['permalink']       = get_permalink($post->ID);
        $eventArray['add_to_cart_url'] = $product->add_to_cart_url();

        return $eventArray;
    }

    public static function getEvents()
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
    public static function updateExpired()
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

            if (Events::isExpired($meta['end-date']))
                $categories = [$expiredCategory];
            else
                $categories = array_diff($categories, [$expiredCategory]);

            wp_set_post_terms($event->ID, $categories, 'product_cat');
        }
    }
}
