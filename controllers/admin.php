<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This is a messaging module for PyroCMS
 *
 * @author 		Ben Rogmans
 * @website		http://www.bridgemarketing.nl
 */
class Admin extends Admin_Controller {

    public function __construct() {
        
        parent::__construct();
        $this->data	= [];
    }

    /**
     * List all threads
     */
    public function index() {
    	
		$this->data['threads'] = $this->mahana->get_all_threads_from_all_grouped();
		
        $this->template
			 ->title($this->module_details['name'])
			 ->build('admin/allThreads', $this->data);
		
    }
    
	
}
