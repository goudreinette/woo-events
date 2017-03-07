<?php namespace WooEvents;

use Utils\PluginContext;
use Utils\Utils;
use Utils\View;
use Utils\WooUtils;

class Admin
{
    /**
     * @var View;
     */
    public $view;

    /**
     * @var PluginContext
     */
    public $context;

    function __construct()
    {
        add_filter('woocommerce_product_data_tabs', [$this, 'registerTab']);
        add_filter('woocommerce_product_data_panels', [$this, 'render']);
        add_action('save_post', [$this, 'handleSave']);
    }

    function registerTab($tabs)
    {
        $tabs[Event::$key] = [
            'label'    => Event::$name,
            'priority' => 50,
            'target'   => Event::$key
        ];

        return $tabs;
    }

    function render()
    {
        global $post;

        $event                           = new Event($post->ID);
        $assigns                         = (array)$event;
        $assigns['key']                  = Event::$key;
        $assigns['enable']               = $this->checked($assigns['enable']);
        $assigns['hasEnd']               = $this->checked($assigns['hasEnd']);
        $assigns['hideButton']           = $this->checked($assigns['hideButton']);
        $assigns['notExpiredCategories'] = Utils::array_exclude_value(WooUtils::getProductCategoryNames(true), $event->expiredCategoryName);

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

        // If this isn't a post update, abort
        if (empty($_POST) || !isset($_POST[Event::$key])) return;

        $event              = new Event($productId);
        $formData           = $_POST[Event::$key];
        $formData['enable'] = !!$formData['enable'];

        foreach ($formData as $key => $value)
            $event->$key = $value;
    }
}