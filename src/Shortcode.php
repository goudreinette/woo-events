<?php namespace WooEvents;

class Shortcode
{
    function __construct()
    {
        add_shortcode(Meta::$key, [$this, 'shortcode']);
        add_action('vc_before_init', [$this, 'vc']);
    }

    function shortcode($atts)
    {
        $atts = shortcode_atts([
            'layout'               => 'List',
            'order'                => 'Ascending',
            'show_category_filter' => true,
            'show_date'            => true,
            'title_color'          => '#000',
            'subtitle_color'       => '#4c4c4c'
        ], $atts);

        echo "<h1>Event List Here!</h1>";

    }

    function vc()
    {
        vc_map([
            'name'     => 'WooCommerce Event List',
            'base'     => Meta::$key,
            'class'    => '',
            'category' => 'woocommerce',
            'params'   => [
                [
                    'group'      => 'Options',
                    'type'       => 'dropdown',
                    'heading'    => 'Layout',
                    'param_name' => 'layout',
                    'value'      => ['Grid', 'List'],
                ],
                [
                    'group'      => 'Options',
                    'type'       => 'dropdown',
                    'heading'    => 'Order',
                    'param_name' => 'order',
                    'value'      => ['Ascending', 'Descending'],
                ],
                [
                    'group'      => 'Options',
                    'type'       => 'textfield',
                    'heading'    => 'Order Button Text',
                    'param_name' => 'button_text',
                    'value'      => 'Order',
                ],
                [
                    'group'      => 'Options',
                    'type'       => 'checkbox',
                    'heading'    => 'Show Category Filter',
                    'param_name' => 'show_category_filter',
                    'value'      => true,
                ],
                [
                    'group'      => 'Options',
                    'type'       => 'checkbox',
                    'heading'    => 'Show Date',
                    'param_name' => 'show_date',
                    'value'      => true,
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
            ]
        ]);
    }
}