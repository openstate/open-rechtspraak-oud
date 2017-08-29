<?php

class Hash_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /*
     * Returns true or false called by compare_item_to_list
     */

    private function items_equal($src, $dst) {
        foreach ($src as $key => $value) {
            if ($dst[$key] != $value) {
                return FALSE;
            }
        }
        return TRUE;
    }

    private function compare_assoc($src, $dst) {
        if ($this->isAssoc($dst)) {// use keys to index
            foreach ($src as $subkey => $subvalue) {
                $res = $this->compare_hash($subvalue, $dst[$subkey]);
                if ($res != null) {
                    $res_arr[$subkey] = $res;
                }
            }
        } else {
            die(" DST is list ");
        }
        return $res_arr;
    }

    /*
     * called by compare_list
     */

    private function item_in_list($src, $dst) {
        for ($j = 0; $j < count($dst); $j++) {
            if ($this->items_equal($src, $dst[$j])) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /*
     * returns difference between lists called by compare_hash
     */

    private function compare_list($src, $dst) {
        for ($i = 0; $i < count($src); $i++) {
            if (!$this->item_in_list($src[$i], $dst)) {
                $res_arr[] = $src[$i];
            }
        }
        return $res_arr;
    }

    /*
     * Compares two hashes. In each value in src not in dst; 
     * todo dst should not contain anything in src
     * returns the diff
     */

    public function compare_hash($src, $dst) {
        if (is_array($src)) {
            $res_arr;
            if ($this->isAssoc($src)) {
                return $this->compare_assoc($src, $dst);
            } else {// no Assoc = array list                 
                return $this->compare_list($src, $dst);
            }
        } else {// key value pair (or value!!!!)
            die('kv not ok');
        }
        die("should not get here");
    }

    private function isAssoc($array) {
        return ($array !== array_values($array));
    }

}

?>