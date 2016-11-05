<?php namespace WooEvents;

class Admin
{
    function __construct()
    {
        $this->m = new \Mustache_Engine(['loader' => new Mustache_Loader_FilesystemLoader(plugin_basename(__FILE__) . '/templates')]);
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

        $assigns = [
            'checked'      => $meta['enable'] ? 'checked' : '',
            'startTime'    => $meta['start-time'],
            'endTime'      => $meta['end-time'],
            'externalLink' => $meta['external-link'],
            'startDate'    => $this->formatDate($meta['start-date']),
            'endDate'      => $this->formatDate($meta['end-date'])
        ];

        wp_enqueue_style('woo-events', plugin_dir_url(__DIR__) . '/styles/style.css');

        echo $this->m->render('')
    }

    function formatDate($date)
    {
        return date('Y-m-d', strtotime($date) ?: time());
    }

    function handleSave($productId)
    {
        // Avoid infinite loop
        remove_action('save_post', [$this, 'handleSave']);

        $key  = Meta::$key;
        $meta = $_POST[$key];
        Meta::update($productId, $meta);
    }
}