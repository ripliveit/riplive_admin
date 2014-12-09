<?php

require_once(ABSPATH . 'wp-admin' . '/includes/image.php');

/**
 * A Service used to upload podcast's images.
 */
class rip_podcasts_image_uploader {
    
    /**
     * Allowed file type.
     * 
     * @var array 
     */
    protected $_allowed_file_types = array(
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
    protected $_upload_overrides = array(
        'test_form' => false
    );
    
    /**
     * Create an attachment, moving
     * the uploaded image to wordpress media library.
     * 
     * @param int $id
     * @param array $uploaded_file
     * @return array
     */
    protected function move_to_media_library($id, array $uploaded_file = array()) {
        $title = 'podcast_image_' .$id . '_'. date('Y-m-d', time());
        
        // Set up options array to add this file as an attachment
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
            return array(
                'status' => 'error',
                'message' => 'Error in inserting the attachment'
            );
        }
        
        $attach_data = wp_generate_attachment_metadata($attach_id, $uploaded_file['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);
        
        return array(
            'status' => 'ok',
            'id_attachment' => $attach_id,
            'message' => 'Attachment succesfully created'
        );
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
            return array(
                'status' => 'error',
                'message' => 'Please specify a unique id'
            );
        }
        
        if (empty($file)) {
            return array(
                'status' => 'error',
                'message' => 'File is empty'
            );
        }
        
        if (!in_array($file['type'], $this->_allowed_file_types)) {
            return array(
                'status' => 'error',
                'message' => 'File type not allowed'
            );
        }

        // Handle 
        // the upload using WP's wp_handle_upload function. 
        $uploaded_file = wp_handle_upload($file, $this->_upload_overrides);

        if (empty($uploaded_file['file'])) {
            return array(
                'status' => 'error',
                'message' => 'Error in uploading the image'
            );
        }
        
        $result = $this->move_to_media_library($id, $uploaded_file);
        
        return $result;
    }
}