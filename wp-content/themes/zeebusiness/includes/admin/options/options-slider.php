<?php
	
	function themezee_get_slider_sections() {
		$themezee_sections = array();
		
		$themezee_sections[] = array("id" => "themeZee_slider",
					"name" => __('Featured Posts Slider', 'themezee_lang'));
					
		return $themezee_sections;
	}
	
	function themezee_get_slider_settings() {
		
		$themezee_settings = array();
						
		### POST SLIDER SETTINGS
		#######################################################################################
		$themezee_settings[] = array("name" => __('Show Post Slider?', 'themezee_lang'),
						"desc" => __('Check this if you want to show the Featured Post Slider.', 'themezee_lang'),
						"id" => "themeZee_show_slider",
						"std" => "false",
						"type" => "checkbox",
						"section" => "themeZee_slider");
						
		$themezee_settings[] = array("name" => __('Slider Title', 'themezee_lang'),
						"desc" => __('Enter here your headline which is displayed above the featured posts.', 'themezee_lang'),
						"id" => "themeZee_slider_title",
						"std" => "Featured Posts",
						"type" => "text",
						"section" => "themeZee_slider");
						
		$themezee_settings[] = array("name" => "Slider Effect",
						"desc" => "",
						"id" => "themeZee_slider_mode",
						"std" => "0",
						"type" => "radio",
						'choices' => array(
									0 => 'Horizontal Slider',
									1 => 'DropDown Slider',
									2 => 'Fade Slider'),
						"section" => "themeZee_slider"
						);

		$themezee_settings[] = array("name" => __('Slider Content', 'themezee_lang'),
						"desc" => "",
						"id" => "themeZee_slider_content",
						"std" => "0",
						"type" => "radio",
						'choices' => array(
									0 => __('Show latest posts', 'themezee_lang'),
									1 => __('Show latest posts from category "featured"', 'themezee_lang'),
									2 => __('Show latest posts with post_meta_key "featured"', 'themezee_lang'),
									3 => __('Show latest posts from custom category(enter ID below)', 'themezee_lang')),
						"section" => "themeZee_slider"
						);
						
		$themezee_settings[] = array("name" => __('category ID', 'themezee_lang'),
						"desc" => __("Please enter the category ID you'd like to include in the slideshow.(You have to tick the last option above)", 'themezee_lang'),
						"id" => "themeZee_slider_cat",
						"std" => "1",
						"type" => "text",
						"section" => "themeZee_slider");

		$themezee_settings[] = array("name" => __('Number of Posts', 'themezee_lang'),
						"desc" => __('Enter the number how much posts should be displayed in the post slider.', 'themezee_lang'),
						"id" => "themeZee_slider_limit",
						"std" => "5",
						"type" => "text",
						"section" => "themeZee_slider");
		
		return $themezee_settings;
	}

?>