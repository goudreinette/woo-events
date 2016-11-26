<?php namespace WooEvents;

use Utils;

class Shortcode
{
    function __construct(View $view)
    {
        $this->view = $view;
        add_shortcode(Model::$key, [$this, 'shortcode']);
        add_action('vc_before_init', [$this, 'vc']);
    }

    function shortcode($options)
    {
        $options      = vc_map_get_attributes(Model::$key, $options);
        $categories   = explode(',', $options['categories']);
        $events       = Model::getEvents();
        $sorted       = sortEvents(prepareEvents($events), $options['order']);
        $withCategory = selectEventsByCategories($categories, $sorted);
        $limited      = Utils\takeIf($options['enable-limit'], $options['limit'], $withCategory);

        /**
         * Translations
         */
        $options = array_merge($options, [
            'order_text'       => __('Order', 'woo-events'),
            'add_to_cart_text' => __('Add to Cart', 'woo-events')
        ]);

        $assigns = [
            'categories' => $categories,
            'events'     => $limited,
            'options'    => $options
        ];

        $this->view
            ->render('eventlist', $assigns)
            ->enqueueStyle('event-list')
            ->enqueueScript('event-list');
    }

    function chosenParamType($settings, $value)
    {
        return $this->view->renderString('chosen', ['settings' => $settings, 'value' => $value]);
    }

    function vc()
    {
        $this->view->enqueueScript('chosen');
        $this->view->enqueueStyle('chosen');
        vc_add_shortcode_param('chosen', [$this, 'chosenParamType']);
        vc_map([
            'name'     => 'WooCommerce Event List',
            'base'     => Model::$key,
            'class'    => '',
            'category' => 'WooCommerce',
            'params'   => $this->params()
        ]);
    }

    function params()
    {
        return [
            [
                'group'       => 'Query',
                'type'        => 'chosen',
                'heading'     => 'Product Categories',
                'param_name'  => 'categories',
                'save_always' => true,
                'value'       => WooUtils::getProductCategories()
            ],
            [
                'group'      => 'Query',
                'type'       => 'checkbox',
                'heading'    => 'Enable Limit',
                'param_name' => 'enable-limit'
            ],
            [
                'group'      => 'Query',
                'type'       => 'textfield',
                'heading'    => 'Limit',
                'param_name' => 'limit'
            ],
            [
                'group'      => 'Layout',
                'type'       => 'dropdown',
                'heading'    => 'Order',
                'param_name' => 'order',
                'value'      => ['Ascending', 'Descending'],
            ],
            [
                'group'      => 'Layout',
                'type'       => 'dropdown',
                'heading'    => 'Image Proportion (width:height)',
                'param_name' => 'image_proportion',
                'value'      => ['1:1' => 1, '2:3' => 1.66, '4:3' => 0.75]
            ],
            [
                'group'      => 'Layout',
                'type'       => 'dropdown',
                'heading'    => 'Category Filter',
                'param_name' => 'show_category_filter',
                'value'      => ['Show' => 'show', 'Hide' => false]
            ],
            [
                'group'      => 'Layout',
                'type'       => 'dropdown',
                'heading'    => 'Date',
                'param_name' => 'show_date',
                'value'      => ['Show' => 'show', 'Hide' => false]
            ],
            [
                'group'      => 'Colors',
                'type'       => 'colorpicker',
                'heading'    => 'Title Color',
                'param_name' => 'title_color',
                'value'      => '#000',
            ],
            [
                'group'      => 'Colors',
                'type'       => 'colorpicker',
                'heading'    => 'Subtitle Color',
                'param_name' => 'subtitle_color',
                'value'      => '#666',
            ]
        ];
    }
}