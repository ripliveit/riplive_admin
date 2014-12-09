<?php

/**
 * Abstract class that all concrete metabox 
 * must implement.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 */
abstract class rip_abstract_metabox {

    /**
     * Path of the metabox html partial.
     * 
     * @var string 
     */
    protected $_partial_path;

    /**
     * Metabox configuration's array.
     * 
     * @var array 
     */
    protected $_field;

    /**
     * Metabox saved value, if presents.
     * 
     * @var array 
     */
    protected $_meta;

    /**
     * Render the html partial content.
     * 
     * @param string $field
     * @param array $meta
     * @return string
     */
    public function render() {
        if (!empty($this->_field)) {
            extract($this->_field);
        }

        if (!empty($this->_meta)) {
            extract($this->_meta);
        }
        
        ob_start();
        include($this->_partial_path);
        $out = ob_get_contents();
        ob_end_clean();

        echo $out;
    }

}