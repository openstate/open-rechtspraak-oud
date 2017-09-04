<?php

class Es_model extends CI_Model {

    private $client = null;

    public function __construct() {
        parent::__construct();

        $hosts = ['docker_c-dev-es_1'];
        $this->client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
    }

    public function get_most_recent_value($index, $indextype, $field) {
        $params = [
            'index' => $index,
            'type' => $indextype,
            'body' => [
                'sort' => [
                    $field => [ 'order' => 'desc']
                ]
            ]
        ];
        $response = $this->client->search($params);
        $mrv = $response['hits']['hits'][0]["_source"]["$field"];
        return $mrv;
    }

    public function update($index, $indextype, $esid, $fields) {
        $params = array();
        $params = [
            'index' => $index,
            'type' => $indextype,
            'id' => $esid, // id of old document
            'body' => [
                'doc' => $fields
            ]
        ];

        $response = $this->client->update($params);
        // print("\tUPDATE index $index indextype $indextype esid $esid \n");
    }

    // used by backup
    public function get_all($index, &$scroll_id) {
        $response;

        if (!isset($scroll_id)) {
            $params = ['index' => $index, 'size' => 100, 'scroll' => '5m'];
            $response = $this->client->search($params);
        } else {
            $params = [ 'scroll' => '5m', 'scroll_id' => $scroll_id];
            $response = $this->client->scroll($params);
        }

        if ($scroll_id != $response["_scroll_id"]) {
            
        }
        $scroll_id = $response["_scroll_id"];
        return $response['hits']['hits'];
    }

// used by ?    
    public function get_all_it($index, $indextype, &$scroll_id) {
        die("OBSOLETE FUNTCTION GET_ALL_IT");
        
        $response;

        if (!isset($scroll_id)) {
            $params = ['index' => $index,
                'type' => $type,
                'size' => 100, 'scroll' => '5m'];
            $response = $this->client->search($params);
//            print("retrieved  " . count($response['hits']['hits']) . " of total of " .
//                    $response['hits']['total'] . "\n");
        } else {
            $params = [ 'scroll' => '5m', 'scroll_id' => $scroll_id];
            $response = $this->client->scroll($params);
            //        print("retrieved another " . count($response['hits']['hits']) . "\n");
        }

        if ($scroll_id != $response["_scroll_id"]) {
            //     print("\t\tNEW SCROLL ID\n");
        }
        $scroll_id = $response["_scroll_id"];
        return $response['hits']['hits'];
    }

    // used by Namenlijst load to get most recent records    
    public function get_all_it_sortable_from($index, $indextype, &$scroll_id, $sortfield, $from) {
        $response;

        if (!isset($scroll_id)) {

            if ($from == NULL) {
                $params = ['index' => $index,
                    'type' => $indextype,
                    'sort' => $sortfield,
                    'size' => 100,
                    'scroll' => '5m'
                ];
            } else {
                $params = ['index' => $index,
                    'type' => $indextype,
                    'sort' => $sortfield,
                    'size' => 100,
                    'scroll' => '5m',
                    'body' => [
                        'query' => [ "range" => [
                                $sortfield => [
                                    'gt' => $from 
                                ]
                            ]
                        ]
                    ]
                ];
            }


            $response = $this->client->search($params);

//            print("retrieved data from $from that " . count($response['hits']['hits']) . " of total of " .
//                    $response['hits']['total'] . "\n");
        } else {
            $params = [ 'scroll' => '5m', 'scroll_id' => $scroll_id];
            $response = $this->client->scroll($params);
            //    print("retrieved another " . count($response['hits']['hits']) . "\n");
        }

        if ($scroll_id != $response["_scroll_id"]) {
            //    print("\t\tNEW SCROLL ID\n");
        }
        $scroll_id = $response["_scroll_id"];
        return $response['hits']['hits'];
    }

    // used by Namenlijst load gets all based on index type
    // Sortable on field
    public function get_all_it_sortable($index, $indextype, &$scroll_id, $sortfield) {
        $response;

        if (!isset($scroll_id)) {
            $params = ['index' => $index,
                'type' => $indextype,
                'sort' => $sortfield,
                'size' => 100, 'scroll' => '5m'];
            $response = $this->client->search($params);
            //   print("retrieved  " . count($response['hits']['hits']) . " of total of " .
            //             $response['hits']['total'] . "\n");
        } else {
            $params = [ 'scroll' => '5m', 'scroll_id' => $scroll_id];
            $response = $this->client->scroll($params);
            //    print("retrieved another " . count($response['hits']['hits']) . "\n");
        }

        if ($scroll_id != $response["_scroll_id"]) {
            //    print("\t\tNEW SCROLL ID\n");
        }
        $scroll_id = $response["_scroll_id"];
        return $response['hits']['hits'];
    }

    // used by list all persons
    public function get_all_unique_countfield($index, $indextype, $field, $filter) {
        $params = [
            'index' => $index,
            'type' => $indextype,
            'size' => 0,
            'body' => [
                "aggs" => [
                    "count_of_$countfield" . "_per_$field" => [
                        "terms" => [
                            "field" => $field,
                            "size" => 999999,
                            "order" =>
                            [ "_term" => "asc"]
        ]]]]];

        if (isset($filter)) {
            $params['body']['query'] = $filter;
        }

        $response = $this->client->search($params);
        return $response["aggregations"]["count_of_$countfield" . "_per_$field"]["buckets"];
    }

