<?php
/**
{
    Module: photocrati-nextgen_addgallery_page
}
**/

define('NGG_ADD_GALLERY_SLUG', 'ngg_addgallery');

class M_NextGen_AddGallery_Page extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-nextgen_addgallery_page',
            'NextGEN Add Gallery Page',
            'Provides admin page for adding a gallery and uploading images',
            '0.6',
            'http://www.nextgen-gallery.com',
            'Photocrati Media',
            'http://www.photocrati.com'
        );
    }
    
    function get_type_list()
    {
    	return array(
    		'A_Import_Folder_Form' => 'adapter.import_folder_form.php',
    		'A_Nextgen_Addgallery_Ajax' => 'adapter.nextgen_addgallery_ajax.php',
    		'A_Nextgen_Addgallery_Controller' => 'adapter.nextgen_addgallery_controller.php',
    		'A_Nextgen_Addgallery_Forms' => 'adapter.nextgen_addgallery_forms.php',
    		'A_Nextgen_Addgallery_Pages' => 'adapter.nextgen_addgallery_pages.php',
    		'A_Upload_Images_Form' => 'adapter.upload_images_form.php',
    	);
    }

    function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_Page_Manager', 'A_NextGen_AddGallery_Pages');
        $this->get_registry()->add_adapter('I_Form_Manager', 'A_NextGen_AddGallery_Forms');
        $this->get_registry()->add_adapter('I_Ajax_Controller', 'A_NextGen_AddGallery_Ajax');
    }

    function _register_hooks()
    {
        add_action('admin_init', array(&$this, 'register_scripts'));
    }

    function register_scripts()
    {
        if (is_admin()) {
            $router = $this->_get_registry()->get_utility('I_Router');
            wp_register_script('browserplus', $router->get_static_url('photocrati-nextgen_addgallery_page#browserplus-2.4.21.min.js'));
            wp_register_script('ngg.plupload.moxie', $router->get_static_url('photocrati-nextgen_addgallery_page#plupload-2.1.1/moxie.min.js'));
            wp_register_script('ngg.plupload.full',  $router->get_static_url('photocrati-nextgen_addgallery_page#plupload-2.1.1/plupload.full.min.js'), array('ngg.plupload.moxie'));
            wp_register_script('ngg.plupload.queue', $router->get_static_url('photocrati-nextgen_addgallery_page#plupload-2.1.1/jquery.plupload.queue/jquery.plupload.queue.min.js'), array('ngg.plupload.full'));
            wp_register_style('ngg.plupload.queue',  $router->get_static_url('photocrati-nextgen_addgallery_page#plupload-2.1.1/jquery.plupload.queue/css/jquery.plupload.queue.css'));
            wp_register_style('nextgen_addgallery_page', $router->get_static_url('photocrati-nextgen_addgallery_page#styles.css'));
            wp_register_script('jquery.filetree', $router->get_static_url('photocrati-nextgen_addgallery_page#jquery.filetree/jquery.filetree.js'), array('jquery'));
            wp_register_style('jquery.filetree', $router->get_static_url('photocrati-nextgen_addgallery_page#jquery.filetree/jquery.filetree.css'));
        }
    }
}
new M_NextGen_AddGallery_Page();
