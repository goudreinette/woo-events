<?php namespace WooEvents;

class Display
{
    function __construct()
    {
        add_action('woocommerce_before_shop_loop_item_title', [$this, 'shopLoop']);
        add_action('woocommerce_single_product_summary', [$this, 'singleProduct']);
        add_action('woocommerce_order_item_meta_start', [$this, 'emails'], 10, 4);
    }

    function shopLoop()
    {
        global $product;
        $meta = Meta::get($product->id);

        if ($meta && $meta['enable']) {
            $startDate = $this->formatDate($meta['start-date'], $meta['start-time']);
            $endDate   = $this->formatDate($meta['end-date'], $meta['end-time']);


            echo "<span>{$startDate}     -   {$endDate}</span>";
        }
    }

    function formatDate($date, $time)
    {
        return date(wc_date_format(), strtotime($date)) . " " . $time;
    }

    function singleProduct()
    {
        global $product;
        $meta = Meta::get($product->id);

        if ($meta && $meta['enable']) {
            $startDate = $this->formatDate($meta['start-date'], $meta['start-time']);
            $endDate   = $this->formatDate($meta['end-date'], $meta['end-time']);


            echo "<h3>
                <span class='start'>{$startDate}</span> - 
                <span class='end'>{$endDate}</span>
            </h3>";
        }
    }

    function emails($_, $item)
    {
        $meta = Meta::get($item['product_id']);

        if ($meta && $meta['enable']) {
            $startDate = $this->formatDate($meta['start-date'], $meta['start-time']);
            $endDate   = $this->formatDate($meta['end-date'], $meta['end-time']);


            echo "<h3>
                <span class='start'>{$startDate}</span> - 
                <span class='end'>{$endDate}</span>
            </h3>";
        }
    }
}