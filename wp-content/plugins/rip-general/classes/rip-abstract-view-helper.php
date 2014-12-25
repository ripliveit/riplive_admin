<?php

abstract class Rip_Abstract_View_Helper {

    /**
     * Path of the metabox html partial.
     * 
     * @var type 
     */
    protected $_partial_path;
    
    /**
     * Store all data that must be drawn.
     * 
     * @var type 
     */
    protected $_view_args = null;
    
    /**
     * Abstract method implemented by all 
     * concrete client.
     */
    abstract public function render();
}