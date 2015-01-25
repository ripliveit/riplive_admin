<?php

namespace Rip_Charts\Controllers;

/**
 * Charts controller.
 * Implements method invoked by ajax method 
 * to retrieve chart's data.
 */
class Rip_Charts_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Retrieve all posts from 'Charts' custom post type.
     */
    public function get_all_charts() {
        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Query_Service($dao);
        $result = $service->get_all_charts();

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Retrieve a single post from 'Charts' custom post type.
     */
    public function get_chart_by_slug() {
        $slug = $this->_request->query->get('slug');

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Query_Service($dao);
        $result = $service->get_chart_by_slug($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return the number of all charts
     * and the number of total pages. 
     * Used for client side pagination.
     */
    public function get_complete_charts_number_of_pages() {
        $slug = $this->_request->query->get('slug');

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Query_Service($dao);
        $result = $service->get_complete_charts_number_of_pages($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return a list of all complete charts, 
     * ordered by date.
     */
    public function get_all_complete_charts() {
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Query_Service($dao);
        $result = $service->get_all_complete_charts($count, $page);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return a list of all complete chart of a specific chart, 
     * specifing the slug of the chart. 
     */
    public function get_all_complete_charts_by_chart_type() {
        $slug = $this->_request->query->get('slug');
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Query_Service($dao);
        $result = $service->get_all_complete_charts_by_chart_type($slug, $count, $page);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return lasts complete charts,
     * one per genre.
     */
    public function get_latest_complete_charts() {
        $count = $this->_request->query->get('count');

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Query_Service($dao);
        $result = $service->get_latest_complete_charts($count);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return a complete chart,
     * with all realtive songs.
     */
    public function get_complete_chart_by_chart_archive_slug() {
        $slug = $this->_request->query->get('slug');

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Query_Service($dao);
        $result = $service->get_complete_chart_by_chart_archive_slug($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Insert a new complete chart.
     */
    public function insert_complete_chart() {
        $complete_chart = stripslashes_deep($this->_request->request->get('complete_chart'));

        if (empty($complete_chart)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify chart data to persists'
            ));
        }

        if (empty($complete_chart['songs'])) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify at least five songs'
            ));
        }

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Persist_Service($dao);
        $result = $service->insert_complete_chart($complete_chart);

        if (isset($result['status']) && $result['status'] === 'error') {
            return $this->_response->set_code(500)->to_json($result);
        }

        $this->_response->to_json($result);
    }

    /**
     * Update complete chart.
     */
    public function update_complete_chart() {
        $complete_chart = stripslashes_deep($this->_request->request->get('complete_chart'));

        if (empty($complete_chart)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify chart informations'
            ));
        }

        if (empty($complete_chart['songs'])) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify at least five songs'
            ));
        }

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Persist_Service($dao);
        $result = $service->update_complete_chart($complete_chart);

        if (isset($result['status']) && $result['status'] === 'error') {
            return $this->_response->set_code(500)->to_json($result);
        }

        $this->_response->to_json($result);
    }

    /**
     * Delete a complete chart.
     */
    public function delete_complete_chart() {
        $slug = $this->_request->query->get('slug');

        if (empty($slug)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a chart archive slug'
            ));
        }

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Persist_Service($dao);
        $result = $service->delete_complete_chart($slug);

        if (isset($result['status']) && $result['status'] === 'error') {
            return $this->_response->set_code(500)->to_json($result);
        }

        $this->_response->set_code(204)->to_json($result);
    }

    /**
     * Duplicate a complete chart.
     */
    public function duplicate_complete_chart() {
        $slug = $this->_request->query->get('slug');

        if (empty($slug)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a chart archive slug'
            ));
        }

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Persist_Service($dao);
        $results = $service->duplicate_complete_chart($slug);

        if (isset($results['status']) && $results['status'] === 'error') {
            return $this->_response->set_code(500)->to_json($results);
        }

        $this->_response->to_json($results);
    }

    /**
     * Insert a user vote 
     * 
     * @param array $data
     * @return array
     */
    public function insert_complete_chart_vote() {
        $chart_archive_slug = $this->_request->request->get('chart_archive_slug');
        $id_song = $this->_request->request->get('id_song');

        $chart_dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Persist_Service($chart_dao);
        $result = $service->insert_complete_chart_vote($chart_archive_slug, $id_song);

        if (isset($result['status']) && $result['status'] === 'error') {
            return $this->_response->set_code(400)->to_json($result);
        }

        $this->_response->to_json($result);
    }

}
