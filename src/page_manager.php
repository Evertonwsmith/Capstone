<?php

class page_manager
{

    public $tab, $asc, $col, $page_amount, $page_num;
    public $offset, $mysql_limit;
    public $page_attr, $submit_post;

    public function __construct($default_tab = null, $default_asc = "1", $default_col = null, $default_page_amount = "25", $default_page = "0", $page_attr = array())
    {
        //Set variables
        $this->tab = (isset($_REQUEST["tab"]) ? htmlspecialchars($_REQUEST["tab"]) : $default_tab);
        $this->asc = (isset($_REQUEST["asc"]) ? htmlspecialchars($_REQUEST["asc"]) : $default_asc);
        $this->col = (isset($_REQUEST["col"]) ? htmlspecialchars($_REQUEST["col"]) : $default_col);
        $this->page_num = (isset($_REQUEST["page_num"]) ? htmlspecialchars($_REQUEST["page_num"]) : $default_page);
        $this->page_amount = (isset($_REQUEST["page_amount"]) ? htmlspecialchars($_REQUEST["page_amount"]) : $default_page_amount);

        //Set the extra page attributes
        $this->page_attr = is_array($page_attr) ? $page_attr : array();
        echo "<script>window.extra_post = {";
        foreach ($page_attr as $key => $default_val) {
            $val = isset($_POST[$key]) ? $_POST[$key] : $default_val;
            echo "$key: '$val'";
        }
        echo "};</script>";
        if (count($this->page_attr) == 0) $this->submit_post = 0;
        else $this->submit_post = 1;

        //Set limit
        $this->offset = ($this->page_num * $this->page_amount);
        $this->mysql_limit = $this->offset . ", " . $this->page_amount;
    }

    public function get_additional_attr()
    {
        $attributes = "";
        foreach ($this->page_attr as $attr => $val) {
            $attributes .= "<input type='hidden' name='$attr' value='$val' />";
        }
        return $attributes;
    }

    //get_list_button function returns a button html element that will link to this page with the specified attributes in the request
    public function get_list_button($attr_col)
    {
        $asc = $this->asc;
        $col = $this->col;

        $active = ($attr_col === $col);
        if ($attr_col === "search_relevance") {
            $char_class = $active ? "sort-on-fixed" : "sort-off";
        } else {
            $char_class = "sort-off";
            if ($active) {
                if ($asc == 0) {
                    $char_class = "sort-asc";
                } else {
                    $char_class = "sort-desc";
                }
                $asc = (1 - $asc);
            } else {
                $asc = 1;
            }
        }

        $button = ("
            <div class='$char_class'><button onclick='asc=\"$asc\";col=\"$attr_col\";reload_content(null,window.extra_post);'></button></div>
        ");
        return $button;
    }

    //get_page_amount_selector returns a selector object to change the amount of results per page
    public static function get_page_amount_selector($options = array(10, 25, 50, 100), $default = 25)
    {
        $page_amount = (isset($_REQUEST["page_amount"]) ? htmlspecialchars($_REQUEST["page_amount"]) : $default);
        $selector = "<select id='page_amount_selector' onchange='page_num=0;page_amount=this.value;reload_content(null,window.extra_post);'>";
        foreach ($options as $amount) {
            $selector .= "<option value='$amount' " . ($page_amount == $amount ? "selected='true'" : "") . ">$amount results per page</option>";
        }
        $selector .= "</select>";
        return $selector;
    }

    //get_page_buttons returns a button html that will go up or down a page of the current table
    public function get_page_buttons($total_results)
    {
        $total = $total_results;
        $limit = $this->page_amount;
        $page_num = $this->page_num;

        $max_page = floor(($total - 1) / $limit); //max page number
        $left_most_page = 0; //default left page
        $right_most_page = $max_page; //default right page
        if ($max_page >= 6) {
            //Only show the 7 pages around current
            $left_most_page = $page_num - 3;
            $right_most_page = $page_num + 3;
            if ($left_most_page < 0) {
                $diff =  0 - $left_most_page;
                $left_most_page += $diff;
                $right_most_page += $diff;
            }
            if ($right_most_page > $max_page) {
                $diff = $max_page - $right_most_page;
                $left_most_page += $diff;
                $right_most_page += $diff;
            }
        }
        $page_num = ($page_num < 0 ? 0 : $page_num);
        $first = $page_num * $limit;
        if ($first >= $total) $first = 0;

        //Create pageination
        $page_buttons = ("<h5>$total result(s)</h5><nav>
            <ul class='pagination'>
            <li class='page-item " . ($page_num == 0 ? "disabled" : "") . "'>
                <button class='page-link' " . ($page_num == 0 ? "tabindex='-1' aria-disabled='true'" : "") . " onclick='page_num--; reload_content(null,window.extra_post);' >Previous</button>
            </li>");

        for ($page = $left_most_page; $page <= $right_most_page; $page++) {
            $page_buttons .= "<li class='d-none d-md-block page-item'><button class='page-link " . ($page_num == $page ? "current-page" : "") . "' onclick='page_num=$page; reload_content(null,window.extra_post);' >" . ($page + 1) . "</button></li>";
        }

        $page_buttons .= ("<li class='page-item " . ($page_num == $max_page ? "disabled" : "") . "'>
                <button class='page-link' " . ($page_num == $max_page ? "tabindex='-1' aria-disabled='true' " : "") . "onclick='page_num++; reload_content(null,window.extra_post);'>Next</button>
            </li>
            </ul>
            </nav>");

        //return pageination
        return $page_buttons;
    }

    public function get_tab_link($name, $tab)
    {
        $form_id = $tab . "_link_form";
        $form = ("<form action='" . $_SERVER['PHP_SELF'] . "' method='" . ($this->submit_post ? "post" : "get") . "' id='$form_id'>"
            . "<a href='#' onclick='(document.getElementById(\"$form_id\")).submit();'><h2>$name</h2></a>"
            . "<input type='hidden' name='tab' value='$tab' />"
            . "<input type='hidden' name='page_amount' value='" . $this->page_amount . "' />"
            . $this->get_additional_attr());
        $form .= "</form>";
        return $form;
    }
}