    // enricher
    public function get_all_ti_exclude($index, $indextype, &$scroll_id, $excludes) {
        $response;

        if (!isset($scroll_id)) {
            $params = ['index' => $index,
                'type' => $indextype,
                '_source_exclude' => $excludes,
                'size' => 100, 'scroll' => '5m'];
            $response = $this->client->search($params);
            //   print("retrieved  " . count($response['hits']['hits']) . " of total of " .
            //             $response['hits']['total'] . "\n");
        } else {
            $params = [ 'scroll' => '5m', 'scroll_id' => $scroll_id];
            $response = $this->client->scroll($params);
            //    print("retrieved another " . count($response['hits']['hits']) . "\n");
        }

        if ($scroll_id != $response["_scroll_id"]) {
            //  print("\t\tNEW SCROLL ID\n");
        }
        $scroll_id = $response["_scroll_id"];
        return $response['hits']['hits'];
        
        
    }

    // used by create_extracted)index (or...)
    public function create_index($index, $params) {
        $nparams = $params;
        $nparams['index'] = $index;
        $response = $this->client->indices()->create($nparams);
        return $response;
    }

    // used by restore (or transform, load) 
    public function put($index, $indextype, $fields) {
        $params = array();
        $params['index'] = $index; //schema
        $params['type'] = $indextype; // tabel such as hogeraad, groningen whatever
        $params['body'] = $fields;

        // var_dump($params);

        $res = $this->client->index($params);
        //     print("\tINSERT index $index with type $type extracted \n");
        return $res;
    }

    // asumes fields to be comparable
    public function exists($index, $indextype, $fields) {
        $params = array();
        $params['index'] = $index;
        $params['type'] = $indextype;

        foreach ($fields as $key => $value) {
            $term_arr[] = ['term' => [$key => $value]];
        }

        $params['body'] = ['query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'must' => $term_arr
        ]]]]];
        //try {
            $response = $this->client->search($params);
            if ($response["hits"]["total"] == 0) {
                return false;
            } elseif ($response["hits"]["total"] > 1) {
                var_dump($response);
                error_log("Corrupt database duplicate: index $index w. type $indextype has more than 1 hit.");
                die("Corrupt database duplicate: index $index w. type $indextype has more than 1 hit.");
            } else { // =1
                return $response["hits"]["hits"][0];
            }
        //} 
//        catch (Exception $e) {
//            print("error\n");
//            var_dump($e->getMessage());
//            die('end of error');
//            if (json_decode($e->getMessage())->error->root_cause[0]
//                    ->type != "index_not_found_exception") {
//                print_r(json_decode($e->getMessage())->error);
//                die("\n\nrethrowing shit\n\n");
//                throw ($e);
//            } else {
//// else continue w. false
//                return false;
//            }
  //      }
    }

    // used by person in viewer
    public function get($index, $indextype, $fields, $sortby) {
        $params = array();

        $params['index'] = $index; //schema
        $params['type'] = $indextype; // tabel such as hogeraad, groningen whatever

        foreach ($fields as $key => $value) {
            $term_arr[] = ['term' => [$key => $value]];
        }

        $params['body'] = ['query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'must' => $term_arr
        ]]]]];

        if (isset($sortby)) {
            $params['body']['sort'] = [ $sortby['field'] => [ 'order' => $sortby['order']]];
        }
        $response = $this->client->search($params);
        //    print " " . $response["hits"]["total"] . "\n</br>";
        return $response['hits']['hits'];
    }

    // returns the formal getMapping of ES instance
    public function get_mapping() {
        return $this->client->indices()->getMapping();
    }

    public function get_indices() {
        $mapping = $this->get_mapping(); //KV 
        unset($mapping[$index]['mappings']['_default_']);
        //   var_dump($mapping[$index]['mappings']);die();
        $indices = array_keys($mapping);
        return $indices;
    }

    public function get_types($index) {
        $mapping = $this->get_mapping(); //KV 

        if (!isset($index)) {
            foreach ($mapping as $key => $values) {
                foreach ($values['mappings'] as $subkey => $value) {
                    if ($subkey == "_default_")
                        continue;
                    $types[$key][] = $subkey;
                }
            }
        } else {
            unset($mapping[$index]['mappings']['_default_']);
            //   var_dump($mapping[$index]['mappings']);die();
            $types = array_keys($mapping[$index]['mappings']);
        }
        return $types;
    }

    public function get_status() {
        $data['info'] = $this->client->info();
        $data['indices'] = $this->get_indices();
        $data['types'] = $this->get_types();
        $data['health'] = $this->client->cluster()->health();
        $data['cstats'] = $this->client->cluster()->stats();
        $data['nstats'] = $this->client->nodes()->stats();

        foreach ($this->get_mapping() as $index) {
            foreach ($types as $type) {
                $data['mapping']['index'][] = $type;
            }
        }
        return $data;
    }

}

/* End of file es_model.php */
/* Location: ./application/models/es_model.php */
?>