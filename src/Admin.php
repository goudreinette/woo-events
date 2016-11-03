<?php namespace WooEvents;

class Admin
{
    function __construct()
    {
        add_filter('woocommerce_product_data_tabs', [$this, 'registerTab']);
        add_filter('woocommerce_product_data_panels', [$this, 'render']);
        add_action('save_post', [$this, 'handleSave']);
    }

    function registerTab($tabs)
    {
        $tabs[Meta::$key] = [
            'label' => Meta::$name,
            'priority' => 50,
            'target' => Meta::$key
        ];

        return $tabs;
    }

    function render()
    {
        global $product;
        $key = Meta::$key;
        $meta = Meta::get($product->id);
        $startDate = $this->formatDate($meta[Meta::$key . 'start-date']);
        $endDate = $this->formatDate($meta[Meta::$key . 'end-date']);
        $externalLink = $meta['external-link'];

        echo "
            <div id='$key' class='panel woocommerce_options_panel hidden'>
                <div>
                    <h3>Start Date</h3>
                    <input name='$key-start-date' type='date' value='$startDate'>
                </div>
                <div>
                    <h3>End Date</h3>
                    <input name='$key-end-date' type='date' value='$endDate'>
                </div>
                <div>
                    <h3>External link</h3>
                    <input name='$key-external-link' type='url' value='$externalLink'>
                </div>
            </div>        
        ";
    }

    function formatDate($timestamp)
    {
        return date('Y-m-d', $timestamp ?: time());
    }

    function handleSave($productId)
    {
        $key = Meta::$key;
        $keys = ["$key-start-date", "$key-end-date", "$key-external-link"];
        $meta = array_intersect_key($_POST, array_flip($keys));

        Meta::update($productId, $meta);
    }
}