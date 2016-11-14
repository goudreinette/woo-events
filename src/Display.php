<?php namespace WooEvents;

class Display
{
    function __construct($mustache)
    {
        $this->m = $mustache;
        add_action('woocommerce_after_shop_loop', [$this, 'style']);
        add_action('woocommerce_before_shop_loop_item_title', [$this, 'shopLoop']);
        add_action('woocommerce_single_product_summary', [$this, 'singleProduct']);
        add_action('woocommerce_order_item_meta_start', [$this, 'emails'], 10, 4);
    }

    function style()
    {
        wp_enqueue_style('woo-events', plugin_dir_url(__DIR__) . '/styles/style.css');
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
                'startDate' => Utils::formatDate($meta['start-date'], $meta['start-time']),
                'endDate'   => Utils::formatDate($meta['end-date'], $meta['end-time'])
            ];

            echo $this->m->render($template, $assigns);
        }
    }

}