<?php

namespace Rip_General\Mappers;

/**
 * A concrete factory
 * that implements a factory method used to create
 * objects at runtime.
 *
 * @author Gabriele
 */
class Rip_Factory_Mapper implements \Rip_General\Interfaces\Rip_Factory_Mapper_Interface {

    /**
     * Namespace used
     * to dynamically construct the mapper's class.
     * 
     * @var string 
     */
    protected static $_namespace = '\Rip_General\Mappers\\';

    /**
     * Dinamically create a mapper.
     * 
     * @param string $class_name
     * @return \class_name
     * @throws Exception
     */
    public static function create_mapper($class_name) {
        if (empty($class_name)) {
            throw new Exception('Specify a mapper class name to create.');
        }

        $args = func_get_args();

        if (!empty($args)) {
            $args = array_slice($args, 1);
        }

        if (class_exists($class_name)) {
            $reflection = new \ReflectionClass($class_name);
            $mapper     = $reflection->newInstanceArgs($args);
            
            return $mapper;
        } else {
            throw new \Exception('Mapper ' . $class_name . ' was not found and cannot be created');
        }
    }

}
