<?php namespace WooEvents;

class Admin
{
    function __construct($mustache)
    {
        $this->view = $mustache;
        add_filter('woocommerce_product_data_tabs', [$this, 'registerTab']);
        add_filter('woocommerce_product_data_panels', [$this, 'render']);
        add_action('save_post', [$this, 'handleSave']);
    }

    function registerTab($tabs)
    {
        $tabs[Model::$key] = [
            'label'    => Model::$name,
            'priority' => 50,
            'target'   => Model::$key
        ];

        return $tabs;
    }

    function render()
    {
        global $post;
        $meta = Model::getMeta($post->ID) ?: Model::$defaults;

        $assigns = [
            'key'          => Model::$key,
            'checked'      => $meta['enable'] ? 'checked' : '',
            'startTime'    => $this->formatTime($meta['start-time']),
            'endTime'      => $this->formatTime($meta['end-time']),
            'startDate'    => $this->formatDate($meta['start-date']),
            'endDate'      => $this->formatDate($meta['end-date']),
            'externalLink' => $meta['external-link'],
        ];

        wp_enqueue_style('woo-events', plugin_dir_url(__DIR__) . '/styles/style.css');
        echo $this->view->render('admin', $assigns);
    }

    function formatDate($date = null)
    {
        return date('Y-m-d', strtotime($date) ?: time());
    }

    function formatTime($time = null)
    {
        return date('H:i', strtotime($time) ?: time());
    }

    function handleSave($productId)
    {
        // Avoid infinite loop
        remove_action('save_post', [$this, 'handleSave']);

        $key  = Model::$key;
        $meta = $_POST[$key];
        Model::update($productId, $meta);
    }
}