<?php

namespace Rip_Charts\Services;

/**
 * A service used by other Chart plugin's classes
 * to implement and run chart's business logic.
 */
class Rip_Charts_Persist_Service {

    /**
     * Holds a reference to Complete Charts Dao.
     * 
     * @var Object 
     */
    private $_complete_charts_dao;

    /**
     * Holds a reference to Chart Query Service.
     * 
     * @var Object 
     */
    private $_query_service;

    /**
     * Holds a reference to Chart Vote Validator
     * 
     * @var Object 
     */
    private $_validator;

    /**
     * An object used to open db
     * transaction.
     * 
     * @var Object 
     */
    private $_transaction;

    /**
     * Class constructor.
     */
    public function __construct(
            \Rip_General\Classes\Rip_Abstract_Dao $complete_charts_dao, 
            \Rip_General\Classes\Rip_Abstract_Query_Service $query_service, 
            \Rip_General\Classes\Rip_Abstract_Validator $validator, 
            \Rip_General\Classes\Rip_Transaction $transaction
    ) {
        $this->_complete_charts_dao = $complete_charts_dao;
        $this->_query_service = $query_service;
        $this->_validator = $validator;
        $this->_transaction = $transaction;
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
    public function insert_complete_chart($chart = array()) {
        $message = new \Rip_General\Dto\Message();

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
        $chart_result = $this->_complete_charts_dao->insert_complete_chart($data);

        if ($chart_result === false) {
            $this->_transaction->rollback();

            $message->set_code(500)
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

            $song_result = $this->_complete_charts_dao->insert_chart_song(
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
        $result = $this->_query_service->get_complete_chart_by_chart_archive_slug($data['chart_archive_slug']);

        return $result;
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
    public function update_complete_chart($chart = array()) {
        $message = new \Rip_General\Dto\Message();

        if (empty($chart)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('No chart data was supplied');

            return $message;
        }

        if (empty($chart['songs'])) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Cannot insert. Please specify at least five songs');

            return $message;
        }

        $data = stripslashes_deep($chart);
        $this->_transaction->start();

        foreach ($data['songs'] as $item) {
            $result = $this->_complete_charts_dao->update_chart_song(
                    (int) $item['id_chart_song'], $data['chart_archive_slug'], (int) $item['id_song'], (int) $item['user_vote']
            );

            if ($result === false) {
                $this->_transaction->rollback();

                $message->set_code(500)
                        ->set_status('error')
                        ->set_message('Error in updating the complete charts into wp_charts_songs');

                return $message;
            }
        }

        $this->_transaction->commit();
        $result = $this->_query_service->get_complete_chart_by_chart_archive_slug($data['chart_archive_slug']);

        return $result;
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
    public function delete_complete_chart($slug = null) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a chart archive slug');

            return $message;
        }

        $this->_transaction->start();
        $result = $this->_complete_charts_dao->delete_complete_chart($slug);

        if ($result === false) {
            $this->_transaction->rollback();

            $message->set_code(500)
                    ->set_status('error')
                    ->set_message('Error in deleting the complete charts');

            return $message;
        }

        // Return success
        // to mantain delete idempotent.
        if ($result === 0) {
            $message->set_code(200)
                    ->set_status('success')
                    ->set_message('Resource does not exists');

            return $message;
        }

        $this->_transaction->commit();

        $message->set_code(200)
                ->set_status('success')
                ->set_message('Resource successfully deleted');

        return $message;
    }

    /**
     * Duplicate a complete chart, retrieved by it's unique chart_archive_slug.
     * Return the duplicated chart.
     * 
     * @param string $slug
     * @return array
     */
    public function duplicate_complete_chart($slug = null) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a chart archive slug');

            return $message;
        }

        $data = $this->_query_service->get_complete_chart_by_chart_archive_slug($slug);
        $chart = $data->get_complete_chart();


        if (empty($chart)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Cannot find the chart to duplicate. Please specify a chart to duplicate');

            return $message;
        }

        // Set the new chart archive slug.
        // If already present @insert_complete_chart will not perform the insert.
        $chart['chart_archive_slug'] = $chart['chart_slug'] . '-' . date('Y-m-d', time());

        $result = $this->insert_complete_chart($chart);

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

        // If no chart archive slug is passed,
        // retrieve the last chart where the song is present.
        if (empty($chart_archive_slug)) {
            $data = $this->_query_service->get_last_complete_chart_by_song_id($id_song);
        } else {
            $data = $this->_query_service->get_complete_chart_by_chart_archive_slug($chart_archive_slug);
        }

        $chart = $data->get_complete_chart();
        $can_vote = $this->_validator->validate($chart, $id_song);

        if (isset($can_vote->status) && $can_vote->status === 'error') {
            $can_vote->set_code(400);
            return $can_vote;
        }

        $this->_transaction->start();
        $result = $this->_complete_charts_dao->insert_complete_chart_vote(
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
