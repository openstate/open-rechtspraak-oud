<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Rechtspraak extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('parser');
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('table');
//  $this->output->cache(60);//prod
        $this->load->model('Rechtspraak_model');
        $this->load->model('Es_model');
    }

    function personen($param) {
        try {
            $wrapper['results'] = $this->Rechtspraak_model->list_all_persons($param);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

        // render page
        $this->load->library('parser');
        $this->parser->parse('header', array('dummy' => 'dummy'));
        $this->parser->parse('personen_view', $wrapper);
        $this->load->view('footer');
    }

    function persoon($enc_name) {
        $wrapper['name'] = urldecode($enc_name);

        try {
            $wrapper['results'] = $this->Rechtspraak_model
                    ->get_person($wrapper['name']);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
        //var_dump($wrapper['results']);
        // render page
        $this->load->library('parser');
        $this->parser->parse('header', array('dummy' => 'dummy'));
        $this->parser->parse('persoon_view', $wrapper);
        $this->load->view('footer');
    }

    public function mapping() {
        $this->output->enable_profiler(FALSE);
        $data = $this->Es_model->get_mapping();
        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($data));
    }

    public function status() {
        $this->output->enable_profiler(FALSE);
        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($this->Es_model->get_status()));
    }

}

/* End of file relations.php */
/* Location: ./application/controllers/relations.php */
?>