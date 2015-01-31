<?php

namespace Rip_General\Mappers;

/**
 * Description of rip-factory-mapper
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
    public static function create_mapper($class_name, $arguments) {
        if (empty($class_name)) {
            throw new Exception('Specify a mapper class name to create.');
        }

        //$class_name = self::$_namespace . $class_name;

        if (class_exists($class_name)) {
            $mapper = new $class_name($arguments);

            return $mapper;
        } else {
            throw new \Exception('Mapper ' . $class_name . ' was not found and cannot be created');
        };
    }

}