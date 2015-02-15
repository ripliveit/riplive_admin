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
    private $_plugin_dir_path;

    /**
     * On class construction set current plugin directory tree.
     * 
     * @param string $path
     */
    public function __construct($path) {
        $this->_plugin_dir_path = plugin_dir_path($path);
        spl_autoload_register(array($this, '_load_classes'));
    }

    /**
     * Load required class.
     * 
     * @param type $class_name
     */
    private function _load_classes($class_name) {
        $filename = str_replace('\\', '/', $class_name);
        $filename = str_replace('_', '-', $filename) . '.php';

        $path = $this->_plugin_dir_path . strtolower($filename);

        if (file_exists($path)) {
            include($path);
        }
    }

}
