<?php

/**
 * Social Users Front Controller.
 */
class rip_social_users_ajax_front_controller {

    /**
     * Return a user, given its unique email.
     * 
     * @return type
     */
    public static function get_social_user_by_email() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();
        $email = $request->query->get('email');

        if (empty($email)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a user email'
            ));
        }

        $dao = new rip_social_users_dao();
        $results = $dao->get_social_user_by_email($email);

        if (empty($results)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        return $json_helper->to_json(array(
                    'status' => 'ok',
                    'user' => $results
        ));
    }

    /**
     * Return a user, given its uuid.
     * 
     * @return type
     */
    public static function get_social_user_by_uuid() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();
        $uuid = $request->query->get('uuid');

        if (empty($uuid)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a valid uuid'
            ));
        }

        $dao = new rip_social_users_dao();
        $results = $dao->get_social_user_by_uuid($uuid);

        if (empty($results)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        return $json_helper->to_json(array(
                    'status' => 'ok',
                    'user' => $results
        ));
    }

    /**
     * Persist a user to the db.
     * 
     * @return string
     */
    public static function insert_social_user() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $user = stripslashes_deep($request->request->get('user'));

        if (empty($user)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a user'
            ));
        }

        $dao = new rip_social_users_dao();
        $results = $dao->insert_social_user($user);

        $json_helper->to_json($results);
    }

}
