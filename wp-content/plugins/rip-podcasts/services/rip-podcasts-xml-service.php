<?php

namespace Rip_Podcasts\Services;

/**
 * Service that handle
 * 1) Generation of XML feed for a specific program.
 * 2) Move the file to te correct location on Amazon S3
 */
class Rip_Podcasts_Xml_Service {

    /**
     * Holds a reference to podcasts_dao Class.
     * 
     * @var object 
     */
    private $_podcasts_query_service;

    /**
     * Holds a reference to programs_dao Class.
     * 
     * @var object 
     */
    private $_programs_query_service;

    /**
     * Holds a reference to S3 service Class.
     * 
     * @var object 
     */
    private $_S3;

    /**
     * Holds a reference to xml_generator Class.
     * @var object 
     */
    private $_xml_generator;

    /**
     * Class constructor.
     * Accept as parameter all the needed dependecy to retrieve podcast's data,
     * to retrieve program's data, to genereate the XML through xml_generator object
     * and to move the generated feed to Amazon S3.
     * 
     * @param object $podcasts_dao
     * @param object $programs_dao
     * @param object $S3
     */
    public function __construct(
        \Rip_General\Classes\Rip_Abstract_Query_Service $podcasts_query_service, 
        \Rip_General\Classes\Rip_Abstract_Query_Service $programs_query_service, 
        $S3, 
        $xml_generator
    ) {
        $this->_podcasts_query_service = $podcasts_query_service;
        $this->_programs_query_service = $programs_query_service;
        $this->_S3 = $S3;
        $this->_xml_generator = $xml_generator;
    }

    /**
     * Retrieve all program's data
     * with a specific slug.
     * 
     * @param string $slug
     * @return array
     */
    private function _get_program_data($slug) {
        $program_object = $this->_programs_query_service->get_program_by_slug($slug);
        $program = $program_object->get_program();

        return $program;
    }

    /**
     * Retrieve all items data.
     * 
     * @param string $slug
     * @return array
     */
    private function _get_podcasts_data($slug) {
        // Retrieve the total number of podcasts.
        $page_object = $this->_podcasts_query_service->get_podcasts_number_of_pages($slug);
        $podcast_object = $this->_podcasts_query_service
                ->set_items_per_page($page_object->get_number_of_pages()['count_total'])
                ->get_all_podcasts_by_program_slug($slug);

        $podcasts = $podcast_object->get_podcasts();

        return $podcasts;
    }

    /**
     * Generate the XML through xml_generator class 
     * and move the feed to Amazon S3.
     * 
     * @param string $slug
     * @return boolean
     * @throws Error
     */
    public function generate($slug) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a program slug');

            return $message;
        }

        $program_data = $this->_get_program_data($slug);
        $podcasts_data = $this->_get_podcasts_data($slug);

        // Corrispondent to the slug of the program / folder on Amazon S3.
        $filename = $program_data['slug'];

        $folder = plugin_dir_path(__FILE__) . '../assets/';

        $this->_xml_generator->set_filename($filename)
                ->set_folder($folder);

        $generated = $this->_xml_generator->generate($program_data, $podcasts_data);

        if ($generated->get_status() === 'error') {
            $message->set_code(500)
                    ->set_status('error')
                    ->set_message($generated->get_message());

            return $message;
        }

        $uploaded = $this->_S3->put_objects($program_data['slug'] . '/' . $generated->get_filename(), $generated->get_path());
        
        return $uploaded;
    }

}
