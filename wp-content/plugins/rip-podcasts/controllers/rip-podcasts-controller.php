<?php

namespace Rip_Podcasts\Controllers;

/**
 * Podcast controller.
 * Implements method invoked by ajax request to retrieve chart's data.
 */
class Rip_Podcasts_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {


    public function get_podcasts_number_of_pages() {
        $slug = $this->_request->query->get('slug');
        $count = $this->_request->query->get('count');
        
        $service = $this->_container['podcastsQueryService'];
        $result = $service->get_podcasts_number_of_pages($slug, $count);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Retrieve all podcasts.
     */
    public function get_all_podcasts() {
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');

        $service = $this->_container['podcastsQueryService'];
        $result = $service->get_all_podcasts($count, $page);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Retrieve all podcasts with a specific program id.
     */
    public function get_all_podcasts_by_program_slug() {
        $slug = $this->_request->query->get('slug');
        $count = $this->_request->query->get('count');
        $page = $this->_request->query->get('page');

        $service = $this->_container['podcastsQueryService'];
        $result = $service->get_all_podcasts_by_program_slug($slug, $count, $page);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Retrieve a podcast by its unique identifier.
     */
    public function get_podcast_by_id() {
        $id_podcast = $this->_request->query->get('id_podcast');

        $service = $this->_container['podcastsQueryService'];
        $result = $service->get_podcast_by_id($id_podcast);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Insert a podcast.
     */
    public function insert_podcast() {
        $podcast = $this->_request->request->get('podcast');

        $service = $this->_container['podcastsPersistService'];
        $result  = $service->insert_podcast($podcast);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Update a podcast.
     */
    public function update_podcast() {
        $id_podcast = $this->_request->query->get('id_podcast');
        $podcast = $this->_request->request->get('podcast');

        $service = $this->_container['podcastsPersistService'];
        $result  = $service->update_podcast((int) $id_podcast, $podcast);
        
        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Delete a single podcast.
     */
    public function delete_podcast($id_podcast) {
        $id_podcast = $this->_request->query->get('id_podcast');

        $service = $this->_container['podcastsPersistService'];
        $result = $service->delete_podcast((int) $id_podcast);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
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
