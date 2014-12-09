<?php

/**
 * Plugin autoloader.
 * Autoload all required plugin's classes, scanning plugin's directory and 
 * subdirectory to inclued target class.
 * 
 * @author Gabriele D'Arrigo - @acirdesign.
 */
class rip_autoloader {

    /**
     * Plugin path.
     * 
     * @var type 
     */
    protected $_plugin_dir_path;

    /**
     * An array that store all plugin's folder and subfolder
     * 
     * @var type 
     */
    protected $_folders = array(
        '.',
        'classes',
        'services',
        'view-helpers',
        'metaboxes',
        'templates',
        'widgets'
    );

    /**
     * An array that is used to construct  plugin's directory tree.
     * 
     * @var type 
     */
    protected $_directory = array();

    /**
     * On class construction set current plugin directory tree.
     * 
     * @param type $plugin_dir_path
     */
    public function __construct($plugin_dir_path) {
        $this->_set_directories($plugin_dir_path);

        spl_autoload_register(array($this, '_load_classes'));
    }

    /**
     * Construct plugin directory tree.
     * 
     * @param type $plugin_dir_path
     */
    protected function _set_directories($plugin_dir_path) {
        foreach ($this->_folders as $folder_name) {
            $this->_directory[] = $plugin_dir_path . $folder_name;
        }
    }

    /**
     * Load required class.
     * 
     * @param type $class_name
     */
    protected function _load_classes($class_name) {
        foreach ($this->_directory as $key => $directory_name) {
            $filename = str_replace('_', '-', $class_name) . '.php';

            $path = $directory_name . '/' . $filename;

            if (file_exists($path)) {
                include($path);
            }
        }
    }
}