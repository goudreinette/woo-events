<?php namespace WooEvents;

use Utils;

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

        $assigns = array_merge($meta, [
            'key'              => Model::$key,
            'enable'           => $meta['enable'] ? 'checked' : '',
            'has-end'          => $meta['has-end'] ? 'checked' : '',
            'hide-add-to-cart' => $meta['hide-add-to-cart'] ? 'checked' : '',
            'start-time'       => DateUtils::formatTime($meta['start-time']),
            'end-time'         => DateUtils::formatTime($meta['end-time']),
            'start-date'       => DateUtils::formatDate($meta['start-date']),
            'end-date'         => DateUtils::formatDate($meta['end-date'])
        ]);

        $this->view->enqueueStyle('admin');
        $this->view->enqueueStyle('datepicker/datepicker');
        $this->view->enqueueScript('datepicker/datepicker');
        $this->view->enqueueScript('datepicker/en');
        $this->view->render('admin', $assigns);
    }

    function handleSave($productId)
    {
        // Avoid infinite loop
        remove_action('save_post', [$this, 'handleSave']);

        $key  = Model::$key;
        $meta = $this->processDateMeta($_POST[$key]);

        Model::update($productId, $meta);
    }

    function processDateMeta($meta)
    {
        $start = explode(" ", $meta['start-date']);
        $end   = explode(" ", $meta['end-date']);

        $meta['start-date'] = $start[0];
        $meta['start-time'] = $start[1];

        if (!$meta['has-end']) {
            $meta['end-date'] = $start[0];
            $meta['end-time'] = $start[1];
        } else {
            $meta['end-date'] = $end[0];
            $meta['end-time'] = $end[1];
        }

        return $meta;
    }
}