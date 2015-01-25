<?php

namespace Rip_General\Dto;

class Message implements \JsonSerializable {

    public $status;
    public $code;
    public $message;

//    public function __get($name) {
//        return isset($this->$name) ? $this->$name : null;
//    }
//
//    public function __set($name, $value) {
//        $this->$name = $value;
//    }

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

    public function get_status() {
        return $this->status;
    }

    public function get_code() {
        return $this->code;
    }

    public function get_message() {
        return $this->message;
    }

    public function set_status($status) {
        $this->status = $status;
        return $this;
    }

    public function set_code($code) {
        $this->code = $code;
        return $this;
    }

    public function set_message($message) {
        $this->message = $message;
        return $this;
    }

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
