<?php

namespace Rip_Charts\Services;

/**
 * A service used by other Chart plugin's classes
 * to implement and run chart's business logic.
 */
class Rip_Charts_Persist_Service {

    /**
     * An object used to open db
     * transaction.
     * 
     * @var Object 
     */
    protected $_transaction;

    /**
     * Holds a reference to Chart DAO.
     * 
     * @var Object 
     */
    protected $_charts_dao;

    /**
     * Class constructor.
     */
    public function __construct(\Rip_General\Classes\Rip_Abstract_Dao $charts_dao) {
        $this->_charts_dao = $charts_dao;
        $this->_transaction = new \Rip_General\Classes\Rip_Transaction();
    }

    /**
     * First insert a chart's record into wp_chart_archive, (that store all archive about chart)
     * Then insert all chart's songs into wp_charts_songs.
     * Accept as parameter an array of rows to insert in a transaction. 
     * Each row specify: the chart archive slug, the id_chart, and the id_song
     * Return the last inserted item.
     * 
     * @param array $data
     * @return array
     */
    public function insert_complete_chart(array $data = array()) {
        if (empty($data['chart_archive_slug'])) {
            return array(
                'status' => 'error',
                'message' => 'Please specify a chart_archive_slug'
            );
        }

        $data = stripslashes_deep($data);
        $this->_transaction->start();
        $chart_result = $this->_charts_dao->insert_complete_chart($data);

        if ($chart_result === false) {
            $this->_transaction->rollback();

            return array(
                'status' => 'error',
                'type' => 'duplicate',
                'message' => 'Error in inserting to wp_chart_archive. Probably the chart is already presents'
            );
        }

        // Insert 
        // all songs.
        foreach ($data['songs'] as $song) {
            if (empty($data['chart_archive_slug']) || empty($data['id_chart']) || empty($song['id_song'])) {
                $this->_transaction->rollback();

                return array(
                    'status' => 'error',
                    'message' => 'Missing parameter required for inserting.'
                );
            }

            $song_result = $this->_charts_dao->insert_chart_song(
                    $data['chart_archive_slug'], (int) $data['id_chart'], (int) $song['id_song']
            );

            if ($song_result === false) {
                $this->_transaction->rollback();

                return array(
                    'status' => 'error',
                    'message' => 'Error in inserting song in wp_chart_songs'
                );
            }
        }

        $this->_transaction->commit();
        $chart = $this->_charts_dao->get_complete_chart_by_chart_archive_slug($data['chart_archive_slug']);

        return $chart;
    }

    /**
     * Update each row of a complete chart.
     * A complete chart is made by 5, 10, 20, or 50 songs with the same chart_archive_slug.
     * 
     * IMPORTANT: 
     * Each row id must be specified to perform the update in a transaction.
     * The row id is passed AS id_chart_song
     * 
     * @param array $data
     * @return int (the number of the affected row).
     */
    public function update_complete_chart(array $data = array()) {
        if (empty($data)) {
            return array(
                'status' => 'error',
                'message' => 'No complete chart data was supplied',
            );
        }

        $this->_transaction->start();

        foreach ($data['songs'] as $item) {
            $result = $this->_charts_dao->update_chart_song(
                    (int) $item['id_chart_song'], $data['chart_archive_slug'], (int) $item['id_song'], (int) $item['user_vote']
            );

            if ($result === false) {
                $this->_transaction->rollback();

                return array(
                    'status' => 'error',
                    'message' => 'Error in updating the complete charts into wp_charts_songs'
                );
            }
        }

        $this->_transaction->commit();
        $chart = $this->_charts_dao->get_complete_chart_by_chart_archive_slug($data['chart_archive_slug']);

        return $chart;
    }

    /**
     * Duplicate a complete chart, retrieved by it's unique chart_archive_slug.
     * Return the duplicated chart.
     * 
     * @param string $slug
     * @return array
     */
    public function duplicate_complete_chart($slug = null) {
        $data = $this->_charts_dao->get_complete_chart_by_chart_archive_slug($slug);

        if (empty($data)) {
            return array(
                'status' => 'error',
                'message' => 'Cannot duplicate. Please specify a chart to duplicate.'
            );
        }

        // Set the new chart archive slug.
        // If already present @insert_complete_chart will not perform the insert.
        $data['chart_archive_slug'] = $data['chart_slug'] . '-' . $date = date('Y-m-d', time());

        $results = $this->insert_complete_chart($data);

        return $results;
    }

    /**
     * Delete a complete chart from wp_charts_archive table,
     * given it's chart archive slug.
     * 
     * IMPORTANT: all row in wp_charts_songs are automatically deleted
     * cause the foreign key chart_archive_slug.
     * 
     * @param string $slug
     * @return type
     */
    public function delete_complete_chart($slug) {
        $this->_transaction->start();
        $result = $this->_charts_dao->delete_complete_chart($slug);

        // Return success
        // to mantain delete idempotent.
        if ($result === 0) {
            return array(
                'status' => 'succes',
                'message' => 'Resource does not exists'
            );
        }

        if ($result === false) {
            $this->_transaction->rollback();

            return array(
                'status' => 'error',
                'message' => 'Error in deleting the complete charts'
            );
        }

        $this->_transaction->commit();

        return $result;
    }

    /**
     * Insert a user vote on wp_charts_songs_vote
     * 
     * @param array $data
     * @return array
     */
    public function insert_complete_chart_vote($chart_archive_slug = null, $id_song = null) {
        // If no chart archive slug is passed,
        // retrieve the last chart where the song is present.
        if (empty($chart_archive_slug)) {
            $chart = $this->_charts_dao->get_last_complete_chart_by_song_id($id_song);
        } else {
            $chart = $this->_charts_dao->get_complete_chart_by_chart_archive_slug($chart_archive_slug);
        }

        $validator = new \Rip_Charts\Services\Rip_Charts_Vote_Validator();
        $can_vote  = $validator->check_if_chart_has_song($chart, $id_song);
        
        if (isset($can_vote['status']) && $can_vote['status'] === 'error') {
            return $can_vote;
        }

        $this->_transaction->start();

        $result = $this->_charts_dao->insert_complete_chart_vote(
                $chart['chart_archive_slug'], (int) $id_song
        );

        if ($result === false) {
            $this->_transaction->rollback();

            return array(
                'status' => 'error',
                'type' => 'duplicate',
                'message' => 'Error in inserting to wp_charst_songs_vote. Probably already voted'
            );
        }

        $this->_transaction->commit();

        return array(
            'status' => 'ok',
            'message' => 'Vote insertion was successfull'
        );
    }

}
