<?php
namespace Rip_General\Services;

/**
 * A service that helps all plugin's DAO to handle business logic.
 */
class Rip_General_Service {

    /**
     * Divide all songs in alphabetical order,
     * grouping them by the first song's title character.
     * 
     * @param array $songs
     * @return array
     */
    public function divide_data_by_letter($field, array $array = array()) {
        if (empty($array)) {
            return false;
        }

        $tmp = array();

        // Cycle all songs and set and push 
        // to the temporary array.
        foreach ($array as $key => $data) {
            if (!array_key_exists($field, $data)) {
                throw new Exception('Field used to divide the data not exists');
                return false;
            }
            
            $letter = $data[$field][0];

            if (!array_key_exists($letter, $tmp)) {
                $tmp[$letter] = array();
            }

            array_push($tmp[$letter], $data);
        }

        return $tmp;
    }
}