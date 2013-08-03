<?php

class A_WordPress_Router extends Mixin
{
	var $_site_url = FALSE;

	function initialize()
	{
		// Set context to path if subdirectory install
		$parts = parse_url($this->object->get_base_url());
		if (isset($parts['path'])) $this->object->context = $parts['path'];


		$this->object->add_post_hook(
			'get_url',
			'Construct url for WordPress, considering permalinks',
			get_class(),
			'_modify_url_for_wordpress'
		);
	}

	function _modify_url_for_wordpress()
	{
		// Get the method to be returned
		$retval = $this->object->get_method_property(
			$this->method_called,
			ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE
		);

		// Determine whether the url is a directory or file on the filesystem
		// If so, then we do NOT need /index.php as part of the url
		$base_url = $this->object->get_base_url();
		$filename = str_replace(
			$base_url,
			$this->get_registry()->get_utility('I_Fs')->get_document_root(),
			$retval
		);

		if ($retval && file_exists($filename) && $retval != $base_url) {

			// Remove index.php from the url
			$retval = $this->object->remove_url_segment('/index.php', $retval);

			// Static urls don't end with a slash
			$retval = untrailingslashit($retval);

			// Set retval to the new url being returned
			$this->object->set_method_property(
				$this->method_called,
				ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE,
				$retval
			);
		}

		return $retval;
	}


	function get_base_url()
	{
		if (!$this->_site_url) {
			$this->_site_url = site_url();
			if (!get_option('permalink_structure')) {
				$this->_site_url = $this->object->join_paths(
					$this->_site_url, '/index.php'
				);
			}
		}

		return $this->_site_url;
	}
}