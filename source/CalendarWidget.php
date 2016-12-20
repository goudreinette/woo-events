<?php namespace WooEvents;

use Utils\Utils;
use Utils\Date;
use Utils\View;
use Utils\WooUtils;

class CalendarWidget extends \WP_Widget
{
    public $title = 'Woo Events Calendar';
    public $key = 'woo-events-calendar';

    function __construct()
    {
        global $view;
        $this->view = $view;
        parent::__construct($this->key, $this->title, $this->title);
    }

    public function enqueue()
    {
        $this->view
            ->enqueueStyle('calendar')
            ->enqueueScript('calendar');
    }

    public function widget($args, $instance)
    {
        /**
         * The range of months that the calendar will cover.
         */
        $categories = WooUtils::getProductCategoryNames(['include' => $instance['categories']]);
        $monthRange = Date::createMonthRange($instance['previousmonths'], $instance['nextmonths']);
        $all        = Event::all();
        $events     = Event::selectByCategories($categories, $all);

        $assigns = ['months' => $monthRange, 'events' => Utils::toArray($events)];

        $this->enqueue();
        $this->view->render('calendar', $assigns);
        wp_enqueue_style('ionicons', 'http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
    }

    function markSelectedCategories($all, $selectedIds)
    {
        return array_values(array_map(function ($category) use ($selectedIds) {
            return [
                'name'     => $category->cat_name,
                'id'       => $category->term_id,
                'selected' => in_array($category->term_id, $selectedIds)
            ];
        }, (array)$all));
    }

    public function form($instance)
    {
        $title          = $instance['title'] ?: $this->title;
        $nextmonths     = $instance['nextmonths'] ?: 3;
        $previousmonths = $instance['previousmonths'] ?: 3;
        $categories     = $instance['categories'] ?: [];

        $assigns = [
            'title_name'           => $this->get_field_name('title'),
            'title_id'             => $this->get_field_id('title'),
            'title_value'          => esc_attr($title),
            'nextmonths_name'      => $this->get_field_name('nextmonths'),
            'nextmonths_id'        => $this->get_field_id('nextmonths'),
            'nextmonths_value'     => $nextmonths,
            'previousmonths_name'  => $this->get_field_name('previousmonths'),
            'previousmonths_id'    => $this->get_field_id('previousmonths'),
            'previousmonths_value' => $previousmonths,
            'categories_name'      => $this->get_field_name('categories'),
            'categories_id'        => $this->get_field_id('categories'),
            'categories'           => $this->markSelectedCategories(WooUtils::getUsedProductCategories(), $categories)
        ];

        $this->view->render('calendar_admin', $assigns);
    }

    public function update($newInstance, $oldInstance)
    {
        return array_merge($oldInstance, $newInstance);
    }
}


add_action('widgets_init', function () {
    register_widget('WooEvents\CalendarWidget');
});
?>