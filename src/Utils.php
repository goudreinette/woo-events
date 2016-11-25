<?php namespace WooEvents;

class Utils
{
    /**
     * PHP
     */
    static function pluck($array, $key)
    {
        return array_values(array_map(function ($item) use ($key) {
            if (is_array($item)) {
                return $item[$key];
            } else {
                return $item->{$key};
            }
        }, $array));
    }

    static function takeIf($condition, $n, $array)
    {
        if ($condition)
            return array_slice($array, 0, $n);
        else
            return $array;
    }




    /**
     * WooCommerce
     */

    /**
     * @param array $args Additional query arguments
     * @return Array [String] of product categories
     */
    static function getProductCategories($args = [])
    {
        $categories = get_categories(array_merge(['taxonomy' => 'product_cat'], $args));
        return array_values(self::pluck($categories, 'cat_name'));
    }

    /**
     * @param $date String
     * @param $time String
     * @return String date formatted to WooCommerce preferences
     */
    static function formatDateTimeWoocommerce($date, $time)
    {
        $formatted = date(wc_date_format(), strtotime($date));
        return "$formatted, $time uur";
    }


    /**
     * Events
     */

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
     * @param $event An event with end-date
     * @return bool Whether the event is expired
     */
    static function isExpired($event)
    {
        return time() > strtotime($event['end-date']);
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
     * @param $events Array of products with woo-events meta
     * @return Array of extended events
     */
    static function prepareEvents($events)
    {
        return array_map(function ($event) {
            $meta                          = Model::getMeta($event->ID);
            $eventArray                    = array_merge((array)$event, $meta);
            $product                       = wc_get_product($eventArray['ID']);
            $eventArray['start-date-only'] = DateUtils::formatDate($eventArray['start-date']);
            $eventArray['start-date']      = self::formatDateTimeWoocommerce($meta['start-date'], $meta['start-time']);
            $eventArray['end-date']        = self::formatDateTimeWoocommerce($meta['end-date'], $meta['end-time']);
            $eventArray['price']           = $product->price;
            $eventArray['image']           = wp_get_attachment_image_src(get_post_thumbnail_id($event->ID), 'medium')[0];
            $eventArray['featured']        = self::featuredText($product);
            $eventArray['post_excerpt']    = substr($product->post->post_excerpt, 0, 140) . "...";
            $eventArray['product_cat']     = wp_get_post_terms($event->ID, 'product_cat')[0]->name;
            $eventArray['permalink']       = get_permalink($event->ID);
            $eventArray['add_to_cart_url'] = $product->add_to_cart_url();

            return $eventArray;
        }, $events);
    }

    static function featuredText(\WC_Product $product)
    {
        if ($product->is_featured())
            return "featured";
        if ($product->is_on_sale())
            return "sale";
    }

}