<?php

/**
 * Charts ajax front controller.
 * Implements method invoked by ajax method to retrieve chart's data.
 */
class rip_charts_ajax_front_controller {

    /**
     * Retrieve all posts from 'Charts' custom post type.
     */
    public static function get_all_charts() {
        $dao = new rip_charts_dao();
        $json_helper = rip_general_json_helper::get_instance();

        $results = $dao->get_all_charts();

        $json_helper->to_json(array(
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
    public static function get_chart_by_slug() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify an author slug'
            ));
        }

        $dao = new rip_charts_dao();
        $results = $dao->get_chart_by_slug($slug);

        if (empty($results)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'chart' => $results,
        ));
    }

    /**
     * Return the number of all charts
     * and the number of page. 
     * Used for client side pagination.
     */
    public static function get_complete_charts_number_of_pages() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');

        $dao = new rip_charts_dao();
        $results = $dao->get_complete_charts_number_of_pages($slug);

        if (empty($results)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Pages not found'
            ));
        }

        $json_helper->to_json(array(
            'number_of_pages' => $results
        ));
    }

    /**
     * Return a list of all complete charts, 
     * ordered by date.
     */
    public static function get_all_complete_charts() {
        $dao = new rip_charts_dao();
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $count = $request->query->get('count');
        $page = $request->query->get('page');

        $results = $dao->set_items_per_page($count)->get_all_complete_charts($page);
        $pages = $dao->get_complete_charts_number_of_pages();

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'complete_charts' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Return a list of all complete chart of a specific chart, 
     * specifing the slug of the chart. 
     */
    public static function get_all_complete_charts_by_chart_slug() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');
        $count = $request->query->get('count');
        $page = $request->query->get('page');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify an author slug'
            ));
        }

        $dao = new rip_charts_dao();

        $results = $dao->set_items_per_page($count)->get_all_complete_charts_by_chart_slug($slug, $page);
        $pages = $dao->get_complete_charts_number_of_pages($slug);



        $json_helper->to_json(array(
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
    public static function get_complete_chart_by_chart_archive_slug() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a chart archive slug'
            ));
        }

        $dao = new rip_charts_dao();
        $results = $dao->get_complete_chart_by_chart_archive_slug($slug);

        if (empty($results)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        $json_helper->to_json(array(
            'status' => 'ok',
            'complete_chart' => $results,
        ));
    }

    /**
     * Insert a new complete chart.
     */
    public static function insert_complete_chart() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();
        $complete_chart = stripslashes_deep($request->request->get('complete_chart'));

        if (empty($complete_chart)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify chart informations'
            ));
        }

        if (empty($complete_chart['songs'])) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify at least five songs'
            ));
        }

        $dao = new rip_charts_dao();
        $results = $dao->insert_complete_chart($complete_chart);
        $json_helper->to_json($results);
    }

    /**
     * Update complete chart.
     */
    public static function update_complete_chart() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();
        $complete_chart = stripslashes_deep($request->request->get('complete_chart'));

        if (empty($complete_chart)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify chart informations'
            ));
        }

        if (empty($complete_chart['songs'])) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify at least five songs'
            ));
        }

        $dao = new rip_charts_dao();
        $results = $dao->update_complete_chart($complete_chart);
        $json_helper->to_json($results);
    }

    /**
     * Delete a complete chart.
     */
    public static function delete_complete_chart() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a chart archive slug'
            ));
        }

        $dao = new rip_charts_dao();
        $results = $dao->delete_complete_chart($slug);
        $json_helper->to_json($results);
    }

    /**
     * Duplicate a complete chart.
     */
    public static function duplicate_complete_chart() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a chart archive slug'
            ));
        }

        $dao = new rip_charts_dao();
        $results = $dao->duplicate_complete_chart($slug);
        $json_helper->to_json($results);
    }

    /**
     * Insert a user vote 
     * 
     * @param array $data
     * @return array
     */
    public static function insert_complete_chart_vote() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $chart_archive_slug = $request->request->get('chart_archive_slug');
        $id_song = $request->request->get('id_song');
        
        // $uuid_user = $request->request->get('uuid_user');
        // $username = $request->request->get('username');

        $service = new rip_charts_service();
        $can_vote = $service->check_if_user_can_vote($chart_archive_slug, $id_song, $uuid_user, $username);
                
        if ($can_vote['status'] === 'error') {
            return $json_helper->to_json($can_vote);
        } 

        $dao = new rip_charts_dao();

        $results = $dao->insert_complete_chart_vote(array(
            'chart_archive_slug' => $chart_archive_slug,
            'id_song' => $id_song,
        //    'uuid_user' => $uuid_user,
        //    'username' => $username,
        ));

        $json_helper->to_json($results);
    }

}