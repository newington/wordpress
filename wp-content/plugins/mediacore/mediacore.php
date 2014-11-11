<?php
/**
 * Plugin Name: MediaCore
 * Plugin URI: http://mediacore.com
 * Description: MediaCore's plugin allows you to embed and upload videos from your wordpress site
 * Version: 2.7.2
 * Author: Derek Harnanansingh derek@mediacore.com
 * License: GPL2
 *
 * Copyright 2012  MediaCore  (email: info@mediacore.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
require_once('vendor/autoload.php');


/**
 * Add custom TinyMCE styles
 *
 * @return void
 */
function mcore_tinymce_styles() {
	$file_path = plugins_url('styles/mcore_admin_tinymce.css' , __FILE__);
	add_editor_style($file_path);
}
add_action('after_setup_theme', 'mcore_tinymce_styles');


/**
 * Initialize the MediaCore TinyMCE Chooser
 *
 * @return void
 */
function mcore_chooser_init() {
	if ((is_super_admin() || current_user_can('edit_posts') || current_user_can('edit_pages')) &&
		get_user_option('rich_editing')) {
		add_filter('mce_external_plugins', 'mcore_chooser_js');
		add_filter('mce_buttons', 'mcore_chooser_button', 0);
		add_filter('tiny_mce_before_init', 'mcore_chooser_tinymce_init');
	}
}
add_action('init', 'mcore_chooser_init');


/**
 * Add the MediaCore plugin settings action to add
 * the settings to the admin menu
 *
 * @return void
 */
function mcore_chooser_init_options(){
	add_options_page('MediaCore Media Embed', 'MediaCore', 8, 'mediacore', 'mcore_options_page');
}
add_action('admin_menu', 'mcore_chooser_init_options');


/**
 * Increment the TinyMCE version number
 *
 * @param string $ver
 * @return string
 */
function mcore_chooser_refresh_mce($ver) {
	$ver += 1;
	return $ver;
}
add_filter('tiny_mce_version', 'mcore_chooser_refresh_mce');


/**
 * Get the MediaCore params on TinyMCE init
 *
 * @param array $settings
 * @return array
 */
function mcore_chooser_tinymce_settings($settings) {
	$url = get_option('mcore_url');
	$client = new \MediaCore\Http\Client($url);
	$settings['mcore_chooser_js_url'] = $client->getUrl('api', 'chooser.js');
	$query_str = $client->getQuery(array(
		'custom_plugin_info'=>mcore_get_plugin_version()
	));
	$settings['mcore_url'] = $client->getUrl('chooser') . '?' . $query_str;
	return $settings;
}
add_filter('tiny_mce_before_init','mcore_chooser_tinymce_settings');


/**
 * Get the plugin version
 * @return string
 */
function mcore_get_plugin_version() {
	$version = 'mediacore-wordpress-chooser-';
	$plugin_data = get_plugin_data( __FILE__ );
	if (array_key_exists('Version', $plugin_data)) {
		$version .= $plugin_data['Version'];
	}
	return $version;
}


/**
 * Implement the shortcode API; takes the shortcode attributes and turns them
 * into the correct iframe embed code. i.e.:
 * [mediacore
 *      public_url="http://demo.mediacore.tv/media/bctia-demoday-2012"
 *      thumb_url="http://demo.mediacore.tv/images/default/video-poster.png"
 *      title="BCTIA demoday"
 *      width="560px"
 *      height="315px"
 * ]
 *
 */
function mcore_shortcode_handler($atts) {
	extract(shortcode_atts(array(
		'public_url' => '',
		'thumb_url' => '',
		'title' => '',
		'width' => '',
		'height' => '',
	), $atts));

	$embedcode = "<iframe src=\"" . $public_url . "/embed_player?iframe=True\"";
	$embedcode .= " width=\"" . $width . "\"";
	$embedcode .= " height=\"" . $height . "\"";
	$embedcode .= " mozallowfullscreen=\"mozallowfullscreen\"";
	$embedcode .= " webkitallowfullscreen=\"webkitallowfullscreen\"";
	$embedcode .= " allowfullscreen=\"allowfullscreen\"";
	$embedcode .= " scrolling=\"no\"";
	$embedcode .= " frameborder=\"0\"";
	$embedcode .= "></iframe>";
	return $embedcode;
}
add_shortcode('mediacore', 'mcore_shortcode_handler');


