<?php

namespace Rip_Social_Users\Controllers;

/**
 * Social Users Front Controller.
 */
class Rip_Social_Users_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Return a user, given its unique email.
     * 
     * @return type
     */
    public function get_social_user_by_email() {
        $email = $this->_request->query->get('email');

        if (empty($email)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a user email'
            ));
        }

        $dao = new \Rip_Social_Users\Daos\Rip_Social_Users_Dao();
        $results = $dao->get_social_user_by_email($email);

        if (empty($results)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        return $this->_response->to_json(array(
                    'status' => 'ok',
                    'user' => $results
        ));
    }

    /**
     * Return a user, given its uuid.
     * 
     * @return type
     */
    public function get_social_user_by_uuid() {
        $uuid = $this->_request->query->get('uuid');

        if (empty($uuid)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a valid uuid'
            ));
        }

        $dao = new \Rip_Social_Users\Daos\Rip_Social_Users_Dao();
        $results = $dao->get_social_user_by_uuid($uuid);

        if (empty($results)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        return $this->_response->to_json(array(
                    'status' => 'ok',
                    'user' => $results
        ));
    }

    /**
     * Persist a user to the db.
     * 
     * @return string
     */
    public function insert_social_user() {
        $user = stripslashes_deep($this->_request->request->get('user'));

        if (empty($user)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a user'
            ));
        }

        $dao = new \Rip_Social_Users\Daos\Rip_Social_Users_Dao();
        $results = $dao->insert_social_user($user);

        $this->_response->to_json($results);
    }

}
