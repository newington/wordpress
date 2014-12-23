<?php

class C_NextGen_Basic_ImageBrowser_Installer extends C_Gallery_Display_Installer
{
	function install()
	{
		$this->install_display_type(
			NGG_BASIC_IMAGEBROWSER, array(
				'title'					=>	__('NextGEN Basic ImageBrowser', 'nggallery'),
				'entity_types'			=>	array('image'),
				'preview_image_relpath'	=>	'photocrati-nextgen_basic_imagebrowser#preview.jpg',
				'default_source'		=>	'galleries',
				'view_order' => NGG_DISPLAY_PRIORITY_BASE + 20
			)
		);
	}
}
