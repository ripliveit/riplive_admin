<?php

namespace Rip_Podcasts\Services;

require_once plugin_dir_path(__FILE__) . '../library/Amazon/aws-autoloader.php';

use \Aws\S3\S3Client;

/**
 * Amazon S3 service.
 */
class Rip_Podcasts_S3_Service {

    /**
     * Pubblic Key.
     * 
     * @var string 
     */
    private $_key = 'AKIAJMWTVZGFOYVC6FRA';

    /**
     * Secret Key.
     * 
     * @var string 
     */
    private $_secret = 'ls17Ciiw4qHY5D3R+ZVquSx+8dCCib4hbHf6G8d/';

    /**
     * Holds the constructed S3 object from official S3 API.
     * 
     * @var string 
     */
    private $_client;

    /**
     * Holds a reference 
     * to the Dto Objects
     * 
     * @var type 
     */
    private $_message;

    /**
     * Class constructor.
     */
    public function __construct(\Rip_General\Dto\Message $message) {
        $this->_message = $message;

        // Instantiate the S3 client with your AWS credentials and desired AWS region
        $this->_client = S3Client::factory(array(
                    'key' => $this->_key,
                    'secret' => $this->_secret,
                    'region' => 'eu-west-1',
        ));

        $this->_client->registerStreamWrapper();
    }

    /**
     * Retrieve a bucket.
     * 
     * @return string
     */
    public function get_bucket() {
        $results = $this->_client->listBuckets();
        $bucket_name = '';

        foreach ($results['Buckets'] as $key => $bucket) {
            if ($bucket['Name'] === 'riplive.it-podcast') {
                $bucket_name = $bucket['Name'];
            }
        }

        return $bucket_name;
    }

    /**
     * Get a list of object specifyng a key.
     * 
     * @param string $key
     * @return object Iterator
     * @throws Exception
     */
    public function get_objects($key) {
        if (!$key) {
            throw new Exception('Please Specify a key to parse');
        }

        $bucket = $this->get_bucket();

        $iterator = $this->_client->getIterator('ListObjects', array(
            'Bucket' => $bucket,
            'Prefix' => $key
        ));

        return $iterator;
    }

    /**
     * Upload a file to Amazon S3 Bucket.
     * 
     * @param string $remote_path
     * @param string $local_path
     * @return boolean
     * @throws Error
     */
    public function put_objects($remote_path, $local_path) {
        if (!$remote_path) {
            $this->_message->set_code(500)
                    ->set_status('error')
                    ->set_message('Please specify a remote path (key) to upload the file');

            return $this->_message;
        }

        if (!$local_path) {
            $this->_message->set_code(500)
                    ->set_status('error')
                    ->set_message('Please specify the local path of the file to send');

            return $this->_message;
        }

        $bucket = $this->get_bucket();

        $result = $this->_client->putObject(array(
            'Bucket' => $bucket,
            'Key' => $remote_path,
            'Body' => fopen($local_path, 'r+'),
            'ACL' => 'public-read'
        ));

        if (!$result) {
            $this->_message->set_code(500)
                    ->set_status('error')
                    ->set_message('Error during upload to bucket');

            return $this->_message;
        }

        @unlink($local_path);

        $this->_message->set_code(200)
                ->set_status('ok')
                ->set_message('Xml was successfully uploaded to ' . $result['ObjectURL'])
                ->set_remote_path($result['ObjectURL']);

        return $this->_message;
    }

}
