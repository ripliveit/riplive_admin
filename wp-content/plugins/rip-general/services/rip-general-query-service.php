<?php

namespace Rip_General\Services;

/**
 * General query service,
 * used by other plugin's layers to retrieve
 * administrative geographic data.
 *
 * @author Gabriele
 */
class Rip_General_Query_Service {

    /**
     * Holds a reference to General Dao.
     * 
     * @var Object 
     */
    private $_general_dao;

    /**
     * Holds a reference to Posts Dao.
     * 
     * @var Object 
     */
    private $_posts_dao;

    /**
     * On construction set the service
     * dependencies.
     * 
     * @param \Rip_General\Classes\Rip_Abstract_Dao $general_dao
     * @param \Rip_General\Classes\Rip_Abstract_Dao $posts_dao
     */
    public function __construct(\Rip_General\Classes\Rip_Abstract_Dao $general_dao, \Rip_General\Classes\Rip_Abstract_Dao $posts_dao) {
        $this->_general_dao = $general_dao;
        $this->_posts_dao = $posts_dao;
    }

    /**
     * Query for a list
     * of cities through General_Dao.
     * 
     * @return \Rip_General\Dto\Message
     */
    public function get_comuni() {
        $message = new \Rip_General\Dto\Message();
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_General\Mappers\Rip_General_Mapper', $this->_posts_dao
        );

        $data = $mapper->map($this->_general_dao->get_comuni());

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $data)
                ->set_pages(1)
                ->set_songs(empty($data) ? array() : $data);

        return $message;
    }

    /**
     * Query for a list of italian's provinces
     * through General_Dao.
     * 
     * @return \Rip_General\Dto\Message
     */
    public function get_province() {
        $message = new \Rip_General\Dto\Message();
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_General\Mappers\Rip_General_Mapper', $this->_posts_dao
        );

        $data = $mapper->map($this->_general_dao->get_province());

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $data)
                ->set_pages(1)
                ->set_songs(empty($data) ? array() : $data);

        return $message;
    }

    /**
     * Query for a list of italian's regions 
     * through General_Dao.
     * 
     * @return \Rip_General\Dto\Message
     */
    public function get_regioni() {
        $message = new \Rip_General\Dto\Message();
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_General\Mappers\Rip_General_Mapper', $this->_posts_dao
        );

        $data = $mapper->map($this->_general_dao->get_regioni());

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $data)
                ->set_pages(1)
                ->set_songs(empty($data) ? array() : $data);

        return $message;
    }

    /**
     * Query for a list
     * of world's countries through General_Dao.
     * 
     * @return \Rip_General\Dto\Message
     */
    public function get_nazioni() {
        $message = new \Rip_General\Dto\Message();
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_General\Mappers\Rip_General_Mapper', $this->_posts_dao
        );

        $data = $mapper->map($this->_general_dao->get_nazioni());

        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total((int) $data)
                ->set_pages(1)
                ->set_songs(empty($data) ? array() : $data);

        return $message;
    }

}
