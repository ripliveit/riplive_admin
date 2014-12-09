<?php
/**
 * Define the contract that concrete factory metabox must respect.
 * @author Gabriele D'Arrigo - @acirdesign
 */
abstract class rip_abstract_factory_metabox {
    
    /**
     * Factory method.
     * Create a metabox class.
     */
    abstract public function create_metabox($field, $meta, $class_name);
}