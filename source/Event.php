<?php namespace WooEvents;

use Utils\Utils;
use Utils\WooUtils;
use Utils\Date;
use Utils\MetaPersist;


class Event
{
    use MetaPersist;

    static $key = 'woo-events';
    static $name = 'WooEvents';
    public $id;
    public $externalLink;
    public $hasEnd;
    public $hideButton;
    public $enable;
    public $subTitle;
    public $expiredCategoryName = 'Expired';
    public $cartButtonText;
    public $startDate;
    public $endDate;
    public $fullDate;

    function __construct($postId)
    {
        $this->restore($postId);

        /**
         * Non-declared (computed, denormalized) properties.
         * Won't be persisted.
         */
        $product               = wc_get_product($postId);
        $this->id              = $postId;
        $this->title           = $product->post->post_title;
        $this->startDateOnly   = Date::formatDate($this->startDate);
        $this->startDatePretty = WooUtils::formatDateTimeWoocommerce($this->startDate, !$this->hasEnd);
        $this->endDatePretty   = WooUtils::formatDateTimeWoocommerce($this->endDate, !$this->hasEnd);
        $this->fullDate        = $this->fullDate();
        $this->price           = $product->get_price_html();
        $this->image           = wp_get_attachment_image_src(get_post_thumbnail_id($postId), 'medium')[0];
        $this->featured        = WooUtils::featuredText($product);
        $this->excerpt         = substr($product->post->post_excerpt, 0, 100) . "...";
        $this->categories      = $this->getCategories($postId);
        $this->category        = $this->categories[0];
        $this->permalink       = get_permalink($postId);
        $this->addToCartUrl    = $product->add_to_cart_url();
    }

    function fullDate()
    {
        if ($this->hasEnd) {
            return "$this->startDatePretty - $this->endDatePretty";
        } else {
            return $this->startDatePretty;
        }
    }

    function __destruct()
    {
        $this->updateExpirationStatus();
        $this->persist();
        WooUtils::flattenMeta($this->id, self::$key);
        if ($this->enable) $this->updatePublicationDate();
    }

    /**
     * @return bool Whether the event is expired
     */
    function isExpired()
    {
        return time() > strtotime("$this->endDate +8 hours");
    }

    private function updatePublicationDate()
    {
        $post              = get_post($this->id, ARRAY_A);
        $post['post_date'] = $this->startDate;
        wp_update_post($post);
    }

    private function getCategories($postId)
    {
        $checked     = Utils::array_pluck(wp_get_post_terms($postId, 'product_cat'), 'term_id');
        $ancestorIds = Utils::array_flatmap('Utils\WooUtils::categoryLegacy', $checked);

        if (count($ancestorIds) > 0) {
            $ancestors = get_categories([
                'taxonomy'     => 'product_cat',
                'hierarchical' => true,
                'hide_empty'   => false, 'include' => $ancestorIds
            ]);
            $names     = Utils::array_pluck($ancestors, 'cat_name');
            return array_unique($names);
        } else {
            return [];
        }
    }


    private function updateExpirationStatus()
    {
        /**
         * Create the term if it doesn't exist.
         */
        $expiredCategory = get_term_by('name', $this->expiredCategoryName, 'product_cat', ARRAY_A)['term_id'] ?:
            wp_insert_term($this->expiredCategoryName, 'product_cat')['term_id'];

        $categories = wp_get_object_terms($this->id, 'product_cat', ['fields' => 'ids']);

        /**
         * If the event is expired, remove all other categories and add the
         * expired category. Else, remove the expired category
         */
        if ($this->isExpired())
            $categories = [$expiredCategory];
        else
            $categories = Utils::array_exclude_value($categories, $expiredCategory);

        wp_set_post_terms($this->id, $categories, 'product_cat');
    }

    /**
     * @param $categories Array[String] of category names
     * @param $events     Event[]
     * @return Event[]
     */
    static function selectByCategories($categories, $events)
    {
        return array_values(array_filter($events, function ($event) use ($categories) {
            return count(array_intersect($event->categories, $categories)) > 0;
        }));
    }

    /**
     * @param $filter 'Show' or 'Only' or 'Hide'
     * @param $events Array of events with end-date
     * @return Filtered Array of events
     */
    static function filterExpired($filter, $events)
    {
        return array_values(array_filter($events, function ($event) use ($filter) {
            $isExpired = self::isExpired($event);

            switch ($filter) {
                case 'Only':
                    return $isExpired;
                case 'Hide':
                    return !$isExpired;
                case 'Show':
                    return true;
                default:
                    return true;
            }
        }));
    }

    /**
     * @param $order  'Ascending' or 'Descending'
     * @param $events Event[]
     * @return Sorted Array of events
     */
    static function sort($order, $events)
    {
        $orderModifier = $order == 'Ascending' ? 1 : -1;

        usort($events, function ($a, $b) use ($orderModifier) {
            return (strtotime($a->startDate) - strtotime($b->startDate)) * $orderModifier;
        });

        return $events;
    }


    /**
     * @param null $only
     * @return Event[]
     */
    static function all($only = null)
    {
        $posts = get_posts([
            'post_type'        => 'product',
            'meta_key'         => self::$key,
            'numberposts'      => -1,
            'suppress_filters' => true,
            'include'          => $only
        ]);

        return array_map(function ($post) {
            return new Event($post->ID);
        }, $posts);
    }

    static function get($postId)
    {
        return new self($postId);
    }

    function assignDynamicDefaults()
    {
        $this->startDate      = Date::formatDateTime();
        $this->endDate        = Date::formatDateTime();
        $this->cartButtonText = __('View Event', 'woo-events');
        $this->fullDate       = $this->fullDate();
    }
}
