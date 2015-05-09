<?php

namespace Rip_Programs\Services;

/**
 *
 *
 * @author Gabriele
 */
class Rip_Programs_Query_Service extends \Rip_General\Classes\Rip_Abstract_Query_Service {

    /**
     * Holds a reference to Post Dao.
     * 
     * @var Object 
     */
    private $_posts_dao;

    /**
     * Holds a reference to Podcasts_Dao.
     * 
     * @var Object 
     */
    private $_podcasts_dao;

    /**
     * Holds a reference to Rip_Programs_Dao.
     * 
     * @var Object 
     */
    private $_programs_dao;

    /**
     * Holds a reference to Rip_Posts_Validator.
     * 
     * @var type 
     */
    private $_posts_validator;
    
    
    /**
     * Holds a reference to Rip_Authors_Query_Service.
     * 
     * @var type 
     */
    private $_authors_query_service;

    /**
     * Holds a reference to Rip_General_Service.
     * 
     * @var type 
     */
    private $_general_service;

    /**
     * Class constructor.
     */
    public function __construct(
            \Rip_General\Classes\Rip_Abstract_Dao $programs_dao, 
            \Rip_General\Classes\Rip_Abstract_Dao $podcasts_dao, 
            \Rip_General\Classes\Rip_Abstract_Dao $posts_dao, 
            \Rip_General\Classes\Rip_Abstract_Validator $posts_validator, 
            \Rip_General\Classes\Rip_Abstract_Query_Service $authors_query_service,
            \Rip_General\Services\Rip_General_Service $general_service
    ) {
        $this->_programs_dao = $programs_dao;
        $this->_podcasts_dao = $podcasts_dao;
        $this->_posts_dao    = $posts_dao;
        $this->_posts_validator = $posts_validator;
        $this->_authors_query_service = $authors_query_service;
        $this->_general_service = $general_service;
    }

    /**
     * 
     * @param type $count
     * @param type $page
     * @param type $status
     * @return \Rip_General\Dto\Message
     */
    public function get_all_programs($count = null, $page = null, $status = 'publish') {
        $message = new \Rip_General\Dto\Message();
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Programs\Mappers\Rip_Program_Mapper', $this->_podcasts_dao, $this->_posts_dao, $this
        );

        $status = $this->_posts_validator->validate_post_status($status);
        $page_args = $this->get_wpquery_pagination_args($count, $page);
        $data = $mapper->map(
                $this->_programs_dao->get_all_programs($page_args, $status)
        );
        
        foreach ($data as &$program) {
            $program['program_information'] = $this->get_program_information($program['id_program'])->get_program_information();
        }

        $pages = $this->_posts_dao->get_post_type_number_of_page('programs', $count);

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $pages['count_total'])
                ->set_pages($pages['pages'])
                ->set_programs(empty($data) ? array() : $data);

        return $message;
    }

    /**
     * 
     * @param type $slug
     * @return \Rip_General\Dto\Message
     */
    public function get_program_by_slug($slug) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a program slug');

            return $message;
        }

        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Programs\Mappers\Rip_Program_Mapper', $this->_podcasts_dao, $this->_posts_dao
        );

        $data = current($mapper->map($this->_programs_dao->get_program_by_slug($slug)));

        if (empty($data)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Cannot find a program with slug ' . $slug);

            return $message;
        }
        
        $data['program_information'] = $this->get_program_information($data['id_program'])->get_program_information();
        
        $message->set_code(200)
                ->set_status('ok')
                ->set_program($data);

        return $message;
    }

    /**
     * Return all custom field of a specific program.
     * 
     * @param int $id_program
     * @return array
     */
    public function get_program_information($id_program) {
        $message = new \Rip_General\Dto\Message();

        if (empty($id_program)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a program id');

            return $message;
        }

        $fields = get_post_custom($id_program);
        $accumulator   = array();
        
        // Loop over all custom fields to populate $accumulator array.
        // Retrieve all authors information instead the default's one.
        foreach ($fields as $key => $value) {
            // private meta
            if (substr($key, 0, 1) === '_') {
                continue;
            }

            // Remove programs-hidden meta.
            if ($key === 'programs-hidden') {
                continue;
            }

            $accumulator[$key] = get_post_meta($id_program, $key, true);

            // Set authors data.
            if ($key === 'programs-authors') {
                $authors = array();

                foreach ($accumulator[$key] as $author_id) {
                    $author_slug = get_the_author_meta('user_nicename', $author_id);
                    $author = $this->_authors_query_service->get_author_by_slug($author_slug)->get_author();
                    array_push($authors, $author);
                }

                $accumulator[$key] = $authors;
            }

            //Set days data.
            if ($key === 'programs-days' || $key === 'reruns-days') {
                $days = array();

                foreach ($accumulator[$key] as $day) {
                    array_push($days, date('l', strtotime('Sunday +' . $day . 'Days')));
                }

                $accumulator[$key] = $days;
            }
        }
        
        $message->set_code(200)
                ->set_status('ok')
                ->set_program_information($accumulator);
        
        return $message;
    }

    /**
     * Return the programs week schedule.
     */
    public function get_programs_schedule() {
        $message = new \Rip_General\Dto\Message();
        $programs = $this->get_all_programs()->get_programs();
        $days = $this->_general_service->get_days();

        if (empty($programs) || empty($days)) {
            $message->set_code(500)
                    ->set_status('error')
                    ->set_message('Cannot process programs schedule');

            return $message;
        }

        $schedule = array();
        $accumulator = array();

        foreach ($days as $day) {
            $day['label'] = ucfirst($day['label']);

            if (date('l', time()) === ucfirst($day['label'])) {
                $day['today'] = true;
            } else {
                $day['today'] = false;
            }

            $day['programs'] = array();

            // Loop over each program. 
            // If a program is scheduled for the current day
            // than save it into the final $day['programs'] array.
            foreach ($programs as $k => $program) {
                $schedule[$day['value']] = array();
                $information_dto = $this->get_program_information($program['id_program']);
                $information = $information_dto->get_program_information(); 
                
                // Check if the current program is scheduled for the current day, than remove all unnecessary information.
                if (!empty($information['programs-days']) && in_array(date('l', strtotime('Sunday + ' . $day['value'] . 'Days')), $information['programs-days'])) {
                    $program['rerun'] = false;
                    $program['schedule'] = $information['programs-schedule'];
                    $program['authors']  = $information['programs-authors'];

                    unset($program['program_information']);

                    array_push($day['programs'], $program);
                }

                //Check if the program is a rerun, than remove all unnecessary information.
                if (!empty($information['reruns-days']) && in_array(date('l', strtotime('Sunday + ' . $day['value'] . 'Days')), $information['reruns-days'])) {
                    $program['rerun'] = true;
                    $program['schedule'] = $information['reruns-schedule'];
                    $program['authors'] = $information['programs-authors'];

                    unset($program['program_information']);

                    array_push($day['programs'], $program);
                }
            }

            usort($day['programs'], function ($a, $b) {
                $a = strtotime($a['schedule']);
                $b = strtotime($b['schedule']);

                if ($a == $b) {
                    return 0;
                }

                return ($a > $b) ? 1 : -1;
            });

            array_push($accumulator, $day);
        }

        $message->set_code(200)
                ->set_status('ok')
                ->set_schedule($accumulator);
        
        return $message;
    }

}
