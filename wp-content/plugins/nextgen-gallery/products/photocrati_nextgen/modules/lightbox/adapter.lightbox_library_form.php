<?php

class A_Lightbox_Library_Form extends Mixin
{
    function get_model()
    {
        return $this->object
                    ->get_registry()
                    ->get_utility('I_Lightbox_Library_Mapper')
                    ->find_by_name($this->object->context, TRUE);
    }

    /**
     * Returns a list of fields to render on the settings page
     */
    function _get_field_names()
    {
        return array(
            'lightbox_library_code',
            'lightbox_library_styles',
            'lightbox_library_scripts'
        );
    }

    /**
     * @param $lightbox
     * @return mixed
     */
    function _render_lightbox_library_code_field($lightbox)
    {
        return $this->_render_text_field(
            $lightbox,
            'code',
            __('Code', 'nggallery'),
            $lightbox->code
        );
    }

    /**
     * @param $lightbox
     * @return mixed
     */
    function _render_lightbox_library_styles_field($lightbox)
    {
        return $this->_render_textarea_field(
            $lightbox,
            'styles',
            __('Stylesheet URL', 'nggallery'),
            $lightbox->styles
        );
    }

    /**
     * @param $lightbox
     * @return mixed
     */
    function _render_lightbox_library_scripts_field($lightbox)
    {
        return $this->_render_textarea_field(
            $lightbox,
            'scripts',
            __('Javascript URL', 'nggallery'),
            $lightbox->scripts
        );
    }
}
