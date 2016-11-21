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
        $view = Model::getMeta($post->ID) ?: Model::$defaults;

        $assigns = [
            'key'          => Model::$key,
            'checked'      => $view['enable'] ? 'checked' : '',
            'startTime'    => Utils::formatDate($view['start-time']),
            'endTime'      => Utils::formatTime($view['end-time']),
            'startDate'    => Utils::formatDate($view['start-date']),
            'endDate'      => Utils::formatTime($view['end-date']),
            'externalLink' => $view['external-link'],
        ];

        $this->view->enqueueStyle('style');
        $this->view->echo('admin', $assigns);
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