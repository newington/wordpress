<?php

class C_NextGen_Basic_Album_Installer extends C_Gallery_Display_Installer
{
	function install()
	{
		$this->install_display_type(
			NGG_BASIC_COMPACT_ALBUM, array(
			'title'					=>	__('NextGEN Basic Compact Album', 'nggallery'),
			'entity_types'			=>	array('album', 'gallery'),
			'preview_image_relpath'	=>	'photocrati-nextgen_basic_album#compact_preview.jpg',
			'default_source'		=>	'albums',
			'view_order' => NGG_DISPLAY_PRIORITY_BASE + 200
		));

		$this->install_display_type(
			NGG_BASIC_EXTENDED_ALBUM, array(
			'title'					=>	__('NextGEN Basic Extended Album', 'nggallery'),
			'entity_types'			=>	array('album', 'gallery'),
			'preview_image_relpath'	=>	'photocrati-nextgen_basic_album#extended_preview.jpg',
			'default_source'		=>	'albums',
			'view_order' => NGG_DISPLAY_PRIORITY_BASE + 210
		));
	}
}