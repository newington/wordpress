<?php

/***
    {
        Module: photocrati-lightbox,
        Depends: { photocrati-nextgen_admin }
    }
***/

define('NGG_LIGHTBOX_OPTIONS_SLUG', 'ngg_lightbox_options');
define('NGG_LIGHTBOX_ADVANCED_OPTIONS_SLUG', 'ngg_lightbox_advanced_options');

class M_Lightbox extends C_Base_Module
{
	static $_registered_lightboxes;
	
    function define()
    {
        parent::define(
            'photocrati-lightbox',
            'Lightbox',
            "Provides integration with several JavaScript lightbox effect libraries",
            '0.14',
            'http://leandrovieira.com/projects/jquery/lightbox/',
            'Photocrati Media',
            'http://www.photocrati.com'
        );

		include_once('class.lightbox_installer.php');
		C_Photocrati_Installer::add_handler($this->module_id, 'C_Lightbox_Installer');
    }

    function initialize()
    {
        parent::initialize();
        if (is_admin()) {
			add_action('admin_init', array(&$this, 'add_all_lightbox_forms'));
		}
    }

    /**
     * Adds a configuration form to each library
     */
    function add_all_lightbox_forms()
    {
        foreach ($this->get_registry()->get_utility('I_Lightbox_Library_Mapper')->find_all() as $lib) {
            $this->get_registry()->add_adapter('I_Form', 'A_Lightbox_Library_Form', $lib->name);
            C_Form_Manager::get_instance()->add_form(NGG_LIGHTBOX_ADVANCED_OPTIONS_SLUG, $lib->name);
        }
    }

	function _register_utilities()
	{
        // Provides a utility to perform CRUD operations for Lightbox libraries
		$this->get_registry()->add_utility(
			'I_Lightbox_Library_Mapper',
			'C_Lightbox_Library_Mapper'
		);
	}

    function _register_adapters()
    {
        // Provides factory methods for instantiating lightboxes
		$this->get_registry()->add_adapter('I_Component_Factory', 'A_Lightbox_Factory');

        // Provides an installer for lightbox libraries
        $this->get_registry()->add_adapter('I_Installer', 'A_Lightbox_Installer');
    }
    
	/**
	 * Registers hooks for the WordPress framework
	 */
	function _register_hooks()
	{
        add_action('wp_enqueue_scripts', array($this, '_register_resources'));
        add_action('wp_footer', array($this, '_enqueue_resources'), 3);
		add_action('init', array(&$this, '_register_custom_post_type'));
	}

	/**
	 * Registers the custom post type saved for lightbox libraries
	 */
	function _register_custom_post_type()
	{
		register_post_type('lightbox_library', array(
			'label'					=>	'Lightbox Library',
			'publicly_queryable'	=>	FALSE,
			'exclude_from_search'	=>	TRUE,
		));
	}

    /**
     * Parses certain paths through get_static_url
     *
     * @param string $url
     * @return string Resulting URL
     */
    static function _handle_url($url)
    {
        $router = C_Component_Registry::get_instance()->get_utility('I_Router');
        if (0 !== strpos($url, '/')
        &&  0 !== strpos($url, 'wordpress#')
        &&  0 !== strpos($url, 'http://')
        &&  0 !== strpos($url, 'https://'))
        {
            $url = $router->get_static_url($url);
        }
        elseif (strpos($url, '/') === 0) {
            $url = home_url($url);
        }

        return $url;
    }

    /**
     * Registers or enqueues resources for a given lightbox library
     *
     * @param object $library Lightbox library
     */
    static function _register_library_resources($library, $register = TRUE)
    {
        if ($library) {
            $i = 0;
            foreach (explode("\n", $library->scripts) as $script) {
                if (empty($script))
                    continue;

                $script = self::_handle_url($script);

                // if _registered_lightboxes exist we should register rather than enqueue
                if ($register)
                {
                    // if the module is "wordpress" we leave the rest to Wordpress and only ask that it enqueue
                    // whatever the name of the resource requested may be.
                    if (0 === strpos($script, 'wordpress#'))
                    {
                        self::$_registered_lightboxes[] = substr($script, 10);
                    } else {
                        wp_register_script(
                            $library->name . '-' . $i,
                            $script,
                            array('ngg_common')
                        );
                        self::$_registered_lightboxes[] = $library->name . '-' . $i;
                    }
                }
                else {
                    // same as above, but enqueue now rather than register for later
                    if (0 === strpos($script, 'wordpress#'))
                    {
                        wp_enqueue_script(substr($script, 10));
                    } else {
                        wp_enqueue_script(
                            $library->name . '-' . $i,
                            $script,
                            array('ngg_common')
                        );
                    }
                }

                if ($i == 0 AND isset($library->values))
                {
                    foreach ($library->values as $name => $value) {
                        if (empty($value))
                            continue;
                        $value = self::_handle_url($value);
                        self::_add_script_data(
                            $library->name . '-0',
                            $name,
                            $value,
                            FALSE
                        );
                    }
                }

                if ($i == 0 && !empty($library->i18n))
                {
                    wp_localize_script(
                        $library->name . '-0',
                        'ngg_lightbox_i18n',
                        $library->i18n
                    );
                }
                $i += 1;
            }

            // in 2.0.41 this field was renamed; use the old attribute as a fallback
            if (empty($library->styles) && !empty($library->css_stylesheets))
                $library->styles = $library->css_stylesheets;

            $i = 0;
            foreach (explode("\n", $library->styles) as $style) {
                if (empty($style))
                    continue;
                $style = self::_handle_url($style);
                if (0 === strpos($style, 'wordpress#'))
                {
                    wp_enqueue_style(substr($style, 10));
                }
                else {
                    wp_enqueue_style(
                        $library->name . '-' . $i,
                        $style
                    );
                }
                $i += 1;
            }
        }
    }

    /**
     * Registers our static settings resources so the ATP module can find them later
     */
    function _register_resources()
    {
        $router = $this->get_registry()->get_utility('I_Router');
        $settings	= C_NextGen_Settings::get_instance();
        $thumbEffectContext = isset($settings->thumbEffectContext) ? $settings->thumbEffectContext : '';

        wp_register_script(
            'nextgen_lightbox_context',
            $router->get_static_url('photocrati-lightbox#lightbox_context.js')
        );
        wp_enqueue_script('nextgen_lightbox_context');
				
        if ($thumbEffectContext != null && $thumbEffectContext != 'nextgen_images') {
            $mapper		= $this->object->get_registry()->get_utility('I_Lightbox_Library_Mapper');
            $library	= $mapper->find_by_name($settings->thumbEffect);
					
            // Make the path to the static resources available for libraries
            // Shutter-Reloaded in particular depends on this
            $this->_add_script_data(
                'ngg_common',
                'nextgen_lightbox_settings',
                array('static_path' => $this->get_registry()->get_utility('I_Fs')->find_static_abspath('', 'photocrati-lightbox'), 'context' => $thumbEffectContext),
                TRUE,
                true
            );
		      
            self::_register_library_resources($library);
        }
    }
    
    function _enqueue_resources()
    {
        if (defined('NGG_SKIP_LOAD_SCRIPTS') && NGG_SKIP_LOAD_SCRIPTS)
            return;

        foreach (((array)self::$_registered_lightboxes) as $library) {
            wp_enqueue_script($library);
        }
    }
    
	/**
	 * Adds data to the DOM which is then accessible by a script -- borrowed from display type controller class
	 * @param string $handle
	 * @param string $object_name
	 * @param mixed $object_value
	 * @param bool $define
	 */
	function _add_script_data($handle, $object_name, $object_value, $define=TRUE, $override=FALSE)
	{
		$retval = FALSE;

		// wp_localize_script allows you to add data to the DOM, associated
		// with a particular script. You can even call wp_localize_script
		// multiple times to add multiple objects to the DOM. However, there
		// are a few problems with wp_localize_script:
		//
		// - If you call it with the same object_name more than once, you're
		//   overwritting the first call.
		// - You cannot namespace your objects due to the "var" keyword always
		// - being used.
		//
		// To circumvent the above issues, we're going to use the WP_Scripts
		// object to workaround the above issues
		global $wp_scripts;

		// Has the script been registered or enqueued yet?
		if (isset($wp_scripts->registered[$handle])) {

			// Get the associated data with this script
			$script = &$wp_scripts->registered[$handle];
			$data = isset($script->extra['data']) ? $script->extra['data'] : '';

			// Construct the addition
			$addition = $define ? "\nvar {$object_name} = " . json_encode($object_value) . ';' :
				"\n{$object_name} = " . json_encode($object_value) . ';';

			// Add the addition
			if ($override) {
				$data .= $addition;
				$retval = TRUE;
			}
			else if (strpos($data, $object_name) === FALSE) {
				$data .= $addition;
				$retval = TRUE;
			}

            $script->extra['data'] = $data;
            
            unset($script);
		}

		return $retval;
	}


    function get_type_list()
    {
        return array(
            'A_Lightbox_Factory' => 'adapter.lightbox_factory.php',
            'C_Lightbox_Installer' => 'class.lightbox_installer.php',
            'A_Lightbox_Library_Form' => 'adapter.lightbox_library_form.php',
            'C_Lightbox_Library' => 'class.lightbox_library.php',
            'C_Lightbox_Library_Mapper' => 'class.lightbox_library_mapper.php',
            'I_Lightbox_Library' => 'interface.lightbox_library.php',
            'I_Lightbox_Library_Mapper' => 'interface.lightbox_library_mapper.php'
        );
    }
}

new M_Lightbox();
