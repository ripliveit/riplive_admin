<?php

namespace Rip_General\Daos;

/**
 * Description of rip-attachments-dao
 *
 * @author Gabriele
 */
class Rip_Attachment_Dao extends \Rip_General\Classes\Rip_Abstract_Dao {

    /**
     * Return all attachments images, giving an attachments id.
     * 
     * @param int $attachment_id
     * @param string $size
     * @return string
     * @throws Exception
     */
    public function get_attachment_images($attachment_id, $size = null) {
        if (empty($attachment_id)) {
            throw new Exception('Please specify an attachment id');
        }

        if (empty($size)) {
            $size = 'thumbnail';
        }

        $image = wp_get_attachment_image_src($attachment_id, $size);

        return $image[0];
    }

}
