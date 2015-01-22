<?php

namespace Rip_Charts\Services;

/**
 * A service used by other Chart plugin's classes
 * to implement and run chart's business logic.
 */
class Rip_Charts_Vote_Validator {

    /**
     * Check if a chart has
     * a specific song.
     * 
     * @param int $id_song
     * @param array $chart
     * @return boolean
     */
    public function check_if_chart_has_song(array $chart = array(), $id_song = null) {
        if (empty($chart)) {
            return array(
                'status' => 'error',
                'message' => 'Please pass a chart to check'
            );
        }

        if (empty($id_song)) {
            return array(
                'status' => 'error',
                'message' => 'Please specify an id song'
            );
        }

        // Check if the chart
        // has the specified song.
        $song_ids = array();

        foreach ($chart['songs'] as $song) {
            array_push($song_ids, $song['id_song']);
        }

        if (!in_array($id_song, $song_ids)) {
            return array(
                'status' => 'error',
                'message' => 'Invalid id song: the song is not in chart'
            );
        }

        return true;
    }

}
