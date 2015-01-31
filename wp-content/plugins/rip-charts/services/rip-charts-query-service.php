<?php

namespace Rip_Charts\Services;

/**
 * A service used by other Chart plugin's classes
 * to implement and run chart's business logic.
 */
class Rip_Charts_Query_Service {

    /**
     * Holds a reference to Chart DAO.
     * 
     * @var Object 
     */
    protected $_charts_dao;

    /**
     * Class constructor.
     */
    public function __construct(\Rip_General\Classes\Rip_Abstract_Dao $charts_dao) {
        $this->_charts_dao = $charts_dao;
    }

    /**
     * Retrieve all posts from 'Charts' custom post type.
     */
    public function get_all_charts() {
        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Charts\Mappers\Rip_Chart_Mapper', new \Rip_General\Daos\Rip_Posts_Dao()
        );

        $data = $mapper->map($this->_charts_dao->get_all_charts());

        $message = new \Rip_General\Dto\Message();
        $message->set_status('ok')
                ->set_code(200)
                ->set_count(count($data))
                ->set_count_total(count($data))
                ->set_pages(1)
                ->set_charts(empty($data) ? array() : $data);

        return $message;
    }

    /**
     * Retrieve a single post from 'Charts' custom post type.
     */
    public function get_chart_by_slug($slug = null) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            return $message->set_code(400)
                            ->set_status('error')
                            ->set_message('Please specify a chart slug');
        }

        $mapper = \Rip_General\Mappers\Rip_Factory_Mapper::create_mapper(
                        '\Rip_Charts\Mappers\Rip_Chart_Mapper', new \Rip_General\Daos\Rip_Posts_Dao()
        );
        $data = $mapper->map($this->_charts_dao->get_chart_by_slug($slug));

        if (empty($data)) {
            return $message->set_code(404)
                            ->set_status('error')
                            ->set_message('Chart not found');
        }

        return $message->set_code(200)
                        ->set_status('ok')
                        ->set_chart(current($data));
    }

    /**
     * Return the number of all charts
     * and the number of total pages. 
     * Used for client side pagination.
     */
    public function get_complete_charts_number_of_pages($slug = null) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            return $message->set_code(400)
                            ->set_status('error')
                            ->set_message('Please specify a chart slug');
        }

        $data = $this->_charts_dao->get_complete_charts_number_of_pages($slug);

        if (empty($data)) {
            return $message->set_code(404)
                            ->set_status('error')
                            ->set_message('Cannot find number of pages');
        }

        return $message->set_code(200)
                        ->set_status('ok')
                        ->set_number_of_pages($data);
    }

    /**
     * Return a list of all complete charts, 
     * ordered by date.
     */
    public function get_all_complete_charts($count = null, $page = null) {
        $message = new \Rip_General\Dto\Message();

        $data = $this->_charts_dao->set_items_per_page($count)
                ->get_all_complete_charts($page);
        $pages = $this->_charts_dao->get_complete_charts_number_of_pages();

        return $message->set_status('ok')
                        ->set_code(200)
                        ->set_count(count($data))
                        ->set_count_total((int) $pages['count_total'])
                        ->set_pages($pages['pages'])
                        ->set_complete_charts(empty($data) ? array() : $data);
    }

    /**
     * Return a list of all complete chart of a specific chart, 
     * specifing the slug of the chart. 
     */
    public function get_all_complete_charts_by_chart_type($slug = null, $count = null, $page = null) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            return $message->set_code(400)
                            ->set_status('error')
                            ->set_message('Please specify a chart type slug, for example rock-chart');
        }

        $data = $this->_charts_dao->set_items_per_page($count)
                ->get_all_complete_charts_by_chart_type($slug, $page);
        $pages = $this->_charts_dao->get_complete_charts_number_of_pages($slug);

        return $message->set_status('ok')
                        ->set_code(200)
                        ->set_count(count($data))
                        ->set_count_total((int) $pages['count_total'])
                        ->set_pages($pages['pages'])
                        ->set_complete_charts(empty($data) ? array() : $data);
    }

    /**
     * Return lasts complete charts,
     * one per genre.
     */
    public function get_latest_complete_charts($count = null) {
        $message = new \Rip_General\Dto\Message();

        $data = $this->_charts_dao->set_items_per_page($count)->get_latest_complete_charts();

        return $message->set_status('ok')
                        ->set_code(200)
                        ->set_count(count($data))
                        ->set_count_total((int) count($data))
                        ->set_pages(1)
                        ->set_complete_charts(empty($data) ? array() : $data);
    }

    /**
     * Return a complete chart,
     * with all realtive songs.
     */
    public function get_complete_chart_by_chart_archive_slug($slug) {
        $message = new \Rip_General\Dto\Message();

        if (empty($slug)) {
            return $message->set_code(400)
                            ->set_status('error')
                            ->set_message('Please specify a chart archive slug');
        }

        $data = $this->_charts_dao->get_complete_chart_by_chart_archive_slug($slug);

        if (empty($data)) {
            return $message->set_code(404)
                            ->set_status('error')
                            ->set_message('Chart not found');
        }

        return $message->set_code(200)
                        ->set_status('ok')
                        ->set_complete_chart($data);
    }

}
