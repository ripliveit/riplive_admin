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
        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Query_Service($dao);
        $result = $service->get_latest_complete_charts();

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
        $complete_chart = $this->_request->request->get('complete_chart');

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Persist_Service($dao);
        $result = $service->insert_complete_chart($complete_chart);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Update complete chart.
     */
    public function update_complete_chart() {
        $complete_chart = $this->_request->request->get('complete_chart');

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Persist_Service($dao);
        $result = $service->update_complete_chart($complete_chart);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Delete a complete chart.
     */
    public function delete_complete_chart() {
        $slug = $this->_request->query->get('slug');

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Persist_Service($dao);
        $result = $service->delete_complete_chart($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Duplicate a complete chart.
     */
    public function duplicate_complete_chart() {
        $slug = $this->_request->query->get('slug');

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $service = new \Rip_Charts\Services\Rip_Charts_Persist_Service($dao);
        $result = $service->duplicate_complete_chart($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
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

        $this->_response->to_json($result);
    }

}
