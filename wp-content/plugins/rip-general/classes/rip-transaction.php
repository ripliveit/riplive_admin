<?php

namespace Rip_General\Classes;

class Rip_Transaction {
    
    /**
     * Holds a reference
     * to the db reference.
     * 
     * @var Object 
     */
    private $_db;


    /**
     * On construction 
     * set the db.
     * 
     * @global Object $wpdb
     */
    public function __construct() {
        global $wpdb;

        $this->_db = $wpdb;
    }
    
    /**
     * Start the transtaction.
     * 
     * @return int
     */
    public function start() {
        return $this->_db->query('START TRANSACTION');
    }
    
    /**
     * Commit the transaction.
     * 
     * @return int
     */
    public function commit() {
        return $this->_db->query('COMMIT');
    }
    
    /**
     * Rollback the transtaction.
     * 
     * @return int
     */
    public function rollback() {
        return $this->_db->query('ROLLBACK');
    }

}
