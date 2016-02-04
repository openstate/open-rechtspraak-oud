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

        $newjson = array();
        foreach ($json as $element) {
            $uri = site_url(array('relations', 'instantie', urlencode($element["set"]),
                urlencode($element["name"]), "json"));

            $newjson[] = array("set" => $element["set"], "name" => $element["name"], "uri" => $uri);
        }

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($newjson));
    }

    public function parseData($details) {
        $data = array();
// $details = $doc->saveHTML($elements->item(0));    
        $doc = new DOMDocument();
        @$doc->loadHTML($details);

        $divs = $doc->getElementsByTagName('div');

        foreach ($divs as $child) {

            if (isset($data['fulltext'])) {
                if (strlen($child->nodeValue) > strlen($data['fulltext']) ) 
                             $data['fulltext'] = $child->nodeValue;    
            } else $data['fulltext'] = $child->nodeValue;
            foreach ($child->childNodes as $gchild) {
                if (!isset($gchild->tagName))
                    continue; //drop non marked shit
                if ($gchild->tagName == "h1" || $gchild->nodeName == "h1")
                    continue; //readdaway h1 headline
                if ($gchild->tagName == "div" || $gchild->nodeName == "div")
                    continue; //readdaway h1 headline
                if ($gchild->tagName == "h2" || $gchild->nodeName == "h2") {
                    // set context variable for saving
                    $contextheader = urlencode(trim($gchild->nodeValue));
                    //  echo "Context HEADER set $contextheader\n<br/>";
                    continue;
                }
                if ($gchild->tagName == "dl" || $gchild->nodeName == "dl") {
                    // subloop for values dt and dd
                    foreach ($gchild->childNodes as $sgchild) {
                        if (!isset($sgchild->tagName)) {
                            continue; //drop non marked shit
                        }
//                        print $sgchild->tagName . " " . $sgchild->nodeName .
//                                " LEN:" . strlen($sgchild->nodeValue) . " VALUE: " .
//                                trim($sgchild->nodeValue) . "\n<br/>";
                        if ($sgchild->tagName == "dt" || $sgchild->nodeName == "dt") {
                            // set context variable for saving
                            $contextdt = urlencode(trim($sgchild->nodeValue));
                            //  echo "Context DT set " . $contextheader . $contextdt . "\n<br/>";
                            continue;
                        } else if ($sgchild->tagName == "dd" || $sgchild->nodeName == "dd") {
                            if ($contextheader == null)
                                die("contextheader for data missing");
                            if ($contextdt == null)
                                die("contextdd for data missing");

                            $index = $contextheader . $contextdt;
                            $value = trim($sgchild->nodeValue);
                            $data[$index] = $value;
                            //   print("<b>$index </b> $value \n<br/>");
                            $contextdt = null;
                        }
//                        $contextdt = null;
                    }
//                 $contextdt = null;
                }
                // $contextheader = NULL;
            }
        }
        return $data;
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
                $data = $this->parseData($details);
            } else {
                error_log("Page didn't contain div@[class=details]");
                echo "<i>Foutmelding: HTML data bevat geen div@[class=details]</i>";
                return;
            }

            $newjson = array('name' => $json['name'], 'set' => $json['set'], 'data' => $data);
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