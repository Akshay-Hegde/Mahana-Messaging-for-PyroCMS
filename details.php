<?php defined('BASEPATH') or exit('No direct script access allowed');
 
class Module_Mahana extends Module {
 
    public $version = '0.1';
 
    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Mahana messaging'
            ),
            'description' => array(
                'en' => 'A module for implementing your own messaging system using Mahana.'
            ),
            'frontend' => true,
            'backend'	=> true,
            'menu'	  	=> 'users',
        );
    }
    
     
    public function install()
    {
    	$messages = "CREATE TABLE `default_mahana_messages` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `thread_id` int(11) NOT NULL,
			  `body` text NOT NULL,
			  `priority` int(2) NOT NULL DEFAULT '0',
			  `sender_id` int(11) NOT NULL,
			  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		
		
		$participants = "CREATE TABLE `default_mahana_participants` (
			  `user_id` int(11) NOT NULL,
			  `thread_id` int(11) NOT NULL,
			  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`user_id`,`thread_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		$status = "CREATE TABLE `default_mahana_status` (
			  `message_id` int(11) NOT NULL,
			  `user_id` int(11) NOT NULL,
			  `status` int(2) NOT NULL,
			  PRIMARY KEY (`message_id`,`user_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		$threads = "CREATE TABLE `default_mahana_threads` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `subject` text,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		
		$this->db->query($messages);
		$this->db->query($participants);
		$this->db->query($status);
		$this->db->query($threads);
		
		$fb_settings = [
			'slug' => 'mahana_subject_as_stream',
			'title' => 'Stream connected to subject',
			'description' => 'You can treat subjects of threads as IDs of a stream',
			'`default`' => '0',
			'`value`' => '0',
			'type' => 'select',
			'`options`' => 'func:mahana/mahana/get_streams',
			'is_required' => 0,
			'is_gui' => 1,
			'module' => 'mahana'
		];
		
		$this->db->insert('settings', $fb_settings);
		
		return true;
    }
    
    public function uninstall()
    {
    	$this->dbforge->drop_table('mahana_messages');
    	$this->dbforge->drop_table('mahana_participants');
    	$this->dbforge->drop_table('mahana_status');
    	$this->dbforge->drop_table('mahana_threads');
    	
    	$this->db->delete('settings', ['slug'=>'subject_as_stream']);
    	
    	return true;    
    }
 
 
    public function upgrade($old_version)
    {
        // Your Upgrade Logic
        return true;
    }
 
    public function help()
    {
        // Return a string containing help info
        return "Here you can enter HTML with paragrpah tags or whatever you like";
 
        // or
 
        // You could include a file and return it here.
        //return $this->load->view('help', null, true); // loads modules/sample/views/help.php
    }
}