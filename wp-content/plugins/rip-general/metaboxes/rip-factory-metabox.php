<?php

namespace Rip_General\Metaboxes;

/**
 * Factory class.
 * Dinamically create metabox, depending of the configuration's array passed in
 * on class contruction.
 * 
 * @author Gabriele D'Arrigo - @acirdesign
 * @see rip_abstract_factory_metabox
 */
class Rip_Factory_Metabox extends \Rip_General\Classes\Rip_Abstract_Metabox {
    
    /**
     * Namespace used
     * to dynamic construct the metabox class.
     * 
     * @var string 
     */
    protected $_namespace = '\Rip_General\Metaboxes\\';

    /**
     * Factory method
     * Return the metabox content.
     * 
     * @param string $field
     * @param array $meta
     * @param string $class_name
     * @throws Exception
     */
    public function create_metabox($field, $meta, $class_name) {
        if (empty($class_name)) {
            throw new Exception('Specify metabox class name to permit the construction of the metabox');
        }

        $class_name = $this->_namespace . $class_name;

        if (class_exists($class_name)) {
            $metabox = new $class_name($field, $meta);

            return $metabox;
        } else {
            throw new Exception('Class ' . $class_name . ' was not found');
        }
    }

}
