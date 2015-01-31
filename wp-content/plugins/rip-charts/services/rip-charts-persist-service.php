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
     * First insert the chart into wp_chart_archive, that store all complete chart,
     * then insert all songs into wp_charts_songs.
     * 
     * Accept an array of rows as parameter. 
     * Each row specify: the chart archive slug, the id_chart, and the id_song
     * Return the inserted chart.
     * 
     * @param array $chart
     * @return array
     */
    public function insert_complete_chart(array $chart = array()) {
        $message = new \Rip_General\Dto\Message();
        $query_service = new \Rip_Charts\Services\Rip_Charts_Query_Service($this->_charts_dao);

        if (empty($chart)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify chart data to persists');

            return $message;
        }

        if (empty($chart['songs'])) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify at least five songs to insert');

            return $message;
        }

        if (empty($chart['chart_archive_slug'])) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a chart_archive_slug');

            return $message;
        }

        $data = stripslashes_deep($chart);
        $this->_transaction->start();
        $chart_result = $this->_charts_dao->insert_complete_chart($data);

        if ($chart_result === false) {
            $this->_transaction->rollback();
            
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Error in inserting the new chart. Probably it is already presents');
            
            return $message;
        }

        // Insert 
        // all songs.
        foreach ($data['songs'] as $song) {
            if (empty($data['chart_archive_slug']) || empty($data['id_chart']) || empty($song['id_song'])) {
                $this->_transaction->rollback();
                
                $message->set_code(400)
                        ->set_status('error')
                        ->set_message('Missing parameter required for inserting');
                
                return $message;
            }

            $song_result = $this->_charts_dao->insert_chart_song(
                    $data['chart_archive_slug'], (int) $data['id_chart'], (int) $song['id_song']
            );

            if ($song_result === false) {
                $this->_transaction->rollback();
                
                $message->set_code(500)
                        ->set_status('error')
                        ->set_message('Error in inserting songs into wp_chart_songs');
                
                return $message;
            }
        }

        $this->_transaction->commit();
        $chart = $query_service->get_complete_chart_by_chart_archive_slug($data['chart_archive_slug']);

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
        $message = new \Rip_General\Dto\Message();
        $query_service = new \Rip_Charts\Services\Rip_Charts_Query_Service($this->_charts_dao);
        $validator = new \Rip_Charts\Services\Rip_Charts_Vote_Validator();

        // If no chart archive slug is passed,
        // retrieve the last chart where the song is present.
        if (empty($chart_archive_slug)) {
            $data = $query_service->get_last_complete_chart_by_song_id($id_song);
        } else {
            $data = $query_service->get_complete_chart_by_chart_archive_slug($chart_archive_slug);
        }

        $chart = $data->get_complete_chart();
        $can_vote = $validator->check_if_chart_has_song($chart, $id_song);

        if (isset($can_vote->status) && $can_vote->status === 'error') {
            $can_vote->set_code(400);
            return $can_vote;
        }

        $this->_transaction->start();
        $result = $this->_charts_dao->insert_complete_chart_vote(
                $chart['chart_archive_slug'], (int) $id_song
        );

        if ($result === false) {
            $this->_transaction->rollback();

            return $message->set_code(500)
                            ->set_status('error')
                            ->set_message('Error in inserting to wp_charst_songs_vote.');
        }

        $this->_transaction->commit();

        return $message->set_code(200)
                        ->set_status('ok')
                        ->set_message('Vote insertion was successfull');
    }

}
