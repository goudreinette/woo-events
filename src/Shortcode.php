<?php namespace WooEvents;

class Shortcode
{
    function __construct($mustache)
    {
        $this->m = $mustache;
        add_shortcode(Meta::$key, [$this, 'shortcode']);
        add_action('vc_before_init', [$this, 'vc']);
    }

    function shortcode($options)
    {
        $options = array_merge([
            'layout'               => 'List',
            'order'                => 'Ascending',
            'button_text'          => 'Order',
            'add_to_cart_text'     => 'Add to Cart',
            'show_category_filter' => true,
            'show_date'            => true,
            'title_color'          => '#000',
            'image_height'         => 150,
            'subtitle_color'       => '#666',
            'categories'           => ''
        ], $options);

        $events     = Meta::getEvents();
        $complete   = $this->sortEvents($this->prepareEvents($events), $options['order']);
        $complete   = array_values($this->selectEventsByCategories(explode(',', $options['categories']), $complete));
        $categories = array_unique(Utils::pluck($complete, 'product_cat'));

        $assigns = [
            'categories' => $categories,
            'events'     => $complete,
            'options'    => $options
        ];

        echo $this->m->render('eventlist', $assigns);
        wp_enqueue_script('woo-event-list', plugin_dir_url(__DIR__) . '/js/event-list.js');
        wp_enqueue_style('event-list', plugin_dir_url(__DIR__) . '/styles/event-list.css');
    }

    function prepareEvents($events)
    {
        return array_map(function ($event) {
            $meta                          = Meta::get($event->ID);
            $eventArray                    = array_merge((array)$event, $meta);
            $product                       = wc_get_product($eventArray['ID']);
            $eventArray['start-date']      = Utils::formatDate($meta['start-date'], $meta['start-time']);
            $eventArray['end-date']        = Utils::formatDate($meta['end-date'], $meta['end-time']);
            $eventArray['price']           = $product->price;
            $eventArray['image']           = wp_get_attachment_image_src(get_post_thumbnail_id($event->ID))[0];
            $eventArray['post_excerpt']    = substr($eventArray['post_content'], 0, 140) . "...";
            $eventArray['product_cat']     = wp_get_post_terms($event->ID, 'product_cat')[0]->name;
            $eventArray['permalink']       = get_permalink($event->ID);
            $eventArray['add_to_cart_url'] = $product->add_to_cart_url();

            return $eventArray;
        }, $events);
    }

    function sortEvents($events, $order)
    {
        $orderModifier = $order == 'Ascending' ? 1 : -1;

        usort($events, function ($a, $b) use ($orderModifier) {
            return (strtotime($a['start-date']) - strtotime($b['start-date'])) * $orderModifier;
        });

        return $events;
    }

    function selectEventsByCategories($categories, $events)
    {
        return array_filter($events, function ($event) use ($categories) {
            return in_array($event['product_cat'], $categories);
        });
    }

    function chosenParamType($settings, $value)
    {
        wp_enqueue_script('chosen', plugin_dir_url(__DIR__) . '/js/chosen.jquery.min.js');
        return $this->m->render('chosen', ['settings' => $settings, 'value' => $value]);
    }

    function vc()
    {
        vc_add_shortcode_param('chosen', [$this, 'chosenParamType']);
        vc_map([
            'name'     => 'WooCommerce Event List',
            'base'     => Meta::$key,
            'class'    => '',
            'category' => 'WooCommerce',
            'params'   => [
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
            ]
        ]);
    }
}