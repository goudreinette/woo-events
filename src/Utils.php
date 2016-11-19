<?php namespace WooEvents;

class Utils
{
    /**
     * PHP
     */
    static function pluck($array, $key)
    {
        return array_map(function ($item) use ($key) {
            if (is_array($item)) {
                return $item[$key];
            } else {
                return $item->{$key};
            }
        }, $array);
    }

    /**
     * Wordpress
     */
    static function formatDate($date, $time)
    {
        return date(wc_date_format(), strtotime($date)) . " " . $time;
    }

    /**
     * WooCommerce
     */
    static function getProductCategories()
    {
        $categories = get_categories(['taxonomy' => 'product_cat']);
        return array_values(self::pluck($categories, 'cat_name'));
    }
}