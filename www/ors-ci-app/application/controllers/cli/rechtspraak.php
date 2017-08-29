<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

define('DS', DIRECTORY_SEPARATOR);

require_once 'pgbrowser.php';

// CLI controller combines sources, enrichers,
// backups and restores database for recovery (extraction pipe)
class Rechtspraak extends CI_Controller {

    private $results = array();

    public function __construct() {
        parent::__construct();
        ini_set('error_log', 'rechtspraak.error.log');
        $this->load->model('Rechtspraak_model');
        $this->load->model('Namenlijst_model');
        $this->load->helper('url');
        $this->results = array();
        if (!$this->input->is_cli_request()) {
            error_log("(Illegal) Web Access Attempt on Rechstpraak Crawler");
            die();
        }
    }

    /*
     * Backups indexes with from Namenlijst (to be extended with new models)
     */

    public function backup() {
        try {
            $this->Rechtspraak_model->backup();
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    /*
     * Restores all backup files in backups/* to Namenlijst
     */

    public function restore() {
        try {
            $this->Rechtspraak_model->restore();
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    /*
     * Extracts data from Namenlijst Model to dotm in backups/
     */

    public function extract() {
        try {
            $this->Namenlijst_model->extract();
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    /*
     * Extracts data from Uitspraken Model
     */

    public function extract_u() {
        try {
            $this->Uitspraken_model->extract();
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    /*
     *  Transforms data from extracted index namenlijst
     */

    public function transform() {
        try {
            $this->Namenlijst_model->transform();
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

     public function transform_u() {
        try {
            $this->Uitspraken_model->transform();
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    /*
     *  Loads data from transnformed index namenlijst
     */

    public function load() {
        try {
            $this->Namenlijst_model->load();
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    public function load_u() {
        try {
            $this->Uitspraken_model->load();
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    /*
     * Enricher
     */

    public function enrich() {
        try {

           // $this->Rechtspraak_model->enrich_family();

            $this->Rechtspraak_model->enrich_basic();

        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

     /*
     * assumes rechtspraak.json in 'backups/old-data' directory
     * assumes full import can be realized
     * transformes some index stuff to compatability
     */

    function import_old() {
        try {
            $this->Rechtspraak_model->import_old();
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }


}
?>



