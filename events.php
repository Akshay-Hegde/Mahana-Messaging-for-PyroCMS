<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Exception handler Events Class
 *
 * @category    Mahana messaging
 * @author      Ben Rogmans
 */
class Events_Mahana {

    protected $ci;

    public function __construct() {

        $this->ci =& get_instance();
        Events::register('public_controller', array($this, 'run'));
    }

    public function run() {

        $this->ci->load->config('mahana/mahana');
        $this->ci->load->model('mahana/mahana_model');
        $this->ci->lang->load('mahana/mahana');
        $this->ci->load->library('mahana/mahana');
    }
}