<?php

namespace Rip_Podcasts\Controllers;

/**
 * Podcast controller.
 * Implements method invoked by ajax request to retrieve chart's data.
 */
class Rip_Podcasts_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Return total number of podcast saved in wp_podcasts table.
     * If program's slug is passed as parameters, then return the total number
     * of podcasts for that particular program.
     */
    public function get_podcasts_number_of_pages() {
        $slug = $this->_request->query->get('slug');

        $dao = new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();
        $results = $dao->get_podcasts_number_of_pages($slug);

        if (empty($results)) {
            return $this->_response->set_code(404)->to_json(array(
                        'status' => 'error',
                        'message' => 'Pages not found'
            ));
        }

        $this->_response->to_json(array(
            'number_of_pages' => $results
        ));
    }

    /**
     * Retrieve all podcasts.
     */
    public function get_all_podcasts() {
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');

        $dao = new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();
        $results = $dao->set_items_per_page($count)->get_all_podcasts($page);
        $pages = $dao->get_podcasts_number_of_pages();

        $this->_response->to_json(array(
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
    public function get_all_podcasts_by_program_slug() {
        $slug = $this->_request->query->get('slug');
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');

        if (empty($slug)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a program slug'
            ));
        }

        $dao = new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();
        $results = $dao->set_items_per_page($count)->get_all_podcasts_by_program_slug($slug, $page);

        // Load page number passing current podcast's program slug.
        $pages = $dao->get_podcasts_number_of_pages($slug);

        $this->_response->to_json(array(
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
    public function get_podcast_by_id() {
        $id_podcast = $this->_request->query->get('id_podcast');

        if (empty($id_podcast)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a podcast id'
            ));
        }

        $dao = new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();
        $results = $dao->get_podcast_by_id((int) $id_podcast);

        if (empty($results)) {
            return $this->_response->set_code(404)->to_json(array(
                        'status' => 'error',
                        'message' => 'Not found'
            ));
        }

        return $this->_response->to_json(array(
                    'status' => 'ok',
                    'podcast' => $results
        ));
    }

    /**
     * Insert a podcast.
     */
    public function insert_podcast() {
        $podcast = stripslashes_deep($this->_request->request->get('podcast'));

        if (empty($podcast)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a podcast'
            ));
        }

        $dao = new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();
        $results = $dao->insert_podcast($podcast);

        $this->_response->to_json($results);
    }

    /**
     * Update a podcast.
     */
    public function update_podcast() {
        (int) $id_podcast = $this->_request->query->get('id_podcast');
        $podcast = stripslashes_deep($this->_request->request->get('podcast'));

        if (empty($id_podcast)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a podcast id'
            ));
        }

        if (empty($podcast)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a podcast'
            ));
        }

        $dao = new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();
        $results = $dao->update_podcast($id_podcast, $podcast);
        
        if ((int) $results === 0) {
            return $this->_response->set_code(412)->to_json(array(
                        'status' => 'error',
                        'message' => 'Cannot update the podcast'
            ));
        }

        $this->_response->set_code(200)->to_json(array(
            'status' => 'ok',
            'message' => 'Podcast successfully updated'
        ));

        $this->_response->to_json($results);
    }

    /**
     * Delete a single podcast.
     */
    public function delete_podcast($id_podcast) {
        $id_podcast = $this->_request->query->get('id_podcast');

        if (empty($id_podcast)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a podcast id'
            ));
        }

        $dao = new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();
        $results = $dao->delete_podcast((int) $id_podcast);

        if ((int) $results === 0) {
            return $this->_response->set_code(412)->to_json(array(
                        'status' => 'error',
                        'message' => 'Podcast does not exists'
            ));
        }

        $this->_response->set_code(200)->to_json(array(
            'status' => 'ok',
            'message' => 'Podcast successfully deleted'
        ));
    }

    /**
     * Upload a podcast image.
     */
    public function upload_podcast_image() {
        $id_podcast = $this->_request->query->get('id_podcast');

        if (empty($id_podcast)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a podcast id'
            ));
        }

        if (empty($_FILES['file']) || $_FILES['file']['size'] <= 0) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a file to upload'
            ));
        }

        $uploader = new \Rip_Podcasts\Classes\Rip_Podcasts_Image_Uploader();
        $uploaded = $uploader->upload((int) $id_podcast, $_FILES['file']);

        if ($uploaded['status'] === 'error') {
            return $this->_response->set_code(500)->to_json($uploaded);
        }

        $dao = new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();

        $results = $dao->insert_podcast_attachment(array(
            'id_podcast' => (int) $id_podcast,
            'id_attachment' => $uploaded['id_attachment'],
        ));

        $this->_response->to_json($results);
    }

    /**
     * Generate the XML for a specific program
     */
    public function generate_podcasts_xml() {
        $slug = $this->_request->query->get('slug');

        if (empty($slug)) {
            return $this->_response->set_code(400)->to_json(array(
                        'status' => 'error',
                        'message' => 'Please specify a program slug'
            ));
        }

        $podcasts_dao  = new \Rip_Podcasts\Daos\Rip_Podcasts_Dao();
        $programs_dao  = new \Rip_Programs\Daos\Rip_Programs_Dao();
        $S3            = new \Rip_Podcasts\Services\Rip_Podcasts_S3_Service();
        $xml_generator = new \Rip_Podcasts\Classes\Rip_Podcasts_Xml_Generator();

        $xml_service = new \Rip_Podcasts\Services\Rip_Podcasts_Xml_Service(
                $podcasts_dao, $programs_dao, $S3, $xml_generator
        );

        $results = $xml_service->generate($slug);

        if (empty($results)) {
            return $this->_response->set_code(500)->to_json(array(
                        'status' => 'error',
                        'message' => 'Error in generating the XML feed'
            ));
        }

        $this->_response->to_json($results);
    }

}
