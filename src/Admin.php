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
        $meta = Meta::getMeta($post->ID) ?: Meta::$defaults;

        $assigns = array_merge($meta, [
            'key'              => Meta::$key,
            'enable'           => $meta['enable'] ? 'checked' : '',
            'hide-button'      => $meta['hide-button'] ? 'checked' : '',
            'has-end'          => $meta['has-end'] ? 'checked' : '',
            'hide-add-to-cart' => $meta['hide-add-to-cart'] ? 'checked' : '',
            'start-time'       => Date::formatTime($meta['start-time']),
            'end-time'         => Date::formatTime($meta['end-time']),
            'start-date'       => Date::formatDate($meta['start-date']),
            'end-date'         => Date::formatDate($meta['end-date'])
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

        $key  = Meta::$key;
        $meta = $this->processDateMeta($_POST[$key]);

        Meta::update($productId, $meta);
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