<?php namespace WooEvents;

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
        $sorted       = Utils::sortEvents(Utils::prepareEvents($events), $options['order']);
        $withCategory = Utils::selectEventsByCategories($categories, $sorted);
        $filtered     = Utils::filterExpiredEvents($options['expired'], $withCategory);

        $assigns = [
            'categories' => $categories,
            'events'     => $filtered,
            'options'    => $options
        ];

        $this->view
            ->echo('eventlist', $assigns)
            ->enqueueStyle('event-list');
    }

    function chosenParamType($settings, $value)
    {
        $this->view->enqueueScript('chosen.jquery.min.js');
        return $this->view->render('chosen', ['settings' => $settings, 'value' => $value]);
    }

    function vc()
    {
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
                'value'       => Utils::getProductCategories()
            ],
            [
                'group'      => 'Query',
                'type'       => 'dropdown',
                'heading'    => 'Expired',
                'param_name' => 'expired',
                'value'      => ['Show', 'Hide', 'Only']
            ],
            [
                'group'      => 'Layout',
                'type'       => 'dropdown',
                'heading'    => 'Layout',
                'param_name' => 'layout',
                'value'      => ['Grid', 'List'],
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
                'type'       => 'textfield',
                'heading'    => 'Image Height (px)',
                'param_name' => 'image_height',
                'value'      => 150
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
                'group'      => 'Layout',
                'type'       => 'textfield',
                'heading'    => 'Add to Cart Button Text',
                'param_name' => 'add_to_cart_text',
                'value'      => 'Add to Cart',
            ],
            [
                'group'      => 'Layout',
                'type'       => 'textfield',
                'heading'    => 'Order Button Text',
                'param_name' => 'button_text',
                'value'      => 'Order'
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