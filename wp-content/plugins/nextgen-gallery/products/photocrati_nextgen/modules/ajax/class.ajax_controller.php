<?php

class C_Ajax_Controller extends C_MVC_Controller
{
	static $_instances = array();

	function define($context=FALSE)
	{
		parent::define($context);
		$this->implement('I_Ajax_Controller');
	}

	function index_action()
	{
        $retval = NULL;

        // Inform the MVC framework what type of content we're returning
        $this->set_content_type('json');

		// Start an output buffer to avoid displaying any PHP warnings/errors
        ob_start();

		// Get the action requested & find and execute the related method
		if (($action = $this->param('action'))) {
			$method = "{$action}_action";
			if ($this->has_method($method)) {
				$retval = $this->call_method($method);
			}
            else $retval = array('error' => 'Not a valid AJAX action');
		}
        else $retval = array('error' => 'No action specified');

		// Flush the buffer
		while (ob_get_level() > 0 ) {
			ob_end_clean();
		}

		// Return the JSON to the browser
		echo json_encode($retval);
	}

	/**
	 * Returns an instance of this class
	 * @param string $context
	 * @return C_Ajax_Controller
	 */
	static function get_instance($context=FALSE)
	{
		if (!isset(self::$_instances[$context])) {
			$klass = get_class();
			self::$_instances[$context] = new $klass($context);
		}
		return self::$_instances[$context];
	}
}
