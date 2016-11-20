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
        $events       = Model::getEvents();
        $sorted       = $this->sortEvents($this->prepareEvents($events), $options['order']);
        $withCategory = array_values($this->selectEventsByCategories(explode(',', $options['categories']), $sorted));
        $filtered     = array_values($this->filterExpiredEvents($options['expired'], $withCategory));
        $categories   = array_unique(Utils::pluck($filtered, 'product_cat'));

        $assigns = [
            'categories' => $categories,
            'events'     => $filtered,
            'options'    => $options
        ];

        $this->view
            ->echo('eventlist', $assigns)
            ->enqueueStyle('event-list');
    }

    function prepareEvents($events)
    {
        return array_map(function ($event) {
            $meta                          = Model::getMeta($event->ID);
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

    function filterExpiredEvents($filter, $events)
    {
        return array_filter($events, function ($event) use ($filter) {
            $isExpired = $this->isExpired($event);

            switch ($filter) {
                case 'Show':
                    return true;
                case 'Only':
                    return $isExpired;
                case 'Hide':
                    return !$isExpired;
            }
        });
    }

    function isExpired($event)
    {
        return time() > strtotime($event['end-date']);
    }

    function selectEventsByCategories($categories, $events)
    {
        return array_filter($events, function ($event) use ($categories) {
            return in_array($event['product_cat'], $categories);
        });
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