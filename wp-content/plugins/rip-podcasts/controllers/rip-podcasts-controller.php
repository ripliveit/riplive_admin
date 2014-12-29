<?php

/**
 * Chars ajax front controller.
 * Implements method invoked by ajax method to retrieve chart's data.
 */
class rip_podcasts_ajax_front_controller {

    /**
     * Return total number of podcast saved in wp_podcasts table.
     * If program's slug is passed as parameters, then return the total number
     * of podcasts for that particular program.
     */
    public static function get_podcasts_number_of_pages() {
        $dao = new rip_podcasts_dao();
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');
        $results = $dao->get_podcasts_number_of_pages($slug);

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
     * Retrieve all podcasts.
     */
    public static function get_all_podcasts() {
        $dao = new rip_podcasts_dao();
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $count = $request->query->get('count');
        $page = $request->query->get('page');

        $results = $dao->set_items_per_page($count)->get_all_podcasts($page);
        $pages = $dao->get_podcasts_number_of_pages();

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'podcasts' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve all podcasts with a specific program id.
     */
    public static function get_all_podcasts_by_program_slug() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');
        $count = $request->query->get('count');
        $page = $request->query->get('page');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a program slug'
            ));
        }

        $dao = new rip_podcasts_dao();
        $results = $dao->set_items_per_page($count)->get_all_podcasts_by_program_slug($slug, $page);

        // Load page number passing current podcast's program slug.
        $pages = $dao->get_podcasts_number_of_pages($slug);

        $json_helper->to_json(array(
            'status' => 'ok',
            'count' => count($results),
            'count_total' => (int) $pages['count_total'],
            'pages' => $pages['pages'],
            'podcasts' => empty($results) ? array() : $results,
        ));
    }

    /**
     * Retrieve a podcast by its unique identifier.
     */
    public static function get_podcast_by_id() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $id_podcast = $request->query->get('id_podcast');

        if (empty($id_podcast)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a podcast id'
            ));
        }

        $dao = new rip_podcasts_dao();
        $results = $dao->get_podcast_by_id((int) $id_podcast);

        if (empty($results)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        return $json_helper->to_json(array(
                    'status' => 'ok',
                    'podcast' => $results
        ));
    }

    /**
     * Insert a podcast.
     */
    public static function insert_podcast() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $podcast = stripslashes_deep($request->request->get('podcast'));

        if (empty($podcast)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a podcast'
            ));
        }

        $dao = new rip_podcasts_dao();
        $results = $dao->insert_podcast($podcast);

        $json_helper->to_json($results);
    }

    /**
     * Update a podcast.
     */
    public static function update_podcast() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        (int) $id_podcast = $request->query->get('id_podcast');
        $podcast = stripslashes_deep($request->request->get('podcast'));

        if (empty($id_podcast)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a podcast id'
            ));
        }

        if (empty($podcast)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a podcast'
            ));
        }

        $dao = new rip_podcasts_dao();
        $results = $dao->update_podcast($id_podcast, $podcast);

        $json_helper->to_json($results);
    }

    /**
     * Upload a podcast image.
     */
    public static function upload_podcast_image() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $id_podcast = $request->query->get('id_podcast');

        if (empty($id_podcast)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a podcast id'
            ));
        }

        if (empty($_FILES['file']) || $_FILES['file']['size'] <= 0) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a file to upload'
            ));
        }

        $uploader = new rip_podcasts_image_uploader();
        $uploaded = $uploader->upload((int) $id_podcast, $_FILES['file']);

        if ($uploaded['status'] === 'error') {
            return $json_helper->to_json($uploaded);
        }

        $dao = new rip_podcasts_dao();

        $results = $dao->insert_podcast_attachment(array(
            'id_podcast' => (int) $id_podcast,
            'id_attachment' => $uploaded['id_attachment'],
        ));

        $json_helper->to_json($results);
    }

    /**
     * Delete a single podcast.
     */
    public static function delete_podcast($id_podcast) {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $id_podcast = $request->query->get('id_podcast');

        if (empty($id_podcast)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a podcast id'
            ));
        }

        $dao = new rip_podcasts_dao();
        $results = $dao->delete_podcast((int) $id_podcast);

        $json_helper->to_json($results);
    }

    /**
     * Generate the XML for a specific program
     */
    public static function generate_podcasts_xml() {
        $json_helper = rip_general_json_helper::get_instance();
        $request = rip_general_http_request::get_instance();

        $slug = $request->query->get('slug');

        if (empty($slug)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a program slug'
            ));
        }

        $podcasts_dao = new rip_podcasts_dao;
        $programs_dao = new rip_programs_dao();
        $S3 = new rip_podcasts_s3_service();
        $xml_service = new rip_podcasts_xml_service($podcasts_dao, $programs_dao, $S3);

        $results = $xml_service->generate($slug);

        if (empty($results)) {
            return $json_helper->to_json(array(
                        'status' => 'error',
                        'message' => 'Error in generating the XML feed'
            ));
        }

        $json_helper->to_json($results);
    }

}