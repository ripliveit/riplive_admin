<?php

namespace Rip_General\Classes;


/**
 * An Abstract validator
 * that all concrete validator must respect.
 *
 * @author Gabriele
 */
abstract class Rip_Abstract_Validator {
    abstract function validate();
}
