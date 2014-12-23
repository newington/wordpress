<?php

/*
 {
		Module: photocrati-ajax,
		Depends: { photocrati-mvc }
 }
 */
class M_Ajax extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-ajax',
			'AJAX',
			'Provides AJAX functionality',
			'0.7',
			'http://www.photocrati.com',
			'Photocrati Media',
			'http://www.photocrati.com'
		);

		include_once('class.ajax_option_handler.php');
		C_NextGen_Settings::get_instance()->add_option_handler('C_Ajax_Option_Handler', array(
			'ajax_slug',
			'ajax_url',
			'ajax_js_url'
		));
        if (is_multisite()) C_NextGen_Global_Settings::get_instance()->add_option_handler('C_Ajax_Option_Handler', array(
            'ajax_slug',
            'ajax_url',
            'ajax_js_url'
        ));

		include_once('class.ajax_installer.php');
		C_Photocrati_Installer::add_handler($this->module_id, 'C_Ajax_Installer');
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_Router', 'A_Ajax_Routes');
	}

	function _register_utilities()
	{
		$this->get_registry()->add_utility('I_Ajax_Controller', 'C_Ajax_Controller');

	}

	/**
	 * Hooks into the WordPress framework
	 */
	function _register_hooks()
	{
		add_action('init', array(&$this, 'enqueue_scripts'), 9);
	}


	/**
	 * Loads a single script to provide the photocrati_ajax settings to the web browser
	 */
	function enqueue_scripts()
	{
        $settings = C_NextGen_Settings::get_instance();
        $router   = $this->get_registry()->get_utility('I_Router');

        wp_register_script('photocrati_ajax', $settings->ajax_js_url);
        wp_enqueue_script('photocrati_ajax');

        $vars = array(
            'url' => $router->get_url($settings->ajax_slug, FALSE),
            'wp_home_url' => $router->get_base_url('home'),
            'wp_site_url' => $router->get_base_url('site'),
            'wp_root_url' => $router->get_base_url('root'),
            'wp_plugins_url' => $router->get_base_url('plugins'),
            'wp_content_url' => $router->get_base_url('content'),
            'wp_includes_url' => includes_url()
        );
        wp_localize_script('photocrati_ajax', 'photocrati_ajax', $vars);

        if (defined('NGG_SKIP_LOAD_SCRIPTS') && NGG_SKIP_LOAD_SCRIPTS)
            return;

		wp_register_script('persist-js', 	$router->get_static_url('photocrati-ajax#persist.js'));
		wp_register_script('store-js',	 	$router->get_static_url('photocrati-ajax#store.js'));
		wp_register_script('ngg-store-js',	$router->get_static_url('photocrati-ajax#ngg_store.js'), array('jquery', 'persist-js', 'store-js'));

		wp_enqueue_script('ngg-store-js');
	}

    function get_type_list()
    {
        return array(
            'A_Ajax_Routes' => 'adapter.ajax_routes.php',
            'C_Ajax_Installer' => 'class.ajax_installer.php',
            'C_Ajax_Controller' => 'class.ajax_controller.php',
            'I_Ajax_Controller' => 'interface.ajax_controller.php',
            'M_Ajax' => 'module.ajax.php'
        );
    }
}

new M_Ajax();
