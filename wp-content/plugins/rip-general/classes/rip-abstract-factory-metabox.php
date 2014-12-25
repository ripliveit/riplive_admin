<?php
namespace Rip_General\Classes;

/**
 * Define the contract that concrete factory metabox must respect.
 * @author Gabriele D'Arrigo - @acirdesign
 */
abstract class Rip_Abstract_Factory_Metabox {
    
    /**
     * Factory method.
     * Create a metabox class.
     */
    abstract public function create_metabox($field, $meta, $class_name);
}