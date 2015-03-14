<?php

namespace Rip_Podcasts\Controllers;

/**
 * Podcast controller.
 * Implements method invoked by ajax request to retrieve chart's data.
 */
class Rip_Podcasts_Controller extends \Rip_General\Classes\Rip_Abstract_Controller {

    /**
     * Return the total number of
     * pages.
     */
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
    public function delete_podcast() {
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
        
        $service = $this->_container['podcastsPersistService'];
        $result = $service->upload_podcast_image((int) $id_podcast, $_FILES);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

    /**
     * Generate the XML for a specific program
     */
    public function generate_podcasts_xml() {
        $slug = $this->_request->query->get('slug');
        $service = $this->_container['podcastsXmlService'];
        $result = $service->generate($slug);

        $this->_response->set_code($result->get_code())
                ->to_json($result);
    }

}
