<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Relations extends CI_Controller {

    public function __construct() {
        parent::__construct();

        // $this->output->enable_profiler(TRUE);
        $this->load->library('parser');
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('table');
        // $this->output->cache(15);
    }

    function index() {
        // get ALL the fucking data
        $file = file_get_contents("names-rechtspraak.json");
        $data['json'] = json_decode($file, true);

        // render page
        $this->load->library('parser');
        $this->parser->parse('header', array('dummy' => 'dummy'));
        $this->parser->parse('relations_view', $data); //, $json); //default view
        $this->load->view('footer');
    }

    public function instantie($enc_set, $enc_name) {
        // get ALL the fucking data
        $set = urldecode($enc_set);
        $name = urldecode($enc_name);

        $file = file_get_contents("names-rechtspraak.json");
        $json = json_decode($file, true);

        foreach ($json as $element) {
            if ($element['set'] == $set && $element['name'] == $name) {                
                $data['json'] = $element;
            }
        }
        
        if (!isset($data['json']))
            die("Cry FOOL TO those who opose us!");
            
        // render page
        $this->load->library('parser');
        $this->parser->parse('header', array('dummy' => 'dummy'));
        $this->parser->parse('relation_view', $data); //, $json); //default view
        $this->load->view('footer');
    }
   
}

/* End of file relations.php */
/* Location: ./application/controllers/relations.php */