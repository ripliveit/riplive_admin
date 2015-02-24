<?php

namespace Rip_General\Validators;

/**
 * Description of rip-posts-validator
 *
 * @author Gabriele
 */
class Rip_Posts_Validator extends \Rip_General\Classes\Rip_Abstract_Validator {

    private $_statuses;
    private $_default_post_status = 'publish';

    public function __construct() {
        $this->_statuses = get_post_statuses();
    }

    public function validate() {
        
    }

    public function validate_post_status($status) {
        if (empty($status)) {
            return $this->_default_post_status;
        }

        if (is_array($status)) {
            $status = array_values($status);
            $valid_statuses = array();

            foreach ($status as $value) {
                if (array_key_exists($value, $this->_statuses)) {
                    array_push($valid_statuses, $value);
                }
            }

            if (empty($valid_statuses)) {
                return $this->_default_post_status;
            }

            return $valid_statuses;
        }

        if (array_key_exists($status, $this->_statuses)) {
            return $status;
        }

        return $this->_default_post_status;
    }

}
