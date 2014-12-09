<?php

abstract class rip_abstract_view_helper {

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