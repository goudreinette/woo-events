<?php namespace WooEvents;

use Utils\PluginContext;
use Utils\View;
use Utils\WooUtils;

class Display
{
    /**
     * @var View;
     */
    public $view;

    /**
     * @var PluginContext
     */
    public $context;

    function __construct()
    {
        add_action('woocommerce_after_shop_loop', [$this, 'style']);
        add_action('woocommerce_before_shop_loop_item_title', [$this, 'shopLoop']);
        add_action('woocommerce_single_product_summary', [$this, 'singleProduct']);
        add_filter('woocommerce_cart_item_name', [$this, 'cart'], 10, 2);
        add_action('woocommerce_order_item_meta_start', [$this, 'emails'], 10, 4);
    }

    function style()
    {
        $this->view->enqueueStyle('style');
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
    function singleProduct($item)
    {
        global $product;
        $event = new Event($product->id);
        $this->display($product->id);

        /**
         * Always enqueue the script.
         * Script behaviour is parameterized by;
         *  - hide button
         *  - button text
         *  - external link
         */
        $this->view->enqueueScript('single-product', [
            'cartButtonText' => $event->cartButtonText,
            'hideButton'     => $event->hideButton,
            'externalLink'   => $event->externalLink
        ]);
    }

    /**
     * Duplication
     */
    function cart($name, $item)
    {
        $event       = new Event($item['product_id']);
        $event->name = $name;

        if ($event->enable) {
            return $this->view->renderString('cart', (array)$event);
        } else {
            return $name;
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
        $event = new Event($product_id);

        if ($event->enable) {
            $this->view->render($template, (array)$event);
        }
    }
}