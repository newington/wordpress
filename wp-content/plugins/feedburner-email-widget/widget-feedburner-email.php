<?php
/*
  Plugin Name: Feedburner Email Widget
  Version: 1.1.5
  Plugin URI: http://wyrihaximus.net/projects/wordpress/feedburner-email-widget/
  Description: Allows you to add a Feedburner Email Subscription widget to one of your sidebars.
  Author: WyriHaximus
  Author URI: http://wyrihaximus.net/
 */ 

class FeedburnerEmailWidget extends WP_Widget {

    function FeedburnerEmailWidget() {
        $widget_ops = array(
            'classname' => 'FeedburnerEmailWidget',
            'description' => 'Allows you to add a Feedburner Email Subscription widget to one of your sidebars.'
        );
        $this->WP_Widget('FeedburnerEmailWidget', 'Feedburner Email Widget', $widget_ops);
    }

    function form($instance) {
        $instance = wp_parse_args((array) $instance, array(
                'title' => '',
                'uri' => '',
                'above_email' => '',
                'below_email' => '',
                'email_text_input' => '',
                'subscribe_btn' => '',
                'show_link' => '',
                'form_id' => '',
                'css_style_code' => '',
                'analytics_cat' => '',
                'analytics_act' => '',
                'analytics_lab' => '',
                'analytics_val' => '',
            )
        );
        $title = esc_attr($instance['title']);
        $uri = esc_attr($instance['uri']);
        $above_email = esc_attr($instance['above_email']);
        $below_email = esc_attr($instance['below_email']);
        $email_text_input = esc_attr($instance['email_text_input']);
        $subscribe_btn = esc_attr($instance['subscribe_btn']);
        $show_link = esc_attr($instance['show_link']);
        $form_id = esc_attr($instance['form_id']);
        $css_style_code = esc_attr($instance['css_style_code']);
        $analytics_cat = esc_attr($instance['analytics_cat']);
        $analytics_act = esc_attr($instance['analytics_act']);
        $analytics_lab = esc_attr($instance['analytics_lab']);
        $analytics_val = esc_attr($instance['analytics_val']);
?>
        <a id="<?php echo $this->get_field_id('title'); ?>_div_a">-</a> Basic Options<br />
        <div id="<?php echo $this->get_field_id('title'); ?>_div" style="display: block;">
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('uri'); ?>"><?php _e('Feedburner feed URL:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('uri'); ?>" name="<?php echo $this->get_field_name('uri'); ?>" type="text" value="<?php echo $uri; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('above_email'); ?>"><?php _e('Above input text:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('above_email'); ?>" name="<?php echo $this->get_field_name('above_email'); ?>" type="text" value="<?php echo $above_email; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('below_email'); ?>"><?php _e('Below input text:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('below_email'); ?>" name="<?php echo $this->get_field_name('below_email'); ?>" type="text" value="<?php echo $below_email; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('email_text_input'); ?>"><?php _e('Input placeholder text:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('email_text_input'); ?>" name="<?php echo $this->get_field_name('email_text_input'); ?>" type="text" value="<?php echo $email_text_input; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('subscribe_btn'); ?>"><?php _e('Submit button caption:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('subscribe_btn'); ?>" name="<?php echo $this->get_field_name('subscribe_btn'); ?>" type="text" value="<?php echo $subscribe_btn; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('show_link'); ?>"><?php _e('Show feedburner link:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('show_link'); ?>" name="<?php echo $this->get_field_name('show_link'); ?>" type="checkbox"<?php echo (($show_link) ? ' checked' : ''); ?> /></label></p>
        </div>
        <a id="<?php echo $this->get_field_id('form_id'); ?>_div_a">+</a> Styling Options<br />
        <div id="<?php echo $this->get_field_id('form_id'); ?>_div" style="display: none;">
            <p><label for="<?php echo $this->get_field_id('form_id'); ?>"><?php _e('Form CSS ID:'); ?> (<a href="http://wiki.wyrihaximus.net/wiki/Wordpress_Feedburner_Email_Widget_Styling" target="_blank">?</a>) <input class="widefat" id="<?php echo $this->get_field_id('form_id'); ?>" name="<?php echo $this->get_field_name('form_id'); ?>" type="text" value="<?php echo $form_id; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('css_style_code'); ?>"><?php _e('CSS Styling:'); ?> (<a href="http://wiki.wyrihaximus.net/wiki/Wordpress_Feedburner_Email_Widget_Styling" target="_blank">?</a>) <textarea style="height:250px;" class="widefat" id="<?php echo $this->get_field_id('css_style_code'); ?>" name="<?php echo $this->get_field_name('css_style_code'); ?>"><?php echo $css_style_code; ?></textarea></label></p>
        </div>
        <a id="<?php echo $this->get_field_id('analytics_cat'); ?>_div_a">+</a> Analytic Options
        <div id="<?php echo $this->get_field_id('analytics_cat'); ?>_div" style="display: none;">
            <p><label for="<?php echo $this->get_field_id('analytics_cat'); ?>"><?php _e('Analytics Category:'); ?> (<a href="http://wiki.wyrihaximus.net/wiki/Wordpress_Feedburner_Email_Widget_Analytics" target="_blank">?</a>) <input class="widefat" id="<?php echo $this->get_field_id('analytics_cat'); ?>" name="<?php echo $this->get_field_name('analytics_cat'); ?>" type="text" value="<?php echo $analytics_cat; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('analytics_act'); ?>"><?php _e('Analytics Action:'); ?> (<a href="http://wiki.wyrihaximus.net/wiki/Wordpress_Feedburner_Email_Widget_Analytics" target="_blank">?</a>) <input class="widefat" id="<?php echo $this->get_field_id('analytics_act'); ?>" name="<?php echo $this->get_field_name('analytics_act'); ?>" type="text" value="<?php echo $analytics_act; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('analytics_lab'); ?>"><?php _e('Analytics Label:'); ?> (<a href="http://wiki.wyrihaximus.net/wiki/Wordpress_Feedburner_Email_Widget_Analytics" target="_blank">?</a>) <input class="widefat" id="<?php echo $this->get_field_id('analytics_lab'); ?>" name="<?php echo $this->get_field_name('analytics_lab'); ?>" type="text" value="<?php echo $analytics_lab; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('analytics_val'); ?>"><?php _e('Analytics Value:'); ?> (<a href="http://wiki.wyrihaximus.net/wiki/Wordpress_Feedburner_Email_Widget_Analytics" target="_blank">?</a>) <input class="widefat" id="<?php echo $this->get_field_id('analytics_val'); ?>" name="<?php echo $this->get_field_name('analytics_val'); ?>" type="text" value="<?php echo $analytics_val; ?>" /></label></p>
        </div>
        <div style="margin-top: 10px;">
            <a class="FlattrButton" style="display:none;" rev="flattr;button:compact;" href="http://wyrihaximus.net/projects/wordpress/feedburner-email-widget/"></a>
            <noscript>
                <a href="http://flattr.com/thing/283885/Wordpress-Feedburner-Email-Widget" target="_blank">
                    <img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" />
                </a>
            </noscript>
        </div>
        <script type="text/javascript">
            /*
             * For those wondering why I don't use jquery to toggle the different settings: I doesn't seem to work with the default functions
             * if anyway can provide me a working example I would love to hear about it but I'm not just gonne look hours and hours at
             * the problem if it can be solved like this ;).
             */
            function feedburner_email_widget_admin_toggle_visibility(id) {
                var e = document.getElementById(id);
                var e_a = document.getElementById(id + '_a');
                if(e.style.display == 'block') {
                    e.style.display = 'none';
                    e_a.innerHTML = '+';
                } else {
                    e.style.display = 'block';
                    e_a.innerHTML = '-';
                }
            }
            addLoadEvent(function() {
                jQuery('#<?php echo $this->get_field_id('title'); ?>_div_a').click(function() {
                    feedburner_email_widget_admin_toggle_visibility('<?php echo $this->get_field_id('title'); ?>_div');
                    return true;
                });
                jQuery('#<?php echo $this->get_field_id('form_id'); ?>_div_a').click(function() {
                    feedburner_email_widget_admin_toggle_visibility('<?php echo $this->get_field_id('form_id'); ?>_div');
                    return true;
                });
                jQuery('#<?php echo $this->get_field_id('analytics_cat'); ?>_div_a').click(function() {
                    feedburner_email_widget_admin_toggle_visibility('<?php echo $this->get_field_id('analytics_cat'); ?>_div');
                    return true;
                });
            });
        </script>
        <script type="text/javascript">
        /* <![CDATA[ */
            (function() {
                var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
                s.type = 'text/javascript';
                s.async = true;
                s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto';
                t.parentNode.insertBefore(s, t);
            })();
        /* ]]> */
        </script>
<?php
    }

    function update($new_instance, $old_instance) {
        return $new_instance;
    }

    function widget($args, $instance) {
        echo $this->generate($args, $instance);
    }

    function generate($args, $instance) {
        extract($args, EXTR_SKIP);
        $html = $before_widget;
        // Grab the settings from $instance and full them with default values if we can't find any
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        $uri = empty($instance['uri']) ? false : $instance['uri'];
        $above_email = empty($instance['above_email']) ? false : $instance['above_email'];
        $below_email = empty($instance['below_email']) ? false : $instance['below_email'];
        $subscribe_btn = empty($instance['subscribe_btn']) ? 'Subscribe' : $instance['subscribe_btn'];
        $email_text_input = empty($instance['email_text_input']) ? '' : $instance['email_text_input'];
        $show_link = (isset($instance['show_link']) && $instance['show_link']) ? true : false;
        $form_id = empty($instance['form_id']) ? 'feedburner_email_widget_sbef' : $instance['form_id'];
        $css_style_code = empty($instance['css_style_code']) ? false : $instance['css_style_code'];
        $analytics_cat = empty($instance['analytics_cat']) ? false : $instance['analytics_cat'];
        $analytics_act = empty($instance['analytics_act']) ? false : $instance['analytics_act'];
        $analytics_lab = empty($instance['analytics_lab']) ? false : $instance['analytics_lab'];
        $analytics_val = empty($instance['analytics_val']) ? false : $instance['analytics_val'];
        if ($uri) {
            // Cut out the part we need
            $uri = str_replace('http://feeds.feedburner.com/', '', $uri);
            if (!empty($title)) {
                if(!isset($before_title)) {
                    $before_title = '';
                }
                if(!isset($after_title)) {
                    $after_title = '';
                }
                $html .= $before_title . trim($title) . $after_title;
            }
            // Get Style if any
            if ($css_style_code) {
                $html .='<style type="text/css">' . trim($css_style_code) . '</style>';
            }
            // Putting onSubmit code together
            $onsubmit = array();
            // Default feedburner window
            $onsubmit[] = 'window.open(\'http://feedburner.google.com/fb/a/mailverify?uri=' . $uri . '\', \'popupwindow\', \'scrollbars=yes,width=550,height=520\');';
            // Google Analytics support
            if ($analytics_cat && $analytics_act) {
                $analytics_array = array();
                $analytics_array[] = '\'' . $analytics_cat . '\'';
                $analytics_array[] = '\'' . $analytics_act . '\'';
                if ($analytics_lab) {
                    $analytics_array[] = '\'' . $analytics_lab . '\'';
                }
                if ($analytics_val) {
                    $analytics_array[] = $analytics_val;
                }
                $onsubmit[] = 'if(!(typeof(pageTracker) == \'undefined\')){pageTracker._trackEvent(' . implode(',', $analytics_array) . ');}else{if(!(typeof(_gaq) == \'undefined\')){_gaq.push([' . implode(',', $analytics_array) . ']);}}';
            }
            $onsubmit[] = 'return true;';
            // Open Form
            $html .= '<form id="' . trim($form_id) . '" action="http://feedburner.google.com/fb/a/mailverify" method="post" onsubmit="' . implode('', $onsubmit) . '" target="popupwindow">';
            if ($above_email) {
                $html .= '<label>' . trim($above_email) . '</label>';
            }
            $html .= '<input id="' . trim($form_id) . '_email" name="email" type="text" ';
            if(!empty($email_text_input)) {
                $html .= 'value="' . htmlentities(trim($email_text_input)) . '" onclick="javascript:if(this.value==\'' . addslashes(htmlentities(trim($email_text_input))) . '\'){this.value= \'\';}" ';
            }
            $html .= '/>';
            // Hidden fields
            $html .= '<input type="hidden" value="' . $uri . '" name="uri"/>';
            $html .= '<input type="hidden" name="loc" value="en_US"/>';
            if ($below_email) {
                $html .= '<label>' . trim($below_email) . '</label>';
            }
            $html .= '<input id="' . trim($form_id) . '_submit" type="submit" value="' . htmlentities(trim($subscribe_btn)) . '" />';
            if ($show_link) {
                $html .= '<label>Delivered by <a href="http://feedburner.google.com" target="_blank">FeedBurner</a></label>';
            }
            $html .= '</form>';
        }
        $html .= $after_widget;
        // Send the widget to the browser
        return $html;
    }

}

// Tell WordPress about our widget
add_action('widgets_init', create_function('', 'return register_widget(\'FeedburnerEmailWidget\');'));

function feedburner_email_widget_shortcode_func($atts) {
	return FeedburnerEmailWidget::generate(array(), shortcode_atts(array(
            'title' => ' ',
            'uri' => false,
            'above_email' => false,
            'below_email' => false,
            'subscribe_btn' => 'Subscribe',
            'show_link' => false,
            'form_id' => 'feedburner_email_widget_sbef',
            'css_style_code' => false,
            'analytics_cat' => false,
            'analytics_act' => false,
            'analytics_lab' => false,
            'analytics_val' => false,
	), $atts));
}
add_shortcode('feedburner_email_widget', 'feedburner_email_widget_shortcode_func');