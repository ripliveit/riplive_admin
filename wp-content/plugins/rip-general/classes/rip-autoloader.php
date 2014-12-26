<?php

namespace Rip_General\Classes;

/**
 * Plugin autoloader.
 * Autoload all required plugin's classes, 
 * including required class through namespace construction.
 * 
 * @author Gabriele D'Arrigo - @acirdesign.
 */
class Rip_Autoloader {

    /**
     * Plugin path.
     * 
     * @var type 
     */
    protected $_plugin_dir_path;

    /**
     * On class construction set current plugin directory tree.
     * 
     * @param type $plugin_dir_path
     */
    public function __construct($plugin_dir_path) {
        $this->_plugin_dir_path = $plugin_dir_path;
        spl_autoload_register(array($this, '_load_classes'));
    }

    /**
     * Load required class.
     * 
     * @param type $class_name
     */
    protected function _load_classes($class_name) {
        $exploded = explode('\\', $class_name);
//        echo '<pre>';
//        print_r(explode('\\', $class_name));
//        echo '</pre>';

        $namespace = $exploded[0] . '\\';

        $filename = str_replace($namespace, '', $class_name);
        $filename = str_replace('\\', '/', $filename);
        $filename = str_replace('_', '-', $filename) . '.php';

        $path = $this->_plugin_dir_path . strtolower($filename);

        if (file_exists($path)) {
//            echo '<pre>';
//            print_r($path);
//            echo '</pre>';
            include($path);
        }
    }

}
