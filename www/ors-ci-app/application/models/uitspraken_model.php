<?php

// inlcude once Elastic search libr
//require_once 'pgbrowser.php';
//require_once 'eswrapper.php';

class Uitspraken_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model('Rechtspraak_model');
        $this->load->model('Es_model');
    }

   
    // should go fetch new zip archive
// looks in directory 'uitspraken', browse subfolders 
//requires apt-get install unzip on server// 
    public function extract() {


        $inserted = $this->Rechtspraak_model->get_timestamp();
        try {
            $path = 'uitspraken/OpenDataUitspraken/';
            $entries = scandir($path, SCANDIR_SORT_DESCENDING);

            foreach ($entries as $entry) {
                if (is_dir($path . $entry)) {
                    $subentries = glob($path . $entry . '/*.zip');
                    foreach ($subentries as $filepath) {
                        $this->open_zipfile($filepath, $inserted);
                    }
                }
            }
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    //incomplete
    private function open_zipfile($filepath) {
        $str = "unzip -n $filepath -d tmp/";
        print($str . "\n");
        system($str);

        $entries = scandir("tmp");
        // print_r($entries);
        foreach ($entries as $entry) {
            if (is_file("tmp/" . $entry)) {
                echo "$entry\n";
                //  $file = file_get_contents("tmp/" . $entry);
                $inserted = filemtime("tmp/" . $entry);
                echo "Last modified: " . date("Y-m-d H:m:s", $inserted);
                //  var_dump($file);
//                $res = $this->Rechtspraak_model
//                        ->put_extracted(md5($entry), $entry, $file, 'uitspraak_e', 'bulk', $inserted);
                // put extracted should test if exists based on $inserted and $id
                break;
            }
        }

        //   system("rm tmp/*");
        die();
    }
    
    
    /*
     * By default only transforms data from newer timestamps (no recovery)
     */

    public function transform() {
        
    }

    /*
     * By default only loads data from newer timestamps (no recovery)
     */

    public function load() {
        
    }

}
?>

