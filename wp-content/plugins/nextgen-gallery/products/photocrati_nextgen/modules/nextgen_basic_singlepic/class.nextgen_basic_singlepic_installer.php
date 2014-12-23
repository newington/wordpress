<?php

class C_NextGen_Basic_SinglePic_Installer extends C_Gallery_Display_Installer
{
	function install()
	{
		$this->install_display_type(
			NGG_BASIC_SINGLEPIC, array(
			'title'					=>	__('NextGEN Basic SinglePic', 'nggallery'),
			'entity_types'			=>	array('image'),
			'preview_image_relpath'	=>	'photocrati-nextgen_basic_singlepic#preview.gif',
			'default_source'		=>	'galleries',
			'view_order' => NGG_DISPLAY_PRIORITY_BASE + 60
		));
	}
}
