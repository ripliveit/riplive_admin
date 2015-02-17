<?php

namespace Rip_General\Classes;

/**
 * Abstract Controller.
 * Set the request object for all client controllers.
 */
class Rip_Abstract_Controller {

    /**
     * Request object.
     * 
     * @var \Rip_General\Classes\Rip_Http_Request
     */
    protected $_request;

    /**
     * Response object.
     * 
     * @var \Rip_General\Classes\Rip_Http_Response
     */
    protected $_response;
    
    /**
     * The Dependency Injection Container.
     * 
     * @var \ArrayAccess 
     */
    protected $_container;

    /**
     * On construction
     * set the Request and Response objects.
     * 
     * @param \Rip_General\Classes\Rip_Http_Request $request
     * @param \Rip_General\Classes\Rip_Http_Response $response
     */
    public function __construct(
        \Rip_General\Classes\Rip_Http_Request $request, 
        \Rip_General\Classes\Rip_Http_Response $response,
        \ArrayAccess $container
    ) {
        $this->_request = $request;
        $this->_response = $response;
        $this->_container = $container;
    }

}
