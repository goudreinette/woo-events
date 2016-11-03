<?php namespace WooEvents;

class Admin
{
    function __construct()
    {
        add_filter('woocommerce_product_data_tabs', [$this, 'registerTab']);
        add_filter('woocommerce_product_data_panels', [$this, 'render']);
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
        $startDate = $this->formatDate($meta['start-date']);
        $endDate = $this->formatDate($meta['end-date']);
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
        return date('m-d-Y', time());
    }

    function update()
    {

    }
}