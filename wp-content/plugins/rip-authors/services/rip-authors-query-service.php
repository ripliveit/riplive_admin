<?php

namespace Rip_Authors\Services;

/**
 * A service
 * that implements method to query and manipulate
 * author's data.
 *
 * @author Gabriele
 */
class Rip_Authors_Query_Service extends \Rip_General\Classes\Rip_Abstract_Query_Service {

    /**
     * Holds a reference
     * to Author Access Object.
     * 
     * @var Objecty 
     */
    private $_authors_dao;

    /**
     * On construction
     * set Authors_Dao as main dependency.
     * 
     * @param \Rip_General\Classes\Rip_Abstract_Dao $authors_dao
     */
    public function __construct(\Rip_General\Classes\Rip_Abstract_Dao $authors_dao) {
        $this->_authors_dao = $authors_dao;
    }

    /**
     * Query for all
     * site's authors, excluded the administrator.
     * 
     * @return \Rip_General\Dto\Message
     */
    public function get_all_authors() {
        $message = new \Rip_General\Dto\Message();

        $authors = $this->_authors_dao->get_all_authors('author');
        $editors = $this->_authors_dao->get_all_authors('editor');
        $result = array_merge($authors, $editors);
        ksort($result);

        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper('\Rip_Authors\Mappers\Rip_Author_Mapper');
        $data = $mapper->map($result);

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total(count($data))
                ->set_pages(1)
                ->set_authors(empty($data) ? array() : $data);

        return $message;
    }

    /**
     * Query for a specific author.
     * 
     * @param type $slug
     * @return \Rip_General\Dto\Message
     */
    public function get_author_by_slug($slug = null) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify an author slug');

            return $message;
        }

        $result = $this->_authors_dao->get_author_by_slug($slug);

        if (empty($result)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Author not found');

            return $message;
        }

        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper('\Rip_Authors\Mappers\Rip_Author_Mapper');
        $data = $mapper->map(array(
            $result
        ));

        $message->set_code(200)
                ->set_status('ok')
                ->set_author(current($data));

        return $message;
    }

    /**
     * Return an array with all wordpress users and their respective id.
     * @return array
     */
    public function get_users_for_metabox() {
        $users = get_users();

        if (empty($users)) {
            return false;
        }

        $accumulator = array();

        foreach ($users as $user) {
            array_push($accumulator, array(
                'label' => $user->display_name,
                'value' => $user->ID
            ));
        }

        return $accumulator;
    }

}
