<?php

namespace Rip_General\Classes;

/**
 * Define the contract 
 * that concrete factory mapper must respect.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 */
abstract class Rip_Abstract_Factory_Mapper {
    
     /**
     * Factory method
     * used tocreate a mapper class.
     */
    abstract public function create_mapper($class_name);
}
