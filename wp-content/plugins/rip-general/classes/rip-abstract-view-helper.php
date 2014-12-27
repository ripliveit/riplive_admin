<?php

namespace Rip_General\Classes;

/**
 * Abstract view helper.
 * Define methods used by concrete view helpers 
 * to render and drawn piece of Html.
 */
abstract class Rip_Abstract_View_Helper implements \Rip_General\Interfaces\Rip_View_Renderer {

    /**
     * Path of the html partial to render.
     * 
     * @var type 
     */
    protected $_partial_path;

    /**
     * Store all the data that must be drawn.
     * 
     * @var type 
     */
    protected $_view_args = null;

    /**
     * Render the template.
     */
    public function render() {
        if (!empty($this->_view_args)) {
            extract($this->_view_args);
        }

        ob_start();
        include($this->_partial_path);
        $out = ob_get_contents();
        ob_end_clean();

        echo $out;
    }

}
