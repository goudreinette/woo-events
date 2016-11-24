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
        $limited      = Utils::takeIf($options['enable-limit'], $options['limit'], $filtered);

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