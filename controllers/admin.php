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

		foreach($this->data['threads']['retval'] as $key => $thread) {
			$this->data['threads']['retval'][$key]['subject'] = $this->getSubject($thread['messages']);
		}
		
        $this->template
			 ->title($this->module_details['name'])
			 ->build('admin/allThreads', $this->data);
		
    }
    
    public function thread($id, $user) {

	    $this->data['thread'] 	= $this->mahana->get_full_thread($id, $user);
		$this->data['id'] 		= $id;
		$this->data['subject'] 	= $this->getSubject($this->data['thread']['retval']);
		
        $this->template
			 ->title($this->module_details['name'])
			 ->build('admin/thread', $this->data);
	    
	    
    }
    
    private function getSubject($thread) {

	    if(empty($thread[0]['subject'])) return;

		$subject 		= $thread[0]['subject'];
		$stream_slug 	= Settings::get('mahana_subject_as_stream');

		if(!empty($stream_slug) && $stream = $this->streams->streams->get_stream($stream_slug, 'streams')) {
			if($connectedEntry = $this->streams->entries->get_entry($subject, $stream_slug, 'streams')) {
				$this->data['subject_column'] = $stream->stream_name;
				return anchor('admin/streams/entries/edit/'.$stream->id.'/'.$connectedEntry->id,$connectedEntry->{$stream->title_column});
			}
		}
		return $subject;
	    
    }
	
}
