<?php

namespace Rip_Seo\Services;

/**
 * Description of rip-seo-query-service
 *
 * @author Gabriele
 */
class Rip_Seo_Query_Service {

    /**
     * Holds a reference
     * to Seo Dao
     * 
     * @var Object
     */
    private $_seo_dao;
    private $_default_meta = array(
        'title' => '',
        'description' => '',
        'image' => ''
    );

    /**
     * On construction set
     * service dependency.
     * 
     * @param \Rip_General\Classes\Rip_Abstract_Dao $seo_dao
     */
    public function __construct(
    \Rip_General\Classes\Rip_Abstract_Dao $seo_dao
    ) {
        $this->_seo_dao = $seo_dao;
    }

    /**
     * 
     * @param type $path
     * @return \Rip_General\Dto\Message
     */
    public function get_meta_by_path($path) {
        $message = new \Rip_General\Dto\Message();

        if (empty($path)) {
            $message->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a relative path');

            return $message;
        }

        $data = $this->_seo_dao->get_meta_by_path($path);

        if (empty($data)) {
            $message->set_code(404)
                    ->set_status('error')
                    ->set_message('Cannot found resource with path ' . $path);

            return $message;
        }

        $data['description'] = \Rip_General\Filters\Rip_Output_Filter::strip_content($data['description']);

        $message->set_code(200)
                ->set_status('ok')
                ->set_meta($data);

        return $message;
    }

}
