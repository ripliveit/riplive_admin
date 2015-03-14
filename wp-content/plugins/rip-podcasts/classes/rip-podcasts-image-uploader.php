<?php

namespace Rip_Podcasts\Classes;

require_once(ABSPATH . 'wp-admin' . '/includes/image.php');

/**
 * A Service used to upload podcast's images.
 */
class Rip_Podcasts_Image_Uploader {
    
    /**
     * A generic Dto Object.
     * 
     * @var type 
     */
    private $_message;

    /**
     * Allowed file type.
     * 
     * @var array 
     */
    private $_allowed_file_types = array(
        'image/jpg',
        'image/jpeg',
        'image/gif',
        'image/png'
    );

    /**
     * Wordpress upload configuration.
     * 
     * @var array 
     */
    private $_upload_overrides = array(
        'test_form' => false
    );
    
    /**
     * On construction set the dependency.
     * 
     * @param \Rip_General\Dto\Message $message
     */
    public function __construct(\Rip_General\Dto\Message $message) {
        $this->_message = $message;
    }

    /**
     * Create an attachment, moving
     * the uploaded image to wordpress media library.
     * 
     * @param int $id
     * @param array $uploaded_file
     * @return array
     */
    private function move_to_media_library($id, array $uploaded_file = array()) {
        $title = 'podcast_image_' . $id . '_' . date('Y-m-d', time());

        // Set up options array to add the file as an attachment
        $attachment = array(
            'post_mime_type' => $uploaded_file['type'],
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'inherit'
        );

        // Run the wp_insert_attachment function. 
        // This adds the file to the media library and generates the thumbnails. 
        $attach_id = wp_insert_attachment($attachment, $uploaded_file['file']);

        if (!$attach_id) {
            $this->_message
                    ->set_code(400)
                    ->set_status('error')
                    ->set_message('Error in inserting the attachment');

            return $this->_message;
        }
        
        // Generate all thumbnails.
        $attach_data = wp_generate_attachment_metadata($attach_id, $uploaded_file['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);
        
        $this->_message
                    ->set_code(200)
                    ->set_status('ok')
                    ->set_message('Attachment succesfully created')
                    ->set_id_attachment($attach_id);
        

        return $this->_message;
    }

    /**
     * Upload an image to wordpress upload's folder.
     *  
     * @param int $id
     * @param array $file
     * @return array
     */
    public function upload($id, array $file = array()) {
        if (empty($id) || !is_int($id)) {
            $this->_message
                    ->set_code(400)
                    ->set_status('error')
                    ->set_message('Please specify a unique id');

            return $this->_message;
        }

        if (empty($file)) {
            $this->_message
                    ->set_code(400)
                    ->set_status('error')
                    ->set_message('File is empty');

            return $this->_message;
        }

        if (!in_array($file['type'], $this->_allowed_file_types)) {
            $this->_message
                    ->set_code(400)
                    ->set_status('error')
                    ->set_message('File type not allowed');

            return $this->_message;
        }

        // Handle 
        // the upload using WP's wp_handle_upload function. 
        $uploaded_file = wp_handle_upload($file, $this->_upload_overrides);

        if (empty($uploaded_file['file'])) {
            $this->_message
                    ->set_code(500)
                    ->set_status('error')
                    ->set_message('Error in uploading the image');

            return $this->_message;
        }

        $result = $this->move_to_media_library($id, $uploaded_file);

        return $result;
    }

}
