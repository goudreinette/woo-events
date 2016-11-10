<?php namespace WooEvents;

class Admin
{
    function __construct($mustache)
    {
        $this->m = $mustache;
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
        $meta = Meta::get($post->ID);

        $assigns = [
            'key'          => Meta::$key,
            'checked'      => '',
            'startTime'    => $this->formatTime(),
            'endTime'      => $this->formatTime(),
            'startDate'    => $this->formatDate(),
            'endDate'      => $this->formatDate(),
            'externalLink' => '',
        ];


        if ($meta) {
            $assigns = array_merge($assigns, [
                'key'          => Meta::$key,
                'checked'      => $meta['enable'] ? 'checked' : '',
                'startTime'    => $this->formatTime($meta['start-time']),
                'endTime'      => $this->formatTime($meta['end-time']),
                'startDate'    => $this->formatDate($meta['start-date']),
                'endDate'      => $this->formatDate($meta['end-date']),
                'externalLink' => $meta['external-link'],
            ]);
        }


        wp_enqueue_style('woo-events', plugin_dir_url(__DIR__) . '/styles/style.css');
        echo $this->m->render('admin', $assigns);
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

        $key  = Meta::$key;
        $meta = $_POST[$key];
        Meta::update($productId, $meta);
    }
}