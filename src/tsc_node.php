<?php

class tsc_node
{

    public $val, $ref, $prev, $child, $next;

    private static $max_next_refs = 3; // maximum words that can be passed for "next references"

    public function __construct($prev, $char = null)
    {
        if (isset($char)) {
            $this->val = $char;
            $this->prev = $prev;
            $this->child = [];
            $this->ref = [];
            $this->next = [];
        }
    }

    public function is_root()
    {
        return isset($this->prev) ? false : true;
    }

    public function pass($str, $ref, $next = "")
    {
        if (!is_array($this->ref)) $this->ref = [];
        if (strlen($str) == 0) {
            if (!in_array($ref, $this->ref)) {
                array_push($this->ref, $ref);
                if (strlen($next) > 0) {
                    //Add next suggestion to array
                    if (!is_array($this->next)) $this->next = [];
                    if (array_key_exists($next, $this->next)) {
                        //increment existing key
                        ($this->next)[$next] = ($this->next)[$next] + 1;
                    } else {
                        //initialize key
                        ($this->next)[$next] = 1;
                    }
                }
            }
            return true;
        }
        $char = substr($str, 0, 1);
        $str = substr($str, 1);
        if (!is_array($this->child)) $this->child = [];
        foreach ($this->child as $child) {
            if ($char == $child->val) {
                if ($child->pass($str, $ref, $next)) return true;
            }
        }
        $new_child = new tsc_node($this, $char);
        array_push($this->child, $new_child);
        return $new_child->pass($str, $ref, $next);
    }

    public function compress()
    {
        if (is_array($this->child)) {
            foreach ($this->child as $key => $child) {
                if (is_array($child->child) && is_array($child->ref)) {
                    while (count($child->child) == 1 && count($child->ref) == 0) {
                        $sub_child = ($child->child)[0];
                        $sub_child->val = $child->val . $sub_child->val;
                        $sub_child->prev = $this;
                        ($this->child)[$key] = $sub_child;
                        $child = $sub_child;
                    }
                    $child->compress();
                }
            }
        }
    }

    public function to_string($depth = null)
    {
        $str = "\n";
        if (isset($depth)) {
            for ($i = 0; $i < $depth; $i++) {
                $str .= "\t"; //Add tabs
            }
            $depth++;
        }
        $str .= "<";
        if (!$this->is_root()) {
            $str .= $this->val . "='";
            if (is_array($this->ref)) {
                foreach ($this->ref as $ref) {
                    $str .= $ref . ","; // add each reference
                }
                $str = rtrim($str, ","); // trim the last comma
            }
            $str .= ":";
            //Add next word references
            if (is_array($this->next)) {
                //Sort next refs by frequency
                arsort($this->next);
                $sorted_keys = array_keys($this->next);
                for ($i = 0; $i < count($sorted_keys) && $i < tsc_node::$max_next_refs; $i++) {
                    $next = $sorted_keys[$i];
                    $str .= $next . ","; // add each next word reference
                }
                $str = rtrim($str, ","); // trim the last comma
            }
            $str .= "'";
        }
        foreach ($this->child as $child) {
            $str .= $child->to_string($depth); //add each child node
        }
        $str .= ">";
        return $str;
    }
}
