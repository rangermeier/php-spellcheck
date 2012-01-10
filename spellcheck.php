<?php

/** usage:
$sc = new spellcheck;
echo $sc->correct("failur");
*/

class spellcheck {

    function deletion($word){
        for($x=0;$x<strlen($word);$x++){
            $results[] = substr($word, 0, $x) . substr($word, $x+1, strlen($word));
        }
        return $results;
    }

    function transposition($word){
        for($x=0;$x<strlen($word)-1;$x++){
            $results[] = substr($word, 0, $x) . $word[$x+1] . $word[$x] . substr($word, $x+2, strlen($word));
        }
        return $results;
    }

    function alteration($word){
        foreach(range('a','z') as $letter){
            for($x=0;$x<strlen($word);$x++){
                $results[] = substr($word, 0, $x) . $letter . substr($word, $x+1, strlen($word));
            }
        }
        return $results;
    }

    function insertion($word){
        foreach(range('a','z') as $letter){
            for($x=0;$x<strlen($word)+1;$x++){
                $results[] = substr($word, 0, $x) . $letter . substr($word, $x, strlen($word));
            }
        }
        return $results;
    }

    function train($features){
        $model = array();
        foreach($features as $feature){
            if(array_key_exists($feature, $model)) {
                $model[$feature] += 1;
            } else {
                $model[$feature] = 1;
            }
        }
        return $model;
    }

    function words($text){
        $matches = preg_match_all("/[a-z]+/", strtolower($text), $output);
echo count($output[0])."words \n";
        return $output[0];
    }

    function read($file){

        $contents = "";
        $fp = fopen($file, "r");
        while(!feof($fp)){
            $contents .= fread($fp, 8192);
        }
        fclose($fp);
        return $contents;
    }

    private $data = "";
    function get_data() {
        if($this->data == ""){
            $this->data = $this->read("big.txt");
        }
        return $this->data;
    }

    private $nwords = array();
    function get_nwords() {
        if(count($this->nwords) == 0){
            $this->nwords = $this->train($this->words($this->get_data()));
        }
        return $this->nwords;
    }
    

    function edits1($word){
        return array_merge($this->deletion($word), $this->transposition($word), $this->alteration($word), $this->insertion($word));
    }

    function known_edits2($words){
        $edits2 = array();
        foreach($words as $word) {
            $edits2 = array_merge($edits2, $this->edits1($word));
        }
        return array_unique($this->known($edits2));
    }      

    function known($words){
        $known_words = array();
        $nwords = $this->get_nwords();
        foreach($words as $word){
            if(array_key_exists($word, $nwords)){
                $known_words[$word] = $nwords[$word];
            }
        }
        return $known_words;
    }

    function correct($word){
        if(count($this->known(array($word))) == 1){
            return $word;
        } else {
            $known = $this->known($this->edits1($word));
            if(count($known) > 0){
                arsort($known);
                return array_shift(array_keys($known));
            } else {
                $known2 = $this->known_edits2($this->edits1($word));
                if(count($known2) > 0){
                    arsort($known2);
                    return array_shift(array_keys($known2));
                }
            }   
        }
        return $word;
    }
}
?>
