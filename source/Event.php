<?php namespace WooEvents;

use Utils\Utils;
use Utils\WooUtils;
use Utils\Date;

class Event
{
    public $key = 'woo-events';

    function __construct($postId)
    {
        $meta                  = $this->getMeta($postId);
        $product               = wc_get_product($postId);
        $this->postId          = $postId;
        $this->enable          = $meta['enable'];
        $this->startDate       = $meta['startDate'];
        $this->endDate         = $meta['endDate'];
        $this->startDatePretty = WooUtils::formatDateTimeWoocommerce($meta['startDate']);
        $this->endDatePretty   = WooUtils::formatDateTimeWoocommerce($meta['endDate']);
        $this->price           = $product->get_price_html();
        $this->image           = wp_get_attachment_image_src(get_post_thumbnail_id($postId), 'medium')[0];
        $this->featured        = WooUtils::featuredText($product);
        $this->excerpt         = substr($product->post->post_excerpt, 0, 100) . "...";
        $this->categories      = $this->getCategories($postId);
        $this->category        = $this->categories[0];
        $this->permalink       = get_permalink($postId);
        $this->addToCartUrl    = $product->add_to_cart_url();
    }

    function __destruct()
    {
        $this->updateExpirationStatus();
        $this->updateMeta();
        if ($this->enable) $this->updatePublicationDate();
    }

    /**
     * @return bool Whether the event is expired
     */
    function isExpired()
    {
        return time() > strtotime("$this->endDate +12 hours");
    }

    private function getMeta($postId)
    {
        return array_merge(get_post_meta($postId, $this->key, true), [
            'key'            => $this->key,
            'enable'         => '',
            'hasEnd'         => '',
            'startDate'      => Date::formatDate(),
            'endDate'        => Date::formatDate(),
            'externalLink'   => '',
            'cartButtonText' => __('View Event', 'woo-events'),
        ]);
    }

    private function updateMeta()
    {
        $array = (array)$this;
        update_post_meta($this->postId, $this->key, $array);
    }

    private function updatePublicationDate()
    {
        $post              = get_post($this->postId, ARRAY_A);
        $post['post_date'] = $this->startDate;
        wp_update_post($post);
    }

    private function getCategories($postId)
    {
        $checked     = Utils::array_pluck(wp_get_post_terms($postId, 'product_cat'), 'term_id');
        $ancestorIds = Utils::array_flatmap('Utils\WooUtils::categoryLegacy', $checked);

        if (count($ancestorIds) > 0) {
            $ancestors = Meta::getCategories(['include' => $ancestorIds]);
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
        $expiredCategory = get_term_by('name', 'Expired', 'product_cat', ARRAY_A)['term_id'];
        if (!$expiredCategory) {
            $expiredCategory = wp_insert_term('Expired', 'product_cat')['term_id'];
        }

        $categories = wp_get_object_terms($this->postId, 'product_cat', ['fields' => 'ids']);

        /**
         * If the event is expired, remove all other categories and add the
         * 'Expired' category.
         */
        if ($this->isExpired())
            $categories = [$expiredCategory];
        else
            $categories = array_diff($categories, [$expiredCategory]);

        wp_set_post_terms($this->postId, $categories, 'product_cat');
    }
}
