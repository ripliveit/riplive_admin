<?php

namespace Rip_General\Mappers;

/**
 * Description of rip-factory-mapper
 *
 * @author Gabriele
 */
class Rip_Factory_Mapper extends \Rip_General\Classes\Rip_Abstract_Factory_Mapper {

    /**
     * Namespace used
     * to dynamically construct the mapper's class.
     * 
     * @var string 
     */
    protected $_namespace = '\Rip_General\Mappers\\';
    
    /**
     * Dinamically create a mapper.
     * 
     * @param string $class_name
     * @return \class_name
     * @throws Exception
     */
    public function create_mapper($class_name) {
        if (empty($class_name)) {
            throw new Exception('Specify a mapper class name to create.');
        }

        $class_name = $this->_namespace . $class_name;

        if (class_exists($class_name)) {
            $mapper = new $class_name();

            return $mapper;
        } else {
            throw new Exception('Mapper ' . $class_name . ' was not found and cannot be created');
        };
    }

}
