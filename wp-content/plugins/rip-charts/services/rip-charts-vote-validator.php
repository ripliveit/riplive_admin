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
        $message = new \Rip_General\Dto\Message();
        
        if (empty($chart)) {
            return $message->set_status('error')
                    ->set_message('Please pass a chart to check');
        }

        if (empty($id_song)) {
            return $message->set_status('error')
                            ->set_message('Please specify an id song');
        }

        // Check if the chart
        // has the specified song.
        $song_ids = array();

        foreach ($chart['songs'] as $song) {
            array_push($song_ids, $song['id_song']);
        }

        if (!in_array($id_song, $song_ids)) {
            return $message->set_status('error')
                            ->set_message('Invalid id song: the song is not in chart');
        }

        return true;
    }

}
