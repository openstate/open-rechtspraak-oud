<?php

class Familienamen_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model('Rechtspraak_model');
        //$this->load->model('Es_model');
    }

    /*
     * Yields a hash that can be used to enrich data 
     */

// Enricher should be developed further
    public function enrich_lastname($lastname, $timestamp) {
        $indextype = 'familienamen';


        print("\tEnriching " . $lastname . "\n");

        $extr = $this->extract($lastname, $timestamp);
        die(" end of extract\n");
        $res = $this->transform($extr);

        // now return value from transformDB;
        return $res['_source']['item'];
        die();
    }

    /*
     *  //http://www.cbgfamilienamen.nl/nfb/lijst_namen.php?operator=eq&naam=van+roomen
     *  YIELDS country of origin at the very least! and tag other or typically dutch
     */

    private function extract($lastname, $inserted) {
        $index = "familienamen_e";
        $indextype = 'familienamen';

        // already exists ?
        try {
            $fields = [ 'name' => $lastname];
            $res = $this->Es_model->exists($index, $indextype, $fields);
        } catch (Exception $e) {
            $json = json_decode($e->getMessage());
            if ($json->error->root_cause[0]->type === "index_not_found_exception") {
                $this->Rechtspraak_model->create_extract_index($index);
                $res = false;
            } else {
                throw Exception($e);
            }
        }

        if ($res != false)
            return $res;

        print("crawling $lastname\n");
    
    //        $link = 'http://www.cbgfamilienamen.nl/nfb/lijst_namen.php?operator=eq&naam='. urlencode($lastname);


        $qry = http_build_query(array('operator' => 'eq', 'naam'=>$lastname ) ) ;
        $link = 'http://www.cbgfamilienamen.nl/nfb/lijst_namen.php?' . $qry;

        var_dump($link);
        
        error_reporting(~0);
ini_set('display_errors', 1);
        $response = file_get_contents($link);
//https://stackoverflow.com/questions/3519939/how-can-i-find-where-i-will-be-redirected-using-curl
        var_dump($response);
        die();


        $doc = new DOMDocument();

        $doc->loadHTML($response); // add @ to supress eerrors
        $xpath = new DOMXpath($doc);

        $set = $xpath->query("//li[@class='optie']/a/@href");
        // case  of Uw zoekactie naar van Boetzelaer-Gulyás heeft geen resultaat.
        foreach ($set as $element) {
            $ele_link = "http://www.cbgfamilienamen.nl/nfb/" . $element->value;
            $html = file_get_contents($ele_link);
            die("putting stuff somewhere in");
            // insert stuff into extract
            $res = $this->Rechtspraak_model->put_item(md5($lastname), $lastname, $html, $index, $indextype, null, $inserted);

            break; // andere linkjes negeren wij.
        }

        return $res;
    }

    /*
     * transforms html
     */

    private function transform($record) {
        var_dump($record);
        die();

        $ele_doc = new DOMDocument();
        $ele_doc->loadHTML($html); // add @ to supress eerrors
        $ele_xpath = new DOMXpath($ele_doc);

        $ele_set = $ele_xpath->query("//div[@id='analyse_en_verklaring']/descendant::node()");

        foreach ($ele_set as $ele_ele) {
            // <p><strong>verklaring: </strong><br>De familienaam Yildirim is afkomstig uit Turkije.</p>

            if (trim($ele_ele->nodeValue) === "") {
                continue;
            }
            $result[urlencode(trim($ele_ele->nodeValue))] = trim($ele_ele->nodeValue);
        }


        if (!isset($result[urlencode("andere taal")]))
            return ["err_message" => "message: response\n"];
        // 'verklaring%3A+De+familienaam+Yildirim+is+afkomstig+uit+Turkije.'
        //verklaring%3A+De+familienaam+Farahani+is+ondermeer+afkomstig+uit+Iran.
        $countries = ["Turkije", "Iran"]; //Maghreb. Marokko, Suriname.
        // grep afkomstig
        if ($result == null)
            return ["err_message" => "message: empty response\n"];

        var_dump($result);
        return $result;
    }

}
?>

