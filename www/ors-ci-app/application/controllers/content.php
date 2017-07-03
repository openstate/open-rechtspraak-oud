<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

define('DS', DIRECTORY_SEPARATOR);

class Content extends CI_Controller {

    var $data = array(); //= array();

    public function __construct() {
        parent::__construct();
        //$this->output->enable_profiler(TRUE);
        $this->load->library('parser');
        //$this->load->helper('form');
        $this->load->helper('url');
    }

    public function page($resource = null) {
        $this->load->library('parser');
        $this->parser->parse('header', $this->data);

        $base_path = getcwd() . DS . 'application'
                . DS . 'views' . DS . 'page' . DS;

        if (!isset($resource)) {
            $this->data['page'] = 'home';
        } else {
            $this->data['page'] = $resource;
        }

        $this->parser->parse('content_view', $this->data);
        $this->load->view('footer');
    }

    public function index() {
        $this->page();
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */