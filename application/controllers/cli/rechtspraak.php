<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

define('DS', DIRECTORY_SEPARATOR);

require 'pgbrowser.php';

class Rechtspraak extends CI_Controller {

    private $results = array();

    public function __construct() {
        parent::__construct();
        ini_set('error_log', 'application' . DS . 'logs' . DS . 'rechtspraak.log');
        // $this->load->model('Rechtspraak_Model');
        $this->load->helper('url');
        $this->results = array();
    }

    private function get_set($set, $arrs) {
        $url = 'http://namenlijst.rechtspraak.nl/default.aspx';
        $b = new PGBrowser();
        $page = $b->get($url); // make initial connect

        error_log("Fetching $set\n");
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

        error_log("First Page of $set\n");
        while (true) {
            foreach ($page->xpath->query("//table[@id='resultaat']//tbody//tr//input[@class='mimichyperlink']") as $el) {
                print("Set $set Name: " . $el->getAttribute('value') . "\n");
                $subform = $page->form();
                $subform->set($el->getAttribute('name'), $el->getAttribute('value'));
                $detailspage = $subform->submit();
                $item = array('set' => $set, 'name' => $el->getAttribute('value'),
                    'html' => $detailspage->html);
                $this->results[] = $item;
                sleep(2);
            }

            $next = $page->xpath->query("//div[@class='mimicpager']/input[@class='next']");
            if ($next->length == 0)
                break;

            $form = $page->form();
            $form->set('ctl00$ContentPlaceHolder1$lbNext', 'Volgende');
            $page = $form->submit();
            error_log("Next page of $set\n");
        }
        error_log("Finished Fetching $set\n");
    }

    public function extract() {
        if (!$this->input->is_cli_request()) {
            error_log("(Illegal) Web Access Attempt on Rechstpraak Crawler");
            die();
        }

// rechtbanken (/div#chklCourts
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
                'rechtbanken' => 'rechtbanken'));
        }

        // gerechtshoven (div#chklCourtsOfAppeal)
        $snames = array('RS0055' => 'Amsterdam', 'RS0056' => 'Arnhem-Leeuwarden', 'RS0057' => 'Den Haag', 'RS0058' => 's-Hertogenbosch');
        $collection = array('ctl00$ContentPlaceHolder1$chklCourtsOfAppeal$0' => 'RS0055',
            'ctl00$ContentPlaceHolder1$chklCourtsOfAppeal$1' => 'RS0056',
            'ctl00$ContentPlaceHolder1$chklCourtsOfAppeal$2' => 'RS0057',
            'ctl00$ContentPlaceHolder1$chklCourtsOfAppeal$3' => 'RS0058');

        foreach ($collection as $key => $set) {
            $this->get_set("Rechtshof " . $snames[$set], array("$key" => "$set",
                'ctl00$ContentPlaceHolder1$hiddenFieldGerechtshovenChecked' => 'true',
                'gerechtshoven' => 'gerechtshoven'));
        }

        $set = 'College van Beroep voor het bedrijfsleven';
        $this->get_set($set, array('ctl00$ContentPlaceHolder1$chklInstances$2' => $set));

        $set = 'Centrale Raad van Beroep';
        $this->get_set($set, array('ctl00$ContentPlaceHolder1$chklInstances$1' => $set));

        $set = 'Hoge Raad';
        $this->get_set($set, array('ctl00$ContentPlaceHolder1$chklInstances$0' => $set));

        $json = json_encode($this->results);
        file_put_contents("rechtspraak.json", $json); // where does this go to?

        $filename = 'rechtspraak-' . date('Y-m-d') . '.json.gz';
        if (!file_exists($filename)) {
            $zh = gzopen($filename, 'w') or error_log("can't open: $php_errormsg");
            if (-1 == gzwrite($zh, $json)) {
                error_log("can't write: $php_errormsg");
            }
            gzclose($zh) or error_log("can't close: $php_errormsg");
        }
        return;
    }

    public function transform() {
        $file = file_get_contents("rechtspraak.json");
        $data['json'] = json_decode($file, true);

        foreach ($data['json'] as $record) {
            $filename = 'rechtspraak' . DS . urlencode($record['name']) . '.txt';
            echo 'SET:' . $record['set'] . ' NAME:' . $record['name'] . ' FILE:' . $filename . "\n";
            $result[] = array('set'=>$record['set'],  'name'=>$record['name'],  'file'=>$filename);

            file_put_contents($filename, $record['html']);
        }
        $json = json_encode($result);
        file_put_contents("rechtspraak-index.json", $json); // where does this go to?
    }

}
?>



