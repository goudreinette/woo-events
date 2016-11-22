<?php namespace WooEvents;

class Admin
{
    function __construct(View $view)
    {
        $this->view = $view;
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
            'key'           => Model::$key,
            'enable'        => $meta['enable'] ? 'checked' : '',
            'has-end'       => $meta['has-end'] ? 'checked' : '',
            'start-time'    => Utils::formatTime($meta['start-time']),
            'end-time'      => Utils::formatTime($meta['end-time']),
            'start-date'    => Utils::formatDate($meta['start-date']),
            'end-date'      => Utils::formatDate($meta['end-date']),
            'external-link' => $meta['external-link'],
            'subtitle'      => $meta['subtitle']
        ];

        $this->view->enqueueStyle('admin');
        $this->view->render('admin', $assigns);
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