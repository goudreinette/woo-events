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
            'label'    => Meta::$name,
            'priority' => 50,
            'target'   => Meta::$key
        ];

        return $tabs;
    }

    function render()
    {
        global $post;
        $key  = Meta::$key;
        $meta = Meta::get($post->ID);

        $checked      = $meta['enable'] ? 'checked' : '';
        $startDate    = $this->formatDate($meta['start-date']);
        $endDate      = $this->formatDate($meta['end-date']);
        $externalLink = $meta['external-link'];

        wp_enqueue_style('woo-events', plugin_dir_url(__DIR__) . '/styles/style.css');

        echo "
            <div id='$key' class='panel woocommerce_options_panel hidden'>
                <div>
                    <span>Enable</span>
                    <input name='$key-[enable]' type='checkbox' $checked>
                </div>
                <div>
                    <span>Start Date</span>
                    <input name='$key-[start-date]' type='date' value='$startDate'>
                </div>
                <div>
                    <span>End Date</span>
                    <input name='$key-[end-date]' type='date' value='$endDate'>
                </div>
                <div>
                    <span>External link</span>
                    <input name='$key-[external-link]' type='url' value='$externalLink'>
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
        $key  = Meta::$key;
        $meta = $_POST[$key . '-'];

        Meta::update($productId, $meta);
    }
}