/**
 * Add MediaCore Chooser button at the end of the
 * buttons list
 *
 * @param array $buttons
 * @return array
 */
function mcore_chooser_button($buttons) {
	array_push($buttons, 'separator', 'mediacore');
	return $buttons;
}


/**
 * Add the MediaCore Chooser plugin javascript
 *
 * @param array $plugin_array
 * @return array
 */
function mcore_chooser_js($plugin_array) {
	$plugin_array['mediacore'] = plugins_url('editor_plugin.js' , __FILE__);
	return $plugin_array;
}


/**
 * Initialize the TinyMCE iframe ruleset
 * Without this, the allowfullscreen attributes will be stripped by TinyMCE,
 * breaking HTML5 fullscreen in our player.
 *
 * @param array $options
 * @return array
 */
function mcore_chooser_tinymce_init($options) {
	$iframeRule = 'iframe[src|width|height|frameborder|allowfullscreen|mozallowfullscreen|webkitallowfullscreen]';
	if (isset($options['extended_valid_elements'])) {
		$options['extended_valid_elements'] .= ',' . $iframeRule;
	} else {
		$options['extended_valid_elements'] = $iframeRule;
	}
	return $options;
}


/**
 * Setup the MediaCore plugin settings page
 *
 * @return string
 */
function mcore_options_page(){
	$url = get_option('mcore_url');
	$hidden_field_name = 'mcore_submit_hidden';
	$settings_style_url =  plugins_url('styles/mcore_chooser_settings.css' , __FILE__);
	wp_enqueue_style('mcore_chooser_settings_style', $settings_style_url);
?>
	<div class="wrap">
		<div class="icon32" id="mcore-logo"></div>
		<h2>MediaCore</h2>
<?php
		$url = get_option('mcore_url');
		$uri = new \MediaCore\Uri($url);

		if (isset($_POST[$hidden_field_name], $_POST['mcore_url']) && $_POST[$hidden_field_name] == 'Y') {
			$url = $_POST['mcore_url'];
			$uri = new \MediaCore\Uri($url);
			if ($uri->isValid()) {
				$message_class = 'updated fade';
				$message_text = 'MediaCore URL updated to: ' . $url;
				update_option('mcore_url', $url);
			} else {
				$message_class = 'error';
				$message_text = 'Please verify that your url is correct.';
			}
?>
		<div id="message" class="<?php echo $message_class; ?>">
			<p>
				<strong><?php echo $message_text; ?></strong>
			</p>
		</div>
<?php
		}
?>
		<form id="mcore-settings" name="att_img_options" method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
			<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
			<p>This plugin allows you to embed media from your MediaCore site into Wordpress pages and posts.</p>
			<p>
				<ol>
					<li>Enter your MediaCore domain in the field below <strong>(i.e. http://demo.mediacore.tv)</strong></li>
					<li>Add or Edit a Page or Post.</li>
					<li>Click the MediaCore icon in the rich text editor to view your MediaCore library.</li>
				</ol>
			</p>
			<p>
				<h2>MediaCore URL:</h2>
				<em><strong>*Note:</strong> If your wordpress site is being served over SSL (https://) then your MediaCore URL must also support SSL.</em>
			</p>
			<p>
				<input type="text" name="mcore_url" class="mcore-url<?php if (!$uri->isValid()): ?> error<?php endif; ?>" value="<?php echo $url ?>" />
			</p>
			<?php submit_button(); ?>
		</form>
	</div>
<?php
}
?>
