<?php namespace WooEvents;

class Display
{
    function __construct(View $view)
    {
        $this->view = $view;
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
        $meta = Model::getMeta($product->id);
        $this->display($product->id);

        if ($meta && $meta['external-link']) {
            $this->view->enqueueScript('single-product', [
                'external-link'      => $meta['external-link'],
                'external-link-text' => __('View', Model::$key)
            ]);
        }
    }

    /**
     * Duplication
     */
    function cart($name, $item)
    {
        $meta = Model::getMeta($item['product_id']);

        if ($meta && $meta['enable']) {
            $assigns = array_merge($meta, [
                'start-date' => WooUtils::formatDateTimeWoocommerce($meta['start-date'], $meta['start-time']),
                'end-date'   => WooUtils::formatDateTimeWoocommerce($meta['end-date'], $meta['end-time']),
                'name'       => $name
            ]);

            return $this->view->renderString('cart', $assigns);
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
        $meta = Model::getMeta($product_id);

        if ($meta && $meta['enable']) {
            $assigns = array_merge($meta, [
                'start-date' => WooUtils::formatDateTimeWoocommerce($meta['start-date'], $meta['start-time']),
                'end-date'   => WooUtils::formatDateTimeWoocommerce($meta['end-date'], $meta['end-time'])
            ]);

            $this->view->render($template, $assigns);
        }
    }
}