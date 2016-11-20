<?php namespace WooEvents;

use WooEvents\Model;
use WooEvents\View;

class CalendarWidget extends \WP_Widget
{
    public $title = 'Woo Events Calendar';
    public $key = 'woo-events-calendar';

    function __construct()
    {
        global $view;
        $this->view = $view;
        parent::__construct(
            $this->key,
            $this->title,
            $this->title
        );
    }

    public function enqueue()
    {
        $this->view
            ->enqueueStyle('widget/tooltipster')
            ->enqueueStyle('widget/themes/tooltipster-shadow')
            ->enqueueStyle('custom')
            ->enqueueScript('widget/jquery.tooltipster.min');
    }

    public function createMonthRange($previousMonths, $nextMonths)
    {
        $nextMonths    = $nextMonths + 1;
        $rangeStart    = new \DateTime("now -$previousMonths months");
        $rangeEnd      = new \DateTime("now +$nextMonths months");
        $monthInterval = new \DateInterval('P1M');
        $range         = new \DatePeriod($rangeStart, $monthInterval, $rangeEnd);

        return $range;
    }

    public function monthRangeToArray($monthRange)
    {
        $result = [];

        foreach ($monthRange as $month) {
            array_push($result, [
                'year'      => $month->format('Y'),
                'month'     => $month->format('m'),
                'localised' => date_i18n('F', $month->getTimestamp()),
                'days'      => array_chunk(range(1, cal_days_in_month(CAL_GREGORIAN, $month->format('m'), $month->format('Y'))), 7)
            ]);
        }

        return $result;
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget($args, $instance)
    {
//        $title = apply_filters('widget_title', $instance['title']);
//
//        // before and after widget arguments are defined by themes
//        if (!empty($title))
//            echo $args['before_title'] . $title . $args['after_title'];
//
////        $output = $this->cww_get_calendar(true, false, $nextmonths, $previousmonths, $cats);

        /**
         * The range of months that the calendar will cover.
         */
        $monthRange      = $this->createMonthRange($instance['previousmonths'], $instance['nextmonths']);
        $monthRangeArray = $this->monthRangeToArray($monthRange);

        $assigns = [
            'months' => $monthRangeArray
        ];

        $this->view->echo('calendar', $assigns);
        $this->enqueue();
    }

    function mergeCategories($all, $selectedIds)
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
            'categories'           => $this->mergeCategories(Model::getCategories(), $categories)
        ];

        $this->view->echo('calendar_admin', $assigns);
    }

    public function update($newInstance, $oldInstance)
    {
        return array_merge($newInstance, $oldInstance);
    }

    public function cww_get_calendar($initial = true, $echo = true, $nextmonths = CCW_MONTHS_DEFAULT, $previousmonths = CCW_MONTHS_DEFAULT, $cats = [0 => 'all'])
    {

        global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

        //echo "<pre>";print_r($posts);echo "</pre>";

        $cache = [];
        $key   = md5($m . $monthnum . $year);


        // Quick check. If we have no posts at all, abort!
        if (!$posts) {
            $gotsome = $wpdb->get_var("SELECT 1 as test FROM $wpdb->posts WHERE post_type = 'product' AND post_status = 'publish' LIMIT 1");
            if (!$gotsome) {
                $cache[$key] = '';
                wp_cache_set('get_cww_calendar', $cache, 'cww_calendar');
                return;
            }
        }

        $monthnum = date('m');
        $monthnum = $monthnum * 1;
        $year     = date('Y');

        $loop_start = $monthnum - $previousmonths;
        $loop_end   = $monthnum + $nextmonths;

        $current_year       = $year;
        $year_changed       = false;
        $year_changed_twice = false;

        for ($i = $loop_start; $i <= $loop_end; $i++) {

            $show_prev     = false;
            $show_next     = false;
            $current_month = $i;

            if ($current_month < 1) {
                $current_month = $current_month + 12;
                if (!$year_changed) {
                    $current_year = $current_year - 1;
                    $year_changed = true;
                }
            }

            if ($current_month == 1) {
                if ($year_changed) {
                    $current_year = $current_year + 1;
                    $year_changed = false;
                }
            }

            if ($current_month > 12) {
                $current_month = $current_month - 12;
                if (!$year_changed) {
                    $current_year = $current_year + 1;
                    $year_changed = true;
                }
            }

            if ($current_month > 12) {
                $current_month = $current_month - 12;
                if (!$year_changed_twice) {
                    $current_year       = $current_year + 1;
                    $year_changed_twice = true;
                }
            }

            if ($i > $loop_start) {
                $show_prev = true;
            }

            if ($i < $loop_end) {
                $show_next = true;
            }

            $calendar_output .= '<div id="ccw_' . $monthnum . '_' . $year . '" class="ccw_month" style="display:none;">' . $this->cww_get_month_html($m, $monthnum, $year, $wp_locale, $posts, $show_next, $show_prev, $cats) . '</div>';

            if ($monthnum == $current_month && $year == $current_year) {

                $calendar_output .= '<div id="ccw_' . $current_month . '_' . $current_year . '" class="ccw_month current" style="display:block">' . $this->cww_get_month_html($m, $current_month, $current_year, $wp_locale, $posts, $show_next, $show_prev, $cats) . '</div>';

            } else {

                $calendar_ouestput .= '<div id="ccw_' . $current_month . '_' . $current_year . '" class="ccw_month" style="display:none;">' . $this->cww_get_month_html($m, $current_month, $current_year, $wp_locale, $posts, $show_next, $show_prev, $cats) . '</div>';

            }
        }

        if (is_single(get_the_ID()) || is_archive()) {
            $post            = get_post(get_the_ID());
            $post_date       = strtotime(get_post_meta(get_the_ID(), 'event_datetime', true));
            $post_date_month = date('m', $post_date) * 1;
            $post_date_year  = date('Y', $post_date);
            $calendar_output .= '<script>jQuery("#ccw_' . $monthnum . '_' . $year . '").hide();jQuery("#ccw_' . $post_date_month . '_' . $post_date_year . '").show();</script>';
        }

        $cache[$key] = $calendar_output;
        wp_cache_set('get_cww_calendar', $cache, 'cww_calendar');

        if ($echo) {
            echo apply_filters('get_cww_calendar', $calendar_output);
        } else {
            return apply_filters('get_cww_calendar', $calendar_output);
        }

    }

    private function cww_get_month_html($m, $monthnum, $year, $wp_locale, $posts, $show_next = true, $show_prev = true, $cats)
    {

        global $wpdb;

        if (isset($_GET['w']))
            $w = '' . intval($_GET['w']);

        // week_begins = 0 stands for Sunday
        $week_begins = intval(get_option('start_of_week'));

        // Let's figure out when we are
        if (is_numeric($monthnum) && $monthnum >= 0) {
            $thismonth = '' . zeroise(intval($monthnum), 2);
            $thisyear  = '' . intval($year);
        } elseif (!empty($w)) {
            // We need to get the month from MySQL
            $thisyear  = '' . intval(substr($m, 0, 4));
            $d         = (($w - 1) * 7) + 6; //it seems MySQL's weeks disagree with PHP's
            $thismonth = $wpdb->get_var("SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')");
        } elseif (!empty($m)) {
            $thisyear = '' . intval(substr($m, 0, 4));
            if (strlen($m) < 6)
                $thismonth = '01';
            else
                $thismonth = '' . zeroise(intval(substr($m, 4, 2)), 2);
        } else {
            $thisyear  = gmdate('Y', current_time('timestamp'));
            $thismonth = gmdate('m', current_time('timestamp'));
        }

        $unixmonth = mktime(0, 0, 0, $thismonth, 1, $thisyear);
        $last_day  = date('t', $unixmonth);

        /* translators: Calendar caption: 1: month name, 2: 4-digit year */
        $calendar_caption = _x('%1$s %2$s', 'calendar caption');
        $calendar_output  = '<table id="wp-calendar">
		<caption>' . sprintf($calendar_caption, $wp_locale->get_month($thismonth), date('Y', $unixmonth)) . '</caption>
		<thead>
		<tr>';

        $myweek = [];

        for ($wdcount = 0; $wdcount <= 6; $wdcount++) {
            $myweek[] = $wp_locale->get_weekday(($wdcount + $week_begins) % 7);
        }

        foreach ($myweek as $wd) {
            $day_name = (true == $initial) ? $wp_locale->get_weekday_initial($wd) : $wp_locale->get_weekday_abbrev($wd);
            $wd       = esc_attr($wd);
            $calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
        }

        $calendar_output .= '
		</tr>
		</thead>
	
		<tfoot>
		<tr>';

        if ($show_prev) {
            $last_month = $thismonth - 1;
            $last_year  = $thisyear;
            if ($last_month < 1) {
                $last_month = 12;
                $last_year  = $thisyear - 1;
            }
            $calendar_output .= "\n\t\t" . '<td colspan="3" id="prev"><a href="' . get_month_link($last_year, $last_month) . '" title="' . esc_attr(sprintf(__('View posts for %1$s %2$s'), $wp_locale->get_month($last_month), date('Y', mktime(0, 0, 0, $last_month, 1, $last_year)))) . '" onclick="javascript:jQuery(\'.ccw_month\').hide();jQuery(\'#ccw_' . $last_month . '_' . $last_year . '\').show(); return false;">&laquo; ' . $wp_locale->get_month_abbrev($wp_locale->get_month($last_month)) . '</a></td>';
        } else {
            $calendar_output .= "\n\t\t" . '<td colspan="3" id="prev" class="pad">&nbsp;</td>';
        }

        $calendar_output .= "\n\t\t" . '<td class="pad">&nbsp;</td>';

        if ($show_next) {

            $last_month = $thismonth + 1;
            $last_year  = $thisyear;
            if ($last_month > 12) {
                $last_month = 1;
                $last_year  = $thisyear + 1;
            }

            $calendar_output .= "\n\t\t" . '<td colspan="3" id="next"><a href="' . get_month_link($last_year, $last_month) . '" title="' . esc_attr(sprintf(__('View posts for %1$s %2$s'), $wp_locale->get_month($last_month), date('Y', mktime(0, 0, 0, $last_month, 1, $last_year)))) . '" onclick="javascript:jQuery(\'.ccw_month\').hide();jQuery(\'#ccw_' . $last_month . '_' . $last_year . '\').show(); return false;">' . $wp_locale->get_month_abbrev($wp_locale->get_month($last_month)) . ' &raquo;</a></td>';
        } else {
            $calendar_output .= "\n\t\t" . '<td colspan="3" id="next" class="pad">&nbsp;</td>';
        }

        $calendar_output .= '
		</tr>
		</tfoot>
	
		<tbody>
		<tr>';

        // Get days with posts


        if (in_array("all", $cats)) {
            $sql = "SELECT DISTINCT DAYOFMONTH($wpdb->postmeta.meta_value) 
				FROM $wpdb->postmeta 
				LEFT JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id
				WHERE 
				$wpdb->postmeta.meta_key LIKE 'event_datetime'
				AND $wpdb->postmeta.meta_value >= '{$thisyear}-{$thismonth}-01 00:00:00'
				AND $wpdb->postmeta.meta_value <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59'
				AND post_type = 'product' AND post_status = 'publish'";
        } else {
            $sql = "SELECT DISTINCT DAYOFMONTH($wpdb->postmeta.meta_value) 
				FROM $wpdb->postmeta 
				LEFT JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id
				WHERE 
				$wpdb->postmeta.meta_key LIKE 'event_datetime'
				AND $wpdb->posts.ID IN ( SELECT tr.object_id FROM " . $wpdb->prefix . "term_relationships AS tr INNER JOIN " . $wpdb->prefix . "term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy = 'product_cat' AND tt.term_id IN (" . implode(",", $cats) . ") )
				AND $wpdb->postmeta.meta_value >= '{$thisyear}-{$thismonth}-01 00:00:00'
				AND $wpdb->postmeta.meta_value <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59'
				AND post_type = 'product' AND post_status = 'publish'";
        }


        if ($thismonth == 10) {
            //echo $sql;exit();
        }
        $dayswithposts = $wpdb->get_results($sql, ARRAY_N);

        //echo "<pre>"; print_r($sql) ;echo "</pre>";
        //echo "<pre>"; print_r($dayswithposts) ;echo "</pre>";


        if ($dayswithposts) {
            foreach ((array)$dayswithposts as $daywith) {
                $daywithpost[] = $daywith[0];
            }
        } else {
            $daywithpost = [];
        }


        $ak_title_separator = "<br>";

        $ak_titles_for_day = [];

        if (in_array("all", $cats)) {
            $ak_post_titles = $wpdb->get_results("SELECT $wpdb->posts.ID, $wpdb->posts.post_title, DAYOFMONTH($wpdb->postmeta.meta_value) as dom
				FROM $wpdb->postmeta 
				LEFT JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id
				WHERE 
				$wpdb->postmeta.meta_key LIKE 'event_datetime'
				AND $wpdb->postmeta.meta_value >= '{$thisyear}-{$thismonth}-01 00:00:00'
				AND $wpdb->postmeta.meta_value <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59'
				AND post_type = 'product' AND post_status = 'publish'"
            );
        } else {

            $ak_post_titles = $wpdb->get_results("SELECT $wpdb->posts.ID, $wpdb->posts.post_title, DAYOFMONTH($wpdb->postmeta.meta_value) as dom
				FROM $wpdb->postmeta 
				LEFT JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id
				WHERE 
				$wpdb->postmeta.meta_key LIKE 'event_datetime'
				AND $wpdb->posts.ID IN ( SELECT tr.object_id FROM " . $wpdb->prefix . "term_relationships AS tr INNER JOIN " . $wpdb->prefix . "term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy = 'product_cat' AND tt.term_id IN (" . implode(",", $cats) . ") )
				AND $wpdb->postmeta.meta_value >= '{$thisyear}-{$thismonth}-01 00:00:00'
				AND $wpdb->postmeta.meta_value <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59'
				AND post_type = 'product' AND post_status = 'publish'"
            );
        }


        $current_day = date('d');

        //echo "<pre>";print_r($ak_post_titles);echo "</pre>";


        if ($ak_post_titles) {
            foreach ((array)$ak_post_titles as $ak_post_title) {


                $event_data = get_post_meta($ak_post_title->ID, 'event_datetime', true);

                //print_r($event_data);

                if (is_array($event_data)) {
                    $date_arr = explode("T", $event_data['start_time']);
                    $time_arr = explode("+", $date_arr[1]);

                    $date = date('H:i', strtotime($date_arr[0] . " " . $time_arr[0]));
                } else {

                    if ($event_data != '') {
                        $date_arr = explode(" ", $event_data);
                        $time_arr = explode(" ", $date_arr[1]);

                        $date = date('H:i', strtotime($date_arr[0] . " " . $time_arr[0]));

                        //echo "<pre>";print_r($time_arr);echo "</pre>";

                    }
                    //$date = "";
                }
                /** This filter is documented in wp-includes/post-template.php */
                $post_title = '<a href="' . get_permalink($ak_post_title->ID) . '"><b style="color:#fd3d3d;margin-right:5px;">' . $date . '</b>' . $ak_post_title->post_title . '</a>';//esc_attr( apply_filters( 'the_title', $ak_post_title->post_title, $ak_post_title->ID ) );

                if (empty($ak_titles_for_day['day_' . $ak_post_title->dom]))
                    $ak_titles_for_day['day_' . $ak_post_title->dom] = '';
                if (empty($ak_titles_for_day["$ak_post_title->dom"])) // first one
                    $ak_titles_for_day["$ak_post_title->dom"] = $post_title;
                else
                    $ak_titles_for_day["$ak_post_title->dom"] .= $ak_title_separator . $post_title;
            }
        }

        // See how much we should pad in the beginning
        $pad = calendar_week_mod(date('w', $unixmonth) - $week_begins);


        if (0 != $pad)
            $calendar_output .= "\n\t\t" . '<td colspan="' . esc_attr($pad) . '" class="pad">&nbsp;</td>';

        $daysinmonth = intval(date('t', $unixmonth));

        for ($day = 1; $day <= $daysinmonth; ++$day) {
            if (isset($newrow) && $newrow)
                $calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
            $newrow = false;

            if ($day == gmdate('j', current_time('timestamp')) && $thismonth == gmdate('m', current_time('timestamp')) && $thisyear == gmdate('Y', current_time('timestamp')))
                $calendar_output .= '<td id="today">';
            else
                $calendar_output .= '<td>';

            if (in_array($day, $daywithpost)) // any posts today?
                //$calendar_output .= '<a class="tooltip_interact" href="' . get_day_link( $thisyear, $thismonth, $day ) . '" title="' . esc_attr( $ak_titles_for_day[ $day ] ) . "\">$day</a>";
                $calendar_output .= '<a style="cursor:pointer" class="tooltip_interact" title="' . esc_attr($ak_titles_for_day[$day]) . "\">$day</a>";
            else
                $calendar_output .= $day;
            $calendar_output .= '</td>';

            if (6 == calendar_week_mod(date('w', mktime(0, 0, 0, $thismonth, $day, $thisyear)) - $week_begins))
                $newrow = true;
        }

        $pad = 7 - calendar_week_mod(date('w', mktime(0, 0, 0, $thismonth, $day, $thisyear)) - $week_begins);
        if ($pad != 0 && $pad != 7)
            $calendar_output .= "\n\t\t" . '<td class="pad" colspan="' . esc_attr($pad) . '">&nbsp;</td>';

        $calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";
        return $calendar_output;
    }

}


add_action('widgets_init', function () {
    register_widget('WooEvents\CalendarWidget');
});
?>