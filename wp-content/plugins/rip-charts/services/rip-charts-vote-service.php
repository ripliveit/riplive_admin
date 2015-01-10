<?php

namespace Rip_Charts\Services;

/**
 * A service used by other Chart plugin's classes
 * to implement and run chart's business logic.
 */
class Rip_Charts_Vote_Service {

    /**
     * Holds a reference to Chart DAO.
     * 
     * @var Object 
     */
    protected $_charts_dao;

    /**
     * Class constructor.
     */
    public function __construct(
    \Rip_General\Classes\Rip_Abstract_Dao $charts_dao
    ) {
        $this->_charts_dao = $charts_dao;
    }

    /**
     * Check if the vote's data are valid. 
     * Check the existence of the chart, and of the associated song.
     * Return error if one of these value are not valid
     * or empty.
     * Return true otherwise.
     * 
     * @param string $chart_archive_slug
     * @param int $id_song
     * @return boolean
     */
    public function check_if_user_can_vote($chart_archive_slug = null, $id_song = null) {
        // Check 
        // all params.
        if (empty($id_song)) {
            return array(
                'status' => 'error',
                'message' => 'Please specify an id song'
            );
        }
        
        // If no chart archive slug is passed,
        // retrieve the last chart where the song is present.
        if (empty($chart_archive_slug)) {
           $chart = $this->_charts_dao->get_last_complete_chart_by_song_id($id_song);
        } else {
            $chart = $this->_charts_dao->get_complete_chart_by_chart_archive_slug($chart_archive_slug);
        }

        if (empty($chart)) {
            return array(
                'status' => 'error',
                'message' => 'The chart does not exists'
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
