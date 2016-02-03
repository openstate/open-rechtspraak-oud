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
        //  $this->output->cache(60);
    }

    function index() {

        // get the index
        $file = file_get_contents("rechtspraak-index.json");
        $data['json'] = json_decode($file, true);


        // render page
        $this->load->library('parser');
        $this->parser->parse('header', array('dummy' => 'dummy'));
        $this->parser->parse('relations_view', $data); //, $json); //default view
        $this->load->view('footer');
    }

    function json() {

        // get the index
        $file = file_get_contents("rechtspraak-index.json");
        $data['json'] = json_decode($file, true);

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($data['json']));
    }

    public function instantie($enc_set, $enc_name) {
        // get ALL the fucking data
        //  die("$enc_name");
        if ($enc_name == "mw.+mr.+D.M.+Staal+") {
            die("404");
        }


        $set = urldecode($enc_set);
        $name = urldecode($enc_name);


        $file = file_get_contents("rechtspraak-index.json");
        $json = json_decode($file, true);

        foreach ($json as $element) {
            if ($element['set'] == $set && $element['name'] == $name) {
                $html = file_get_contents($element['file']);
                $data['json'] = array('set' => $element['set'], 'name' => $element['name'], 'html' => $html);
            }
        }

        if (!isset($data['json'])) {
            error_log("ERRoR in RELATIONS SHOWING STUF TO CLIENTS");
            die("error in relations controller!");
        }

        // render page
        $this->load->library('parser');
        $this->parser->parse('header', array('dummy' => 'dummy'));
        $this->parser->parse('relation_view', $data); //, $json); //default view
        $this->load->view('footer');
    }

}

/* End of file relations.php */
/* Location: ./application/controllers/relations.php */