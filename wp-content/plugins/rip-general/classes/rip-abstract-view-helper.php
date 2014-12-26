<?php

namespace Rip_General\Classes;

/**
 * Abstrac view helper.
 * Define methods used by concrete view helpers 
 * to render and drawn piece of Html.
 */
abstract class Rip_Abstract_View_Helper {

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
     * Abstract method implemented by all 
     * concrete vie helpers.
     */
    abstract public function render();
}
