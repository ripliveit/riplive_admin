<?php

/**
 * Social Users Data Access Object
 */
class rip_social_users_dao extends rip_abstract_dao {

    /**
     * Retrieve and return a single user 
     * by its unique identifier.
     * 
     * @param int $id
     * @return array | false
     */
    public function get_social_user_by_id($id) {
        $wpdb = $this->get_db();

        $sql = "SELECT * 
                FROM wp_social_users
                WHERE id = %d";

        $prepared = $wpdb->prepare($sql, array(
            (int) $id
        ));

        $results = $wpdb->get_row($prepared, ARRAY_A);

        return $results;

        //return empty($podcasts_data) ? false : current($podcasts_data);
    }

    /**
     * Retrieve and return a single user 
     * by its unique email address.
     * 
     * @param string $email
     * @return array | false
     */
    public function get_social_user_by_email($email) {
        $wpdb = $this->get_db();

        $sql = "SELECT * 
                FROM wp_social_users
                WHERE email = %s";

        $prepared = $wpdb->prepare($sql, array(
            $email
        ));

        $results = $wpdb->get_row($prepared, ARRAY_A);

        return $results;
    }

    /**
     * Retrieve and return a single user 
     * by its unique uuid.
     * 
     * @param string $email
     * @return array | false
     */
    public function get_social_user_by_uuid($uuid) {
        $wpdb = $this->get_db();

        $sql = "SELECT * 
                FROM wp_social_users
                WHERE uuid = %s";

        $prepared = $wpdb->prepare($sql, array(
            $uuid
        ));

        $results = $wpdb->get_row($prepared, ARRAY_A);

        return $results;
    }

    /**
     * Persist a user into the db.
     * 
     * @param array $data
     * @return array | false
     */
    public function insert_social_user($data = array()) {
        $wpdb = $this->get_db();
        $wpdb->hide_errors();

        $wpdb->query('START TRANSACTION');

        $sql = "INSERT INTO wp_social_users(
                uuid,
                provider,
                email,
                username,
                display_name,
                user_info,
                registration_date
            )
            VALUES (%s, %s, %s, %s, %s, %s, %s)";

        $prepared = $wpdb->prepare($sql, array(
            $data['uuid'],
            $data['provider'],
            $data['email'],
            $data['username'],
            $data['display_name'],
            json_encode($data['user_info']),
            date('Y-m-d H:i:s', time()),
        ));

        if ($results = $wpdb->query($prepared) === false) {
            $wpdb->query('ROLLBACK');

            return array(
                'status' => 'error',
                'type' => 'duplicate',
                'message' => 'Error in inserting into wp_social_users. Probably the user is already presents.'
            );
        }

        //$results = $wpdb->query($prepared);

        $wpdb->query('COMMIT');

        $last_id = $wpdb->insert_id;

        $user = $this->get_social_user_by_id($last_id);

        return $user;
    }

}