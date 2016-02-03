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
        $json = json_decode($file, true);

        // {"set":"Rechtbank Amsterdam","name":"mw. mr. J.F. Aalders ","file":"rechtspraak\/mw.+mr.+J.F.+Aalders+.txt"},
        // -->
        // {"set":"Rechtbank Amsterdam","name":"mw. mr. J.F. Aalders ","uri"},
        $newjson = array();
        foreach ($json as $element) {
            $uri = site_url(array('relations', 'instantie', urlencode($element["set"]),
                urlencode($element["name"]), "json"));

            $newjson[] = array("set" => $element["set"], "name" => $element["name"], "uri" => $uri);
        }
//var_dump($newjson);
//die();
        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($newjson));
    }

    public function instantie($enc_set, $enc_name, $option = "noJson") {

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
            error_log("Error");
            die("error in relations controller!");
        }

        if ($option === "json") {
            $json = $data['json'];

            $doc = new DOMDocument();
            @$doc->loadHTML($json['html']);
            $xpath = new DOMXpath($doc);

            $elements = $xpath->query("//div[@class='details']");
            if ($elements->length == 1) {
                $details = $doc->saveHTML($elements->item(0));
            } else {
                error_log("Page didn't contain div@[class=details]");
                echo "<i>Foutmelding aan 112: HTML data bevat geen div@[class=details]</i>";
            }

            $newjson = array('name' => $json['name'], 'set' => $json['set'], 'details' => $details);
            $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($newjson));
            return;
        } else {
            // render page
            $this->load->library('parser');
            $this->parser->parse('header', array('dummy' => 'dummy'));
            $this->parser->parse('relation_view', $data); //, $json); //default view
            $this->load->view('footer');
        }
    }

}

/* End of file relations.php */
/* Location: ./application/controllers/relations.php */