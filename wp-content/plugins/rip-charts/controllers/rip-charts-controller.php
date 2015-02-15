<?php

namespace Rip_Charts\Controllers;

/**
 * Songs Controller.
 * Each method, publicly available, return data in JSON format.
 */
class Rip_Charts_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {
    
    /**
     * On construction set the container.
     * 
     * @param \Rip_General\Classes\Rip_Http_Request $request
     * @param \Rip_General\Classes\Rip_Http_Response $response
     */
    public function __construct(\Rip_General\Classes\Rip_Http_Request $request, \Rip_General\Classes\Rip_Http_Response $response) {
        parent::__construct($request, $response);
        $this->_container = new \Rip_Charts\Services\Rip_Charts_Container();
    }

    /**
     * Retrieve all charts.
     */
    public function get_all_charts() {
        $service = $this->_container['chartsQueryService'];
        $result = $service->get_all_charts();

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Retrieve a single charts
     * with a specific slug.
     */
    public function get_chart_by_slug() {
        $slug = $this->_request->query->get('slug');

        $service = $this->_container['chartsQueryService'];
        $result = $service->get_chart_by_slug($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return the number of all charts
     * and the total number of pages. 
     * Used for client side pagination.
     */
    public function get_complete_charts_number_of_pages() {
        $slug = $this->_request->query->get('slug');

        $service = $this->_container['chartsQueryService'];
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

        $service = $this->_container['chartsQueryService'];
        $result = $service->get_all_complete_charts($count, $page);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return a list of all complete chart of a specific type
     * (for example all rock's chart and so on), 
     * specifing the slug of the type. 
     */
    public function get_all_complete_charts_by_chart_type() {
        $slug = $this->_request->query->get('slug');
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');

        $service = $this->_container['chartsQueryService'];
        $result = $service->get_all_complete_charts_by_chart_type($slug, $count, $page);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return latest complete charts,
     * one per genre (rock, electronic, pop and so on)
     */
    public function get_latest_complete_charts() {
        $service = $this->_container['chartsQueryService'];
        $result = $service->get_latest_complete_charts();

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Return a single complete chart,
     * with all realtive songs.
     */
    public function get_complete_chart_by_chart_archive_slug() {
        $slug = $this->_request->query->get('slug');

        $service = $this->_container['chartsQueryService'];
        $result = $service->get_complete_chart_by_chart_archive_slug($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Insert a new complete chart.
     */
    public function insert_complete_chart() {
        $complete_chart = $this->_request->request->get('complete_chart');

        $service = $this->_container['chartsPersistsService'];
        $result = $service->insert_complete_chart($complete_chart);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Update complete chart.
     */
    public function update_complete_chart() {
        $complete_chart = $this->_request->request->get('complete_chart');

        $service = $this->_container['chartsPersistsService'];
        $result = $service->update_complete_chart($complete_chart);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Delete a complete chart.
     */
    public function delete_complete_chart() {
        $slug = $this->_request->query->get('slug');

        $service = $this->_container['chartsPersistsService'];
        $result = $service->delete_complete_chart($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Duplicate a complete chart.
     */
    public function duplicate_complete_chart() {
        $slug = $this->_request->query->get('slug');

        $service = $this->_container['chartsPersistsService'];
        $result = $service->duplicate_complete_chart($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Insert a user vote 
     * 
     */
    public function insert_complete_chart_vote() {
        $chart_archive_slug = $this->_request->request->get('chart_archive_slug');
        $id_song = $this->_request->request->get('id_song');

        $service = $this->_container['chartsPersistsService'];
        $result = $service->insert_complete_chart_vote($chart_archive_slug, $id_song);

        $this->_response->to_json($result);
    }

}
