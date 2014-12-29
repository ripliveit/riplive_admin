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
    protected $_podcasts_dao;

    /**
     * Holds a reference to programs_dao Class.
     * 
     * @var object 
     */
    protected $_programs_dao;

    /**
     * Holds a reference to S3 service Class.
     * 
     * @var object 
     */
    protected $_S3;

    /**
     * Holds a reference to xml_generator Class.
     * @var object 
     */
    protected $_xml_generator;

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
            \Rip_General\Classes\Rip_Abstract_Dao $podcasts_dao, 
            \Rip_General\Classes\Rip_Abstract_Dao $programs_dao, 
            $S3,
            $xml_generator
        ) {
        $this->_podcasts_dao = $podcasts_dao;
        $this->_programs_dao = $programs_dao;
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
    protected function _get_channel_data($slug) {
        $channels_data = $this->_programs_dao->get_program_by_slug($slug);

        return $channels_data;
    }

    /**
     * Retrieve all items data.
     * 
     * @param string $slug
     * @return array
     */
    protected function _get_items_data($slug) {
        // Retrieve the total number of podcasts.
        $pages  = $this->_podcasts_dao->get_podcasts_number_of_pages($slug);
        $items  = $this->_podcasts_dao->set_items_per_page($pages['count_total'])
                ->get_all_podcasts_by_program_slug($slug);

        return $items;
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
        if (empty($slug)) {
            throw new Exception('Pleas Specify a program slug');
        }

        $channel_data = $this->_get_channel_data($slug);
        $items_data = $this->_get_items_data($slug);

        // Corrispondent to the slug of the program / folder on Amazon S3.
        $filename = $channel_data['slug'];

        $folder = plugin_dir_path(__FILE__) . '../assets/';

        $this->_xml_generator->set_filename($filename);

        $this->_xml_generator->set_folder($folder);

        $generated = $this->_xml_generator->generate($channel_data, $items_data);

        if ($generated['status'] === 'ok') {
            $result = $this->_S3->put_objects($channel_data['slug'] . '/' . $generated['filename'], $generated['path']);

            if ($result['remote_path']) {
                return $result;
            } else {
                return array(
                    'status' => 'error',
                    'message' => 'Error in uploading the file to Amazon S3',
                );
            }
        } else {
            return array(
                'status' => 'error',
                'message' => $generated['message']
            );
        }
    }

}
