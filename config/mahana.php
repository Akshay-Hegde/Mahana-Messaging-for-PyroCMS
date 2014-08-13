<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['msg_status_unread']				= 0;
$config['msg_status_read']					= 1;
$config['msg_status_archived']				= 2;

// priority
$config['priority_low']						= 1;
$config['priority_normal']					= 2;
$config['priority_high']					= 3;
$config['priority_urgent']					= 4;

// status return message codes
$config['msg_success']						= 0;
$config['msg_err_general']					= 1;
$config['msg_err_invalid_user_id']			= 2;
$config['msg_err_invalid_msg_id']			= 3;
$config['msg_err_invalid_thread_id']		= 4;
$config['msg_err_invalid_status_id']		= 5;
$config['msg_err_invalid_sender_id']		= 6;
$config['msg_err_invalid_recipients']		= 7;
$config['msg_message_sent']					= 8;
$config['msg_status_update']				= 9;
$config['msg_participant_added']			= 10;
$config['msg_err_participant_exists']		= 11;
$config['msg_err_participant_nonsystem']	= 12;
$config['msg_participant_removed']			= 13;