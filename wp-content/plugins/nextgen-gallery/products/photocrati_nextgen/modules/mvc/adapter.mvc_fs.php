<?php

class A_MVC_Fs extends Mixin
{
	/**
	 * Gets the absolute path to a static resource. If it doesn't exist, then NULL is returned
     *
	 * @param string $path
	 * @param string $module
	 * @param string $relative
	 * @return string|NULL
	 */
	function find_static_abspath($path, $module = FALSE, $relative = FALSE)
	{
		// Find the module directory
		$fs = $this->object->get_registry()->get_utility('I_Fs');
		if (!$module) list($path, $module) = $fs->parse_formatted_path($path);
		$mod_dir = $this->object->get_registry()->get_module_dir($module);

		// Create the absolute path to the file
		$path = $fs->join_paths(
			$mod_dir,
			C_NextGen_Settings::get_instance()->get('mvc_static_dirname'),
			$path
		);

		// Get the relative path, if asked. Skip when docroot=/ lest we generate url like
        // wp-contentpluginsnextgen-galleryproducts..
		if ($relative) {
            $original_length = strlen($path);
            $roots = array('plugins', 'plugins_mu', 'templates', 'stylesheets');
            $found_root = FALSE;
            foreach ($roots as $root) {
                $path = str_replace($this->object->get_document_root($root), '', $path);
                if (strlen($path) != $original_length) {
                    $found_root = $root;
                    break;
                }
            }
        }

		return $path;
	}

	/**
	 * Gets the relative path to a static resource. If it doesn't exist, then NULL is returned
     *
	 * @param string $path
	 * @param string $module
	 * @return string|NULL
	 */
	function find_static_relpath($path, $module = FALSE)
	{
		return $this->object->find_static_abspath($path, $module, TRUE);
	}
}
