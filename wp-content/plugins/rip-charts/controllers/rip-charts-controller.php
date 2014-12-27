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
        $results = $dao->get_all_charts();

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => count($results),
            'pages' => 1,
            'charts' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve a single post from 'Charts' custom post type.
     */
    public function get_chart_by_slug() {
        $slug = $this->_request->query->get('slug');

        if (empty($slug)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify an author slug'
            ));
        }

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $results = $dao->get_chart_by_slug($slug);

        if (empty($results)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'chart' => $results,
        ));
    }

    /**
     * Return the number of all charts
     * and the number of total pages. 
     * Used for client side pagination.
     */
    public function get_complete_charts_number_of_pages() {
        $slug = $this->_request->query->get('slug');

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $results = $dao->get_complete_charts_number_of_pages($slug);

        if (empty($results)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Pages not found'
            ));
        }

        $this->_response->to_json(array(
            'number_of_pages' => $results
        ));
    }

    /**
     * Return a list of all complete charts, 
     * ordered by date.
     */
    public function get_all_complete_charts() {
        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();

        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');

        $results = $dao->set_items_per_page($count)->get_all_complete_charts($page);
        $pages = $dao->get_complete_charts_number_of_pages();

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'complete_charts' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return a list of complete charts, 
     * ordered by date and grouped by genre
     */
    public function get_last_complete_charts_per_genre() {
        $count = $this->_request->query->get('count');

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $results = $dao->set_items_per_page($count)->get_last_complete_charts_per_genre();

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) count($results),
            'pages' => 1,
            'complete_charts' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return a list of all complete chart of a specific chart, 
     * specifing the slug of the chart. 
     */
    public function get_all_complete_charts_by_chart_genre() {
        $genre = $this->_request->query->get('genre');
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');

        if (empty($genre)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a chart genre'
            ));
        }

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();

        $results = $dao->set_items_per_page($count)
                ->get_all_complete_charts_by_chart_genre($genre, $page);
        $pages = $dao->get_complete_charts_number_of_pages($genre);

        $this->_response->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'complete_charts' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return a complete chart,
     * with all realtive songs.
     */
    public function get_complete_chart_by_chart_archive_slug() {
        $slug = $this->_request->query->get('slug');

        if (empty($slug)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a chart archive slug'
            ));
        }

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $results = $dao->get_complete_chart_by_chart_archive_slug($slug);

        if (empty($results)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $this->_response->to_json(array(
            'status' => 'ok',
            'complete_chart' => $results,
        ));
    }

    /**
     * Insert a new complete chart.
     */
    public function insert_complete_chart() {
        $complete_chart = stripslashes_deep($this->_request->request->get('complete_chart'));

        if (empty($complete_chart)) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify chart data to persists'
            ));
        }

        if (empty($complete_chart['songs'])) {
            return $this->_response->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify at least five songs'
            ));
        }

        $dao = new \Rip_Charts\Daos\Rip_Charts_Dao();

        $results = $dao->insert_complete_chart($complete_chart);
        $this->_response->to_json($results);
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

        $results = $dao->update_complete_chart($complete_chart);
        $this->_response->to_json($results);
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
        $results = $dao->delete_complete_chart($slug);

        if ((int) $results === 0) {
            return $this->_response->set_code(412)->to_json(array(
                        'status' => 'error',
                        'message' => 'Resource not exists'
            ));
        }

        $this->_response->set_code(200)->to_json(array(
            'status' => 'ok',
            'message' => 'Resource successfully deleted'
        ));
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

        $results = $dao->duplicate_complete_chart($slug);
        
        if ($results['status'] === 'error') {
            return $this->_response->set_code(412)->to_json(array(
                        'status' => 'error',
                        'message' => 'You can duplicate a chart of the same type max one time a day'
            ));
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
        $chart_dao = new \Rip_Charts\Daos\Rip_Charts_Dao();
        $social_user_dao = new \Rip_Social_Users\Daos\Rip_Social_Users_Dao();

        $chart_archive_slug = $this->_request->request->get('chart_archive_slug');
        $id_song = $this->_request->request->get('id_song');

        $service = new \Rip_Charts\Services\Rip_Charts_Service($chart_dao, $social_user_dao);
        $can_vote = $service->check_if_user_can_vote($chart_archive_slug, $id_song);

        if ($can_vote['status'] === 'error') {
            return $this->_response->set_code(400)->to_json($can_vote);
        }

        $results = $chart_dao->insert_complete_chart_vote(array(
            'chart_archive_slug' => $chart_archive_slug,
            'id_song' => $id_song,
        ));

        $this->_response->to_json($results);
    }

}
