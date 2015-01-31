<?php

namespace Rip_General\Interfaces;

/**
 * Define the contract 
 * that concrete factory metabox must respect.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 */
interface Rip_Factory_Metabox_Interface {
    
    /**
     * Factory method
     * used to create a metabox class.
     */
    public static function create_metabox($field, $meta, $class_name);
}
