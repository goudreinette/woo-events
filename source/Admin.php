<?php namespace WooEvents;

use Utils\Date;
use Utils\View;

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


        $assigns = (array)new Event($post->ID);

        $this->view->enqueueStyle('admin');
        $this->view->enqueueStyle('datepicker/datepicker');
        $this->view->enqueueScript('datepicker/datepicker');
        $this->view->enqueueScript('datepicker/en');
        $this->view->render('admin', $assigns);
    }

    function checked($condition)
    {
        return $condition ? 'checked' : '';
    }

    function handleSave($productId)
    {
        // Avoid infinite loop
        remove_action('save_post', [$this, 'handleSave']);

        $event    = new Event($productId);
        $formData = $_POST[$event->key];

        foreach ($formData as $key => $value)
            $event->$key = $value;

        return;
    }
}