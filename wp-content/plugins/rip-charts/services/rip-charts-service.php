<?php

namespace Rip_Charts\Services;

/**
 * A service used by other Chart plugin's classes
 * to implement and run chart's business logic.
 */
class Rip_Charts_Service {

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
    public function __construct(
        \Rip_General\Classes\Rip_Abstract_Dao $charts_dao,
        \Rip_General\Classes\Rip_Abstract_Dao $social_users_dao
    ) {
        $this->_charts_dao = $charts_dao;
        $this->_social_users_dao = $social_users_dao;
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
    public function check_if_user_can_vote($chart_archive_slug = null, $id_song = null) {
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

        return true;
    }

}
