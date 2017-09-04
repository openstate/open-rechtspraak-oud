<?php

class Rechtspraak_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model('Es_model');
        $this->load->model('Hash_model');
    }

    private function startswith($str, $start) {
        $res = substr($str, 0, strlen($start)) == $start ? true : false;
        return $res;
    }

    private function substr_str($str, $start) {
        return substr($str, strlen($start));
    }

    private function parse_sex($name, &$result) {
        $male = 'dhr. ';
        $female = 'mw. ';

        if ($this->startswith($name, $male)) {
            $result['Geslacht'] = 'man';
            $name = $this->substr_str($name, $male);
//            print(" nieuwe naam is $name\n");
        } elseif ($this->startswith($name, $female)) {
            $result['Geslacht'] = 'vrouw';
            $name = $this->substr_str($name, $female);
//            print(" nieuwe naam is $name\n");
        } else
            die('enrichment error');
        return $name;
    }

    private function parse_pretitles($name, &$result) {
        $pretitles = [
            'dr.', 'drs.', 'prof.', 'ing.', 'ir.', 'mr.', 'jonkheer ', 'baron ', 'barones ', 'Baron ', 'Barones ', 'Graaf ', 'graaf ', 'Kol', 'kol', 'Ktza', 'ktza'
        ];
        foreach ($pretitles as $pretitle) {
            if ($this->startswith($name, $pretitle)) {
                $result['Titels'][] = strtolower(trim($pretitle));
                $name = $this->substr_str($name, $pretitle);

                if ($this->startswith($name, ' ')) {
                    $name = $this->substr_str($name, ' ');
                }
                $name = $this->parse_pretitles($name, $result);
                return $name;
            }
        }

        return $name;
    }

    private function parse_initials($name, &$result) {
        for ($i = 0; $i < strlen($name); $i++) {
            if ($name[$i] == ' ') {
                //        print("I is $i\n");
                $result['Initialen'] = substr($name, 0, $i);
                //       print ("initials are " . $result['initials'] . "\n");
                $name = substr($name, $i + 1);
                //        print("new name is '$name'\n");
                return $name; //break upon space
            }
        }

        die("no initials found");
    }

    private function parse_lastname_and_post_titles($name, &$result) {
        $posttitles = [
            'MPA', 'RA', 'LL.M.', 'MSc.', "UM"
        ];
        $wrds = explode(" ", $name);
        foreach ($posttitles as $posttitle) {
            if (end($wrds) == $posttitle) {
                $result['Titels'][] = $posttitle;
                $name = substr($name, 0, strlen($name) - strlen($posttitle));
                break;
            }
        }
        $result['Achternaam'] = $name;
        return $name;
    }

    private function enrich_basic_name($name) {
        $result;
        $orgname = $name;

        $name = $this->parse_sex($name, $result);
        $name = $this->parse_pretitles($name, $result);
        $name = $this->parse_initials($name, $result);
        $name = $this->parse_pretitles($name, $result);

        $name = $this->parse_lastname_and_post_titles($name, $result);
        return $result;
    }

    /*
     * Enricher : basic 
     */

    public function enrich_basic() {
        $index = 'rechtspraak_l';
        $indextype = 'namenlijst';
        $excludes = ["item", "previous"];
        try {
            $scroll_id = null;
            $vol = 0;
            while (true) {
                $data = $this->Es_model->get_all_ti_exclude($index, $indextype, $scroll_id, $excludes);
                if (count($data) == 0) {
                    break;
                }
                foreach ($data as $element) {
                    if (!isset($element["_source"]["enrichments_basic_name"])) {

                        $res = $this->enrich_basic_name($element['_source']['name']); // yields result that can be added or put in place                    
                        $fields = [ 'enrichments_basic_name' => $res];
                        $this->Es_model->update($index, $indextype, $element["_id"], $fields);
                    }
                }
                $vol += count($data);
                print(" Items processed $vol\n");
            }
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    /*
     * Enricher : family (dev phase)
     */

    public function enrich_family() {
        $this->load->model('Familienamen_model');
        $src_index = 'rechtspraak_l';
        $src_indextype = 'namenlijst';
        $excludes = ["item", "previous"];
        $timestamp = $this->Rechtspraak_model->get_timestamp();

        $scroll_id = null;
        $vol = 0;
        while (true) {
            $data = $this->Es_model->get_all_ti_exclude($src_index, $src_indextype, $scroll_id, $excludes);
            if (count($data) == 0) {
                break;
            }
            foreach ($data as $element) {
                $lastname = $element['_source']["enrichments_basic_name"]["Achternaam"];
                print("Evaluating " . $lastname . "\n");

                if (!isset($element["_source"]["enrichments_family_name"]) &&
                        isset($element["_source"]["enrichments_basic_name"])) {
                    if (isset($element["_source"]["enrichments_family_name"]))
                        continue;

                    $res = $this->Familienamen_model->enrich_lastname($lastname, $timestamp);

                    $fields = [ 'enrichments_family_name' => $res];
                    print("\tWriting " . $element['_source']["enrichments_basic_name"]["Achternaam"] . "\n");

                    die('dsaasdsdahjkadshjk');

                    $this->Es_model->update($index, $indextype, $element["_id"], $fields);
                }
            }
            $vol += count($data);
            print(" Items processed $vol\n");
        }
    }

    /*
     * 
     * VIEWER SPECIFIC
     * 
     */

    public function list_all_persons($param) {
        $result = array();
        $index = 'rechtspraak_l';
        $indextype = 'namenlijst';
        $field = 'name';
        $filter;
        $sortfield = 'updated';
                
        if (!isset($param)) {            // default = current only
           
            $from = $this->Es_model->get_most_recent_value($index, $indextype, $sortfield);
//print ("FROM is $from \n</br>");
           $filter = [ "range" => [
                    $sortfield => [
                        'gte' => $from
            ]]];
           
        } elseif ($param != 'unfiltered') {
            die('wrong parameter');
        }// case everything = default

        var_dump($filter);

        $result = $this->Es_model->get_all_unique_countfield($index, $indextype, $field, $filter);
/* problematic, yields only 3 results (dhr. mr. M.J. Alink	1,mw. mr. L.C. Bachrach	1,mw. mr. L.Z. Achouak El Idrissi	1)*/

        return $result;
    }

    /*
     * Used by CONTROLLER for person page
     */

    public function get_person($name) {
        $index = 'rechtspraak_l';
        $indextype = 'namenlijst';
        $fields = [ 'name' => $name];

        $sortfield = 'updated';
        $sortby = [
            'field' => $sortfield, 'order' => 'asc'
        ];

        $from = $this->Es_model->get_most_recent_value($index, $indextype, $sortfield);
        $positions = $this->Es_model->get($index, $indextype, $fields, $sortby);

        foreach ($positions as &$pos) {

            $this->get_diffs($pos); // should not iterate @todo
            //   var_dump($pos);die();

            if ($from == $pos['_source']["updated"]) {
                $pos['current'] = true;
                $pos['currentstamp'] = $from;
            } else {
                $pos['current'] = false;
                $pos['currentstamp'] = $from;
            }
        }

        return $positions;
    }

    /*
     * diffs by prevs and items
     */

    private function get_diffs(&$pos) {
        $dst = $pos["_source"]["item"];
        foreach ($pos["_source"]["previous"] as &$prev) {
            $src = $prev["previous_item"];
            $prev['diff'] = $this->Hash_model->compare_hash($src, $dst);
            $dst = $src;
        }
    }

    /*
     * Load_item is used by loaders to insert new data into consistent model
     */

    public function load_item($id, $name, $item, $index, $indextype, $doctype, $inserted) {
        // updates or inserts loadable item into a index (pref) rechtspraak_l which is a consistent model
        // which keeps versions of old objects, which can be called upon

        $hit = $this->exists_loaded($id, $name, $index, $indextype, $doctype);

        if ($hit) {
            // print("UPDATE '$name' with doctype $doctype id $id\n");
            $this->update_item($id, $name, $item, $index, $indextype, $doctype, $inserted, $hit);
        } else {
            //    print("INSERT '$name' with doctype $doctype id $id\n");
            //error_log("INSERT '$name' with doctype $doctype id $id");
            $this->insert_item($id, $name, $item, $index, $indextype, $doctype, $inserted);
        }
    }

    /*
     * used by load item to check for new data into consistent model
     */

    private function exists_loaded($id, $name, $index, $indextype, $doctype) {
        // print("**** Exists() for name '$name' id  '$id' with doctype '$doctype' ?\n");
        $fields = [
            /* 'id' => $id */
            'name' => $name
            , 'type' => $doctype
        ];
        return $this->Es_model->exists($index, $indextype, $fields);
    }

    /*
     * used by load item to possibly update an item 
     */

    private function update_item($id, $name, $item, $index, $indextype, $doctype, $inserted, $hit) {

        if ($this->get_epoch($hit['_source']['updated']) >= $this->get_epoch($inserted)) {
            //  print("DROP because of same or newer already inserted\n");
            return;
        }

        if ($this->equals_item($item, $hit['_source']["item"])) {
            //      print("\tempty UPDATE with new inserted... \n");
            $fields = [ 'updated' => $inserted];
            $this->Es_model->update($index, $indextype, $hit["_id"], $fields);
        } else {

            $previous = $hit["_source"]["previous"];
            $previous[] = [ 'updated' => $hit["_source"]["updated"],
                'previous_item' => $hit["_source"]["item"]];

            $fields = [ 'item' => $item,
                'updated' => $inserted,
                'previous' => $previous];

            $this->Es_model->update($index, $indextype, $hit["_id"], $fields);

            // print("updated doc in production " . $name . "\n");
            //error_log("updated in production " . $item_t["name"]);
        }
        return;
    }

    private function equals_item($a, $b) {
        $a_str = json_encode($a);
        $b_str = json_encode($b);

        if (strcmp($a_str, $b_str) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Used by load item to insert an item 
     */

    private function insert_item($id, $name, $item, $index, $indextype, $doctype, $inserted) {
        $fields = [
            'id' => $id,
            'name' => $name,
            'type' => $doctype,
            'inserted' => $inserted,
            'updated' => $inserted,
            'previous' => null,
            'item' => $item
        ];

        $this->Es_model->put($index, $indextype, $fields);
        //  print("INSERT $name in $index with type $indextype doctype $doctype on inserted $inserted\n");
    }

    /*
     * Restore used by CLI
     */

    public function restore() {
        $index = 'rechtspraak_e';
        $dir = "backups/";
        $entries = scandir($dir);
        $indextype = 'namenlijst';

        try {
            $this->create_extract_index($index);

            foreach ($entries as $entry) {
                if (is_file($dir . $entry)) {
                    echo "loading file $entry\n";
                    $file = file_get_contents($dir . $entry);
                    $data = json_decode(gzdecode($file), true);

                    foreach ($data as $item) {

                        // var_dump($item);

                        if (!isset($item['_source']['type'])) {
                            $doctype = $item["_type"];
                        } else {
                            $doctype = $item['_source']['type'];
                        }
                        print("Restoring '" . $item['_source']['name'] . "' from " . $doctype . " inserted "
                                . $item['_source']['inserted'] . "\n");

                        $res = $this->put_item(
                                md5($item['_source']['name']), $item['_source']['name'], $item['_source']['item'], $index, $indextype, $doctype, $item['_source']['inserted']);
                    }
                }
            }
        } catch (Exception $e) {
            if (json_decode($e->getMessage())->error->type != "index_already_exists_exception") {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        }
    }

    /*
     * Backup used by CLI
     */

    public function backup() {
        $index = 'rechtspraak_e';
        $path = 'backups/';

        try {
            $dotm = date('d'); // gives timezone issue but is bS
            @mkdir($path);
            $dir = $path . $dotm . "/";
            @mkdir($dir); 
            // clean directory
            $di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
            $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($ri as $file) {
                $file->isDir() ? rmdir($file) : unlink($file);
            }

            $scroll_id = null;
            $vol = 0;
            while (true) {
                $data = $this->Es_model->get_all($index, $scroll_id);
                //  print ("lengte van de hash:" . count($data) . "\n");
                if (count($data) == 0) {
                    break;
                }
                $json = json_encode($data);

                $gzjson = gzencode($json, 9);
                print ("lengte van de json uncompressed:" . round(strlen($json) / (1024 ))
                        . " KB compressed:" . round(strlen($gzjson) / (1024 )) . " KB\n");

                $filename = $dir . $index . ".VOL-$vol" . ".json.gz";
                file_put_contents($filename, $gzjson);
                print("saving '$filename'\n");
                $vol += count($data);
            }
            // create huge ass zipfile down here
            $cmd = 'tar cvfz ' . $dir . 'fulldump_rechtspraak_e.tar.gz.skip-me ' . $dir . '*';
            system($cmd);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    /*
     * Create extract index (later just gimme one) used by restore and extractors
     */

    public function create_extract_index($index) {
        try {
            $response = $this->Es_model
                    ->create_index($index, $this->extract_index_template());
            print("Created Index $index\n");
            return $response;
        } catch (Exception $e) {
            if (json_decode($e->getMessage())->error->type != "index_already_exists_exception") {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        }
    }

    /*
     * Create transform index (later just gimme one)  used by transformers
     */

    public function create_transform_index($index) {
        try {
            $response = $this->Es_model
                    ->create_index($index, $this->transform_index_template());
            print("Created Index $index\n");
            return $response;
        } catch (Exception $e) {
            if (json_decode($e->getMessage())->error->type != "index_already_exists_exception") {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        }
    }

    /*
     * Create load index (later just gimme one)  used by transformers
     */

    public function create_load_index($index) {
        try {
            $response = $this->Es_model
                    ->create_index($index, $this->load_index_template());
            print("Created Index $index\n");
            return $response;
        } catch (Exception $e) {
            if (json_decode($e->getMessage())->error->type != "index_already_exists_exception") {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        }
    }

    /*
     *  // puts item from restore, transformation and ...?
     */

    public function put_item($id, $name, $item, $index, $indextype, $doctype, $inserted) {

        if ($this->exists_($id, $index, $indextype, $doctype, $inserted)) {
            print("\tDROPPPING / ALREADY EXISTS $name in $index with type $indextype doctype $doctype on inserted $inserted\n");
            error_log("\tDROPPPING / ALREADY EXISTS $name in $index with type $indextype doctype $doctype on inserted $inserted");
            // caused by multiple functions with the same court
            return true;
        }

        $fields = [
            'id' => $id,
            'name' => $name,
            'type' => $doctype,
            'inserted' => $inserted,
            'item' => $item
        ];

        $this->Es_model->put($index, $indextype, $fields);
    }

    /*
     *  Used by put_item to prevent doubels
     */

    public function exists_($id, $index, $indextype, $doctype, $inserted) {
        $fields = [
            'id' => $id,
            'type' => $doctype,
            'inserted' => $inserted
        ];
        return $this->Es_model->exists($index, $indextype, $fields);
    }

    // '2017-06-12 12:55:25' 
    public function get_timestamp() {
        date_default_timezone_set('Europe/Amsterdam');
        $inserted = date('Y-m-d H:i:s');
        print($inserted . "\n");
        return $inserted;
    }

    // returns milliseconds since epoch based on ymd HIS
    public function get_epoch($str) {
        date_default_timezone_set('Europe/Amsterdam');
        //  print("Epch str $str\n");
        $dt = date_create_from_format('Y-m-d H:i:s', $str);
        //  var_dump(date_format($dt, 'U'));

        return date_format($dt, 'U');
    }

    /*
     * Template for extraction index
     */

    public function extract_index_template() {
        $template = [
            'body' => [
                'mappings' => [
                    "_default_" => [// for every document type  in index the same
                        'properties' => [
                            /*   'updated' => [
                              'type' => 'date',
                              'format' => "yyyy-MM-dd HH:mm:ss"
                              ], */
                            'name' => [
                                'type' => 'keyword'
                            ],
                            'id' => [
                                'type' => 'keyword'
                            ],
                            'type' => [
                                'type' => 'keyword'
                            ],
                            'inserted' => [
                                'type' => 'date',
                                'format' => "yyyy-MM-dd HH:mm:ss"
                            ],
                            'item' => [
                                'type' => 'text',
                                'index' => 'false'
                            ]
        ]]]]];
        return $template;
    }

    /*
     * Template for transformation index
     */

    public function transform_index_template() {
        $template = [

            'body' => [
                'mappings' => [
                    "_default_" => [// for every document type  in index the same
                        'properties' => [
                            'name' => [
                                'type' => 'keyword'
                            ],
                            'id' => [
                                'type' => 'keyword'
                            ],
                            'type' => [
                                'type' => 'keyword'
                            ],
                            'inserted' => [
                                'type' => 'date',
                                'format' => "yyyy-MM-dd HH:mm:ss"
                            ]
                            ,
                            'item' => [
                                'type' => 'text',
                                'index' => 'false'
                            ]
        ]]]]];
        return $template;
    }

    /*
     * Template for transformation index
     */

    public function load_index_template() {
        $template = [
            'body' => [
                'mappings' => [
                    "_default_" => [// for every document type  in index the same
                        'properties' => [
                            'name' => [
                                'type' => 'keyword'
                            ],
                            'id' => [
                                'type' => 'keyword'
                            ],
                            'type' => [
                                'type' => 'keyword'
                            ],
                            'inserted' => [
                                'type' => 'date',
                                'format' => "yyyy-MM-dd HH:mm:ss"
                            ],
//                            'item' => [
//                                'type' => 'text'
//                            ],
//                            'previous' => [
//                                'type' => 'text'
//                            ],
                            'updated' => [
                                'type' => 'date',
                                'format' => "yyyy-MM-dd HH:mm:ss"
                            ]
        ]]]]];
        return $template;
    }

    /*
     * Import old data used by CLI
     */

// obsolete; only for legancy restores
    public function import_old() {
        $index = 'rechtspraak_e';
        $indextype = 'namenlijst';

        $dir = "backups/old-data/";

        die('pas import inserted statement aan!');
        $entry = 'rechtspraak-2017-05-24.json';

        $this->create_extract_index($index);

        echo "loading file $entry\n";
        $file = file_get_contents($dir . $entry);
        $data = json_decode($file, true);

        $inserted = '2017-05-24 12:00:00'; //'2017-07-04 16:29:40'

        foreach ($data as $item) {
            print("\tImporting old name: " . $item['name'] . " doctype:" . $item['set'] . " into $index w. $indextype \n");
            continue;
            $res = $this->put_item(
                    md5($item['name'])
                    , $item['name'], $item['html'], $index, $indextype, $item['set'], $inserted);
        }
    }

}
?>

