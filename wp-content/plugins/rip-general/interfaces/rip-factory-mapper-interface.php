<?php

namespace Rip_General\Interfaces;

/**
 * Define the contract 
 * that concrete factory mapper must respect.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 */
interface Rip_Factory_Mapper_Interface {

    /**
     * Factory method
     * used tocreate a mapper class.
     */
    public static function create_mapper($class_name);
}
