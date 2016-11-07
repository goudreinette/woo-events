<?php namespace WooEvents;

class Display
{
    function __construct()
    {
        $this->m = new \Mustache_Engine(['loader' => new \Mustache_Loader_FilesystemLoader(plugin_dir_path(__DIR__) . '/templates')]);
        add_action('woocommerce_before_shop_loop_item_title', [$this, 'shopLoop']);
        add_action('woocommerce_single_product_summary', [$this, 'singleProduct']);
        add_action('woocommerce_order_item_meta_start', [$this, 'emails'], 10, 4);
    }

    /**
     * Display the event date on shop loop items.
     */
    function shopLoop()
    {
        global $product;
        $this->display($product->id, 'shoploop');
    }

    /**
     * Display the event date on single product page.
     * Optionally redirect.
     */
    function singleProduct()
    {
        global $product;
        $meta = Meta::get($product->id);
        $this->display($product->id);

        if ($meta && $meta['external-link']) {
            wp_enqueue_script('external-link', plugin_dir_url(__DIR__) . '/js/external-link.js');
            wp_localize_script('external-link', 'external_link', [$meta['external-link']]);
        }
    }

    /**
     * Display the event date on emails.
     */
    function emails($_, $item)
    {
        $this->display($item['product_id']);
    }

    function display($product_id, $template = 'display')
    {
        $meta = Meta::get($product_id);

        if ($meta && $meta['enable']) {
            $assigns = [
                'startDate' => $this->formatDate($meta['start-date'], $meta['start-time']),
                'endDate'   => $this->formatDate($meta['end-date'], $meta['end-time'])
            ];

            echo $this->m->render($template, $assigns);
        }
    }

    function formatDate($date, $time)
    {
        return date(wc_date_format(), strtotime($date)) . " " . $time;
    }
}