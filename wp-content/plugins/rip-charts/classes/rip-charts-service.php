<?php

/**
 * A service used by other Chart plugin's class
 * to implement and run char'ts business logic.
 */
class rip_charts_service {

    /**
     * Holds a reference to Chart DAO.
     * 
     * @var Object 
     */
    protected $_charts_dao;

    /**
     * Holds a reference to Social User DAO.
     * 
     * @var Object 
     */
    protected $_social_users_dao;

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->_charts_dao = new rip_charts_dao;
        $this->_social_users_dao = new rip_social_users_dao();
    }

    /**
     * Check if the vote's data
     * are valid. 
     * Check the existence of the chart, of the associated song, and of
     * the user. Return error if one of these value are not valid
     * or empty.
     * Return true otherwise.
     * 
     * @param string $chart_archive_slug
     * @param int $id_song
     * @param string $uuid_user
     * @param string $username
     * @return boolean
     */
    public function check_if_user_can_vote($chart_archive_slug = null, $id_song = null, $uuid_user = null, $username = null) {
        // Check 
        // all params.
        if (empty($chart_archive_slug)) {
            return array(
                'status' => 'error',
                'message' => 'Please specify a chart archive slug'
            );
        }

        if (empty($id_song)) {
            return array(
                'status' => 'error',
                'message' => 'Please specify an id song'
            );
        }

//        if (empty($username)) {
//            return array(
//                'status' => 'error',
//                'message' => 'Please specify a username'
//            );
//        }
//
//        if (empty($uuid_user)) {
//            return array(
//                'status' => 'error',
//                'message' => 'Please specify a user uuid'
//            );
//        }

        // Check in a chart with the
        // passed chart_archive_exists.
        $chart = $this->_charts_dao->get_complete_chart_by_chart_archive_slug($chart_archive_slug);

        if (empty($chart)) {
            return array(
                'status' => 'error',
                'message' => 'Invalid chart archive slug'
            );
        }

        // Check if a song with
        // the passed id_song exists.
        $song_ids = array();

        foreach ($chart['songs'] as $song) {
            array_push($song_ids, $song['id_song']);
        }

        if (!in_array($id_song, $song_ids)) {
            return array(
                'status' => 'error',
                'message' => 'Invalid id song'
            );
        }

        // Check if
        // a user with the passed uuid exists.
//        $user = $this->_social_users_dao->get_social_user_by_uuid($uuid_user);
//
//        if (empty($user)) {
//            return array(
//                'status' => 'error',
//                'message' => 'Invalid user uuid'
//            );
//        }
//   
//        if ($user['username'] !== $username) {
//            return array(
//                'status' => 'error',
//                'message' => 'Invalid username'
//            );
//        }

        return true;
    }

}