<?php

// inlcude once Elastic search libr
//require_once 'pgbrowser.php';
//require_once 'eswrapper.php';

class Namenlijst_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model('Rechtspraak_model');
        $this->load->model('Es_model');
    }
                
    
    private function extr_Courts($inserted) {
        //// rechtbanken (/div#chklCourts
        $snames = array('AR0040' => 'Amsterdam', 'AR0041' => 'Noord-Holland',
            'AR0042' => 'Midden-Nederland', 'AR0043' => 'Noord-Nederland',
            'AR0045' => 'Den Haag', 'AR0046' => 'Rotterdam', 'AR0047' => 'Limburg',
            'AR0048' => 'Oost-Brabant', 'AR0049' => 'Zeeland-West-Brabant',
            'AR0050' => 'Gelderland', 'AR0051' => 'Overijssel');
        $collection = array('ctl00$ContentPlaceHolder1$chklCourts$0' => 'AR0040',
            'ctl00$ContentPlaceHolder1$chklCourts$1' => 'AR0041',
            'ctl00$ContentPlaceHolder1$chklCourts$2' => 'AR0042',
            'ctl00$ContentPlaceHolder1$chklCourts$3' => 'AR0043',
            'ctl00$ContentPlaceHolder1$chklCourts$4' => 'AR0045',
            'ctl00$ContentPlaceHolder1$chklCourts$5' => 'AR0046',
            'ctl00$ContentPlaceHolder1$chklCourts$6' => 'AR0047',
            'ctl00$ContentPlaceHolder1$chklCourts$7' => 'AR0048',
            'ctl00$ContentPlaceHolder1$chklCourts$8' => 'AR0049',
            'ctl00$ContentPlaceHolder1$chklCourts$9' => 'AR0050',
            'ctl00$ContentPlaceHolder1$chklCourts$10' => 'AR0051');

        foreach ($collection as $key => $set) {
            $this->get_set("Rechtbank " . $snames[$set], array("$key" => "$set",
                'ctl00 $ContentPlaceHolder1$hiddenFieldGerechtshovenChecked' => 'false',
                'ctl00$ContentPlaceHolder1$hiddenFieldRechtbankenChecked' => 'true',
                'rechtbanken' => 'rechtbanken'), $inserted);
        }
    }

    private function extr_CourtsOfAppeal($inserted) {
        // gerechtshoven (div#chklCourtsOfAppeal)
        $snames = array('RS0055' => 'Amsterdam', 'RS0056' => 'Arnhem-Leeuwarden', 'RS0057' => 'Den Haag', 'RS0058' => 's-Hertogenbosch');
        $collection = array('ctl00$ContentPlaceHolder1$chklCourtsOfAppeal$0' => 'RS0055',
            'ctl00$ContentPlaceHolder1$chklCourtsOfAppeal$1' => 'RS0056',
            'ctl00$ContentPlaceHolder1$chklCourtsOfAppeal$2' => 'RS0057',
            'ctl00$ContentPlaceHolder1$chklCourtsOfAppeal$3' => 'RS0058');

        foreach ($collection as $key => $set) {
            $this->get_set("Rechtshof " . $snames[$set], array("$key" => "$set",
                'ctl00$ContentPlaceHolder1$hiddenFieldGerechtshovenChecked' => 'true',
                'gerechtshoven' => 'gerechtshoven'), $inserted);
        }
    }

    private function extr_HigherCourts($inserted) {
        // overige hoge raden
        $types = array(/* record example */
               'Centrale Raad van Beroep' => 'ctl00$ContentPlaceHolder1$chklInstances$1'
            , 
            'Hoge Raad' => 'ctl00$ContentPlaceHolder1$chklInstances$0'
                 ,            'College van Beroep voor het bedrijfsleven' => 'ctl00$ContentPlaceHolder1$chklInstances$2' 
        );

        foreach ($types as $key => $value) {
            $set = $key;
            $this->get_set($set, array($value => $set), $inserted);
        }
    }

    public function extract() {

        $inserted = $this->Rechtspraak_model->get_timestamp();

        $this->extr_Courts($inserted);
        $this->extr_CourtsOfAppeal($inserted);
        $this->extr_HigherCourts($inserted);
    }

    private function get_set($set, $arrs, $inserted) {
        $index = "rechtspraak_e";
        $indextype = 'namenlijst';

        $res = $this->Rechtspraak_model->create_extract_index($index);

        $url = 'https://namenlijst.rechtspraak.nl/default.aspx';
        $b = new PGBrowser();
        $page = $b->get($url); // make initial connect

        error_log("Fetching $set");
        $form = $page->form();
        // some pretty much default settings
        $form->set('ctl00$ContentPlaceHolder1$hiddenFieldGerechtshovenChecked', 'false');
        $form->set('ctl00$ContentPlaceHolder1$hiddenFieldRechtbankenChecked', 'false');
        $form->set('ctl00$ContentPlaceHolder1$ddlFunctions', 'alle functies');
        $form->set('ctl00$ContentPlaceHolder1$hiddenFieldSelectedFunction', 'alle functies');
        $form->set('ctl00$ContentPlaceHolder1$txtSearchKenmerken', '');
        $form->set('ctl00$ContentPlaceHolder1$btnSearch', 'Zoeken');
        // some pretty much default settings

        foreach ($arrs as $key => $value) {
            $form->set($key, $value);
        }

        $page = $form->submit();

        error_log("First Page of $set");
        while (true) {

            foreach ($page->xpath->query("//table[@id='resultaat']//tbody//tr//input[@class='mimichyperlink']") as $el) {
                $subform = $page->form();
                $subform->set($el->getAttribute('name'), $el->getAttribute('value'));
                $detailspage = $subform->submit();
                $item = $detailspage->html;
                print("\tExtracting " . $el->getAttribute('value') . "inserted $inserted\n");
                $res = $this->Rechtspraak_model
                        ->put_item(md5($el->getAttribute('value')), $el->getAttribute('value'), $item, $index, $indextype, $set, $inserted);
            }

            $next = $page->xpath->query("//div[@class='mimicpager']/input[@class='next']");
            if ($next->length == 0) {
                print("No next page found\n");
                break;
            }
            $form = $page->form();
            $form->set('ctl00$ContentPlaceHolder1$lbNext', 'Volgende');
            $page = $form->submit();
            error_log("Next page of $set");
        }
        error_log("Finished Fetching $set");
    }

    /*
     * By default only transforms data from newer timestamps (no recovery)
     */

    public function transform() {
        $src_index = "rechtspraak_e";
        $dst_index = "rechtspraak_t";
        $res = $this->Rechtspraak_model->create_transform_index($dst_index);
        $indextype = 'namenlijst';
        $updatedfield = 'inserted';
        $sortfield = 'inserted';
        // get most recent timestamp from transform index (rechtspraak_t/namenlijst field= inserted

        try {
            $from = $this->Es_model->get_most_recent_value($dst_index, $indextype, $updatedfield);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
            echo "dit betekent wsl. dat er geen data is";
        }
        print("\nTransforming data from $from\n");

        $scroll_id = null;
        $vol = 0;
        while (true) {
            $data = $this->Es_model->get_all_it_sortable_from($src_index, $indextype, $scroll_id, $sortfield, $from);

            if (count($data) == 0) {
                break;
            }

            foreach ($data as $item) {
                print("\tTransforming '" . $item["_source"]["name"] . "' inserted: " . $item["_source"]["inserted"] . "\n");
                $t_item = $this->transform_e_item($item['_source']['item']); // html --> json

                $tname = trim($item["_source"]["name"]);

                $res = $this->Rechtspraak_model->put_item(
                        md5($tname), $tname, json_encode($t_item)
                        , $dst_index, $indextype, $item["_source"]["type"], $item["_source"]["inserted"]);
            }
            // end do 
            $vol += count($data);
            print(" Items processed $vol\n");
        }


        return true;
    }

    // generiek maken 
    private function transform_e_item($e_item) {
        $doc = new DOMDocument();

        $doc->loadHTML($e_item); // add @ to supress eerrors
        $xpath = new DOMXpath($doc);

        $elements = $xpath->query("//div[@class='details']");
        if ($elements->length == 1) {
            $details = $doc->saveHTML($elements->item(0)); //no clue about this ;-)          
            $t_item = $this->transform_details($details);
        } else {
            error_log("Page didn't contain div@[class=details]");
            echo "<i>Foutmelding: HTML bron data bevat geen div@[class=details]</i>";
            die("die transform_e_item");
        }

        return $t_item;
    }

    private function transform_details($details) {
        $data = array();
        $doc = new DOMDocument();
        @$doc->loadHTML($details);

        $divs = $doc->getElementsByTagName('div');

        foreach ($divs as $child) {
            //    $data['fulltext'] = $child->nodeValue;//werkt (maar niet altijd???? check relations oude code?
            foreach ($child->childNodes as $gchild) {
                if (!isset($gchild->tagName)) {
                    //             print "A dropping NodeName:'" . $gchild->nodeName . "' Value:'" . $gchild->nodeValue . "'\n";
                    continue; //drop non tagged shit such as #text
                }
                if ($gchild->tagName == "h1" || $gchild->nodeName == "h1") {
                    //         print "B dropping NodeName:'" . $gchild->nodeName . "' Value:'" . $gchild->nodeValue . "'\n";
                    continue; //readdaway h1 headline
                }
                if ($gchild->tagName == "div" || $gchild->nodeName == "div") {
                    //            print "C dropping NodeName:'" . $gchild->nodeName . "' Value:'" . $gchild->nodeValue . "'\n";
                    continue; //readdaway div headline
                }
                if ($gchild->tagName == "h2" || $gchild->nodeName == "h2") {
                    $contextheader = (trim($gchild->nodeValue)); //urlencode
                    //            echo "Context HEADER set '$contextheader'\n";
                    continue;
                }
                if ($gchild->tagName == "dl" || $gchild->nodeName == "dl") {// parse dl counter for multiple values
                    $hash = array();
                    // subloop for values dt and dd
                    foreach ($gchild->childNodes as $sgchild) {
                        if (!isset($sgchild->tagName)) {
                            //               print "D dropping NodeName:" . $sgchild->nodeName . " Value:'" . $gchild->nodeValue . "'\n";
                            continue; //drop non tagged shit (regullary does #text str=Functie Raadsheer Instantie Hoge Raad Datum ingang 01-02-2012 
                        }
                        if ($sgchild->tagName == "dt" || $sgchild->nodeName == "dt") {
                            $contextdt = (trim($sgchild->nodeValue)); //urlencode
                            //          echo "Context DT set '" . $contextheader . $contextdt . "'\n";
                            continue;
                        } else if ($sgchild->tagName == "dd" || $sgchild->nodeName == "dd") {
                            if ($contextheader == null) {
                                die("contextheader for data missing");
                            }
                            if ($contextdt == null) {
                                //print "F contextdd for data missing inspecting NodeName:'" . $sgchild->nodeName . "' Value:'" . $gchild->nodeValue . "'\n";
                                $contextdt = "GEEN VELDNAAM AANGEGEVEN";
                            }
                            //       print "E saving NodeName:'" . $sgchild->nodeName . "' Value:'" . $gchild->nodeValue . "'\n";
                            $value = trim($sgchild->nodeValue);
                            // print(" '".utf8_decode($value). "' ");
                            $hash[$contextdt] = utf8_decode($value); //urlencode($value); 
                            //this is needed because otherwise ES crashes
                            // utf8_encode not working
                            // 
                            $contextdt = null;
                        }
//                        $contextdt = null;
                    }
                    //store data hash in object
                    $data[$contextheader][] = $hash;
//                 $contextdt = null;
                }
                // $contextheader = NULL;
            }
        }
        //  var_dump($data);

        if (count($data) == 0) {
            $a = $doc->getElementsByTagName('p')->item(0)->nodeValue;
            $data['message'] = $a;
        }
        return $data;
    }

    /*
     * By default only loads data from newer timestamps (no recovery)
     */

    public function load() {
        $src_index = "rechtspraak_t";
        $dst_index = 'rechtspraak_l';
        $res = $this->Rechtspraak_model->create_load_index($dst_index);
        $indextype = 'namenlijst';
        $sortfield = 'inserted';
        $updatedfield = 'updated';

        try {
            // get most recent timestamp from load index (rechtspraak_l/namenlijst field= inserted
            $from = $this->Es_model->get_most_recent_value($dst_index, $indextype, $updatedfield);
        } catch (Exception $e) {
            $err = json_decode($e->getMessage(), true);
            if ("No mapping found for [updated] in order to sort on" == $err['error']["root_cause"][0]["reason"]) {
                // print("setting nulll");
                $from = null;
            } else {
                print ('Caught exception: ' . $e->getMessage() . "\n\n");
                die();
            }
        }
        $scroll_id = null;
        $vol = 0;
        while (true) {
            $data = $this->Es_model->get_all_it_sortable_from($src_index, $indextype, $scroll_id, $sortfield, $from);
            if (count($data) == 0) {
                break;
            }

            foreach ($data as $item) {
                print("loading '" . $item["_source"]["name"] . "' doctype " . $item['_source']['type'] . " inserted: " . $item["_source"]["inserted"] . "\n");
                $this->Rechtspraak_model->load_item(
                        $item["_source"]["id"], $item["_source"]["name"], json_decode($item['_source']['item'], true), $dst_index, $indextype, $item['_source']['type'], $item["_source"]["inserted"]);          
            }
            // end do 
            $vol += count($data);
            print(" Items processed $vol\n");
        }
        return true;
    }

}
?>

