<?php

namespace Rip_General\Dto;

class Message implements \JsonSerializable {
    
    /**
     *
     * @var type 
     */
    public $status;
    
    /**
     *
     * @var type 
     */
    public $code;
    
    /**
     *
     * @var type 
     */
    public $message;
    
    /**
     * 
     * @param type $name
     * @param type $arguments
     * @return \Rip_General\Dto\Message
     */
    public function __call($name, $arguments) {
        $property = substr($name, 4);

        if (substr($name, 0, 4) === 'get_') {
            return isset($this->$property) ? $this->$property : null;
        } elseif (substr($name, 0, 4) === 'set_') {
            $value = (int) count($arguments) === 1 ? $arguments[0] : null;
            $this->$property = $value;
            
            return $this;
        }
    }
    
    /**
     * 
     * @return type
     */
    public function get_status() {
        return $this->status;
    }
    
    /**
     * 
     * @return type
     */
    public function get_code() {
        return $this->code;
    }

    public function get_message() {
        return $this->message;
    }
    
    /**
     * 
     * @param type $status
     * @return \Rip_General\Dto\Message
     */
    public function set_status($status) {
        $this->status = $status;
        return $this;
    }
    
    /**
     * 
     * @param type $code
     * @return \Rip_General\Dto\Message
     */
    public function set_code($code) {
        $this->code = $code;
        return $this;
    }
    
    /**
     * 
     * @param type $message
     * @return \Rip_General\Dto\Message
     */
    public function set_message($message) {
        $this->message = $message;
        return $this;
    }
    
    /**
     * 
     * @return \Rip_General\Dto\Message
     */
    public function jsonSerialize() {
        if ($this->status === 'error') {
            return array(
                'status' => $this->status,
                'code' => $this->code,
                'message' => $this->message
            );
        } else {
            return $this;
        }
    }

}
