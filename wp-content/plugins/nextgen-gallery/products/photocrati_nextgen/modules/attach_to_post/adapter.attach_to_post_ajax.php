<?php

/**
 * Provides AJAX actions for the Attach To Post interface
 * TODO: Need to add authorization checks to each action
 */
class A_Attach_To_Post_Ajax extends Mixin
{
	var $attach_to_post = NULL;

	/**
	 * Retrieves the attach to post controller
	 */
    function get_attach_to_post()
    {
        if (is_null($this->attach_to_post))
            $this->attach_to_post = $this->object->get_registry()->get_utility('I_Attach_To_Post_Controller');
        return $this->attach_to_post;
    }


	/**
	 * Returns a list of image sources for the Attach to Post interface
	 * @return type
	 */
	function get_attach_to_post_sources_action()
	{
		$response = array();

		if ($this->object->validate_ajax_request())
		{
			$response['sources'] = $this->get_attach_to_post()->get_sources();
		}

		return $response;
	}


	/**
	 * Gets existing galleries
	 * @return array
	 */
	function get_existing_galleries_action()
	{
		$response = array();

		if ($this->object->validate_ajax_request())
		{
			$limit = $this->object->param('limit');
			$offset = $this->object->param('offset');

			// We return the total # of galleries, so that the client can make
			// pagination requests
			$mapper = $this->object->get_registry()->get_utility('I_Gallery_Mapper');
			$response['total'] = $mapper->count();
			$response['limit'] = $limit = $limit ? $limit : 0;
			$response['offset'] = $offset = $offset ? $offset : 0;

			// Get the galleries
			$mapper->select();
			if ($limit) $mapper->limit($limit, $offset);
			$response['items'] = $mapper->run_query();
		}

		return $response;
	}


    /**
     * Gets existing albums
     * @return array
     */
    function get_existing_albums_action()
    {
        $response = array();

		if ($this->object->validate_ajax_request())
		{
		    $limit  = $this->object->param('limit');
		    $offset = $this->object->param('offset');

		    // We return the total # of albums, so that the client can make pagination requests
		    $mapper = $this->object->get_registry()->get_utility('I_Album_Mapper');
		    $response['total'] = $mapper->count();
		    $response['limit'] = $limit = $limit ? $limit : 0;
		    $response['offset']= $offset = $offset ? $offset : 0;

		    // Get the albums
		    $mapper->select();
		    if ($limit) $mapper->limit($limit, $offset);
		    $response['items'] = $mapper->run_query();
		}

        return $response;
    }

	/**
	 * Gets existing image tags
	 * @return array
	 */
	function get_existing_image_tags_action()
	{
		$response = array();

		if ($this->object->validate_ajax_request())
		{
			$limit = $this->object->param('limit');
			$offset = $this->object->param('offset');
			$response['limit'] = $limit = $limit ? $limit : 0;
			$response['offset'] = $offset = $offset ? $offset : 0;
			$response['items'] = array();
			$params = array(
				'number'	=>	$limit,
				'offset'	=>	$offset,
				'fields'	=>	'names'
			);
			foreach (get_terms('ngg_tag', $params) as $term) {
				$response['items'][] = array(
					'id'	=>	$term,
					'title'	=>	$term,
                    'name'  =>  $term
				);
			}
			$response['total'] = count(get_terms('ngg_tag', array('fields' => 'ids')));
		}

		return $response;
	}

	/**
	 * Gets entities (such as images) for a displayed gallery (attached gallery)
	 */
	function get_displayed_gallery_entities_action()
	{
		$response = array();
		if ($this->object->validate_ajax_request() && ($params = $this->object->param('displayed_gallery'))) {
			$limit	 = $this->object->param('limit');
			$offset  = $this->object->param('offset');
			$factory = $this->object->get_registry()->get_utility('I_Component_Factory');
			$displayed_gallery = $factory->create('displayed_gallery');
			foreach ($params as $key => $value) $displayed_gallery->$key = $value;
			$response['limit']	= $limit = $limit ? $limit : 0;
			$response['offset'] = $offset = $offset ? $offset : 0;
			$response['total']	= $displayed_gallery->get_entity_count('both');
			$response['items'] = $displayed_gallery->get_entities($limit, $offset, FALSE, 'both');
            $controller   = $this->object->get_registry()->get_utility('I_Display_Type_Controller');
			$storage	  = $this->object->get_registry()->get_utility('I_Gallery_Storage');
			$image_mapper = $this->object->get_registry()->get_utility('I_Image_Mapper');
			$settings	  = C_NextGen_Settings::get_instance();
			foreach ( $response['items'] as &$entity) {
                $image = $entity;
                if (in_array($displayed_gallery->source, array('album','albums'))) {
                    // Set the alttext of the preview image to the
					// name of the gallery or album
					if (($image = $image_mapper->find($entity->previewpic))) {
						if ($entity->is_album)
							$image->alttext = sprintf(__('Album: %s', 'nggallery'), $entity->name);
						else
							$image->alttext = sprintf(__('Gallery: %s', 'nggallery'), $entity->title);
					}

					// Prefix the id of an album with 'a'
                    if ($entity->is_album) {
                        $id = $entity->{$entity->id_field};
                        $entity->{$entity->id_field} = 'a'.$id;
                    }
                }

				// Get the thumbnail
				$entity->thumb_url = $storage->get_image_url($image, 'thumb', TRUE);
				$entity->thumb_html	= $storage->get_image_html($image, 'thumb');
				$entity->max_width  = $settings->thumbwidth;
				$entity->max_height = $settings->thumbheight;
			}
		}
		else {
			$response['error'] = __('Missing parameters', 'nggallery');
		}
		return $response;
	}


	/**
	 * Saves the displayed gallery
	 */
	function save_displayed_gallery_action()
	{
		$response = array();
		$mapper = $this->object->get_registry()->get_utility('I_Displayed_Gallery_Mapper');

		// Do we have fields to work with?
		if ($this->object->validate_ajax_request(true) && ($params = json_decode($this->object->param('displayed_gallery')))) {

			// Existing displayed gallery ?
			if (($id = $this->object->param('id'))) {
				$displayed_gallery = $mapper->find($id, TRUE);
				if ($displayed_gallery) {
					foreach ($params as $key => $value) $displayed_gallery->$key = $value;
				}
			}
			else {
				$factory = $this->object->get_registry()->get_utility('I_Component_Factory');
				$displayed_gallery = $factory->create('displayed_gallery', $params, $mapper);
			}

			// Save the changes
			if ($displayed_gallery) {
				if ($displayed_gallery->save()) $response['displayed_gallery'] = $displayed_gallery->get_entity();
				else $response['validation_errors'] = $this->get_attach_to_post()->show_errors_for($displayed_gallery, TRUE);
			}
			else
			{
				$response['error'] = __('Displayed gallery does not exist', 'nggallery');
			}
		}
		else $response['error'] = __('Invalid request', 'nggallery');

		return $response;
	}

	function validate_ajax_request($check_token = false)
	{
		$valid_request = false;
		$security = $this->get_registry()->get_utility('I_Security_Manager');
		$sec_token = $security->get_request_token('nextgen_edit_displayed_gallery');
		$sec_actor = $security->get_current_actor();

		if ($sec_actor->is_allowed('nextgen_edit_displayed_gallery') && (!$check_token || $sec_token->check_current_request()))
		{
			$valid_request = true;
		}

		return $valid_request;
	}
}
