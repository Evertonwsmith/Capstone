<?php
require_once 'my_sql.php';

class eventpost
{

    private $event_email, $event_id, $media_group_id, $title, $timestamp, $text, $is_public;
    private static $eventpost_detail_link = "eventpost_detail.php";

    public function __construct($event_id)
    {
        if ($event_id === "new") {
            $this->event_id = null;
            $this->media_group_id = null;
            $this->title = "New eventpost";
            $this->timestamp = null;
            $this->text = "";
            $this->is_public = "0";
            $event_id = $this->insert_into_database();
        }

        $data = my_sql::select("*", "eventpost", "eventID=:0", array("$event_id"));
        if (count($data) == 1) {
            $data = $data[0];
            $this->event_id = $data['eventID'];
            $this->media_group_id = $data['mediaGroupID'];
            $this->title = $data['title'];
            $this->timestamp = $data['timestamp'];
            $this->text = $data['text'];
            $this->is_public = $data['isPublic'];
        } else {
            echo "eventpost, $event_id, does not exist!";
            return null;
        }
    }

    /****************
     * Define getters
     * **************
     */

    public function get_event_id()
    {
        return $this->event_id;
    }

    public function get_media_group_id()
    {
        return $this->media_group_id;
    }

    public function get_title()
    {
        return $this->title;
    }

    public function get_timestamp()
    {
        return $this->timestamp;
    }

    public function get_text()
    {
        return $this->text;
    }

    public function get_media()
    {
        return $this->media_group_id;
    }

    public function get_eventpost_detail_link()
    {
        return "<a href='" . eventpost::$eventpost_detail_link . "?event_id=" . $this->get_event_id() . "'>View / Edit eventpost</a>";
    }

    public function is_public()
    {
        return isset($this->is_public) && $this->is_public ? true : false;
    }

    public function get_media_edit_input()
    {
        //TODO: return all pictures / audio associated with the media_group_id with add / delete options
    }

    public function get_title_edit_input()
    {
        $attr_name = "eventpost_title";
        $input = ("<input type='text' name='$attr_name' value='" . $this->get_title() . "'/>");
        return $input;
    }

    public function get_text_edit_input($rows = 8, $cols = 80)
    {
        $attr_name = "eventpost_text";
        $input = ("<textarea rows='$rows' cols='$cols' maxlength='4000' name='$attr_name' form='eventpost_form_" . $this->get_event_id() . "'>"
            . $this->get_text()
            . "</textarea>");
        return $input;
    }

    public function get_is_public_edit_form()
    {
        $attr_name = "eventpost_is_public";
        $message = ($this->is_public()
            ? "Are you sure? If you are planning to publish this same eventpost again, it will be assigned a new timestamp and followers will be alerted that there is a new post. If you are only making small changes, consider just saving any edits."
            : "Are you sure? Publishing the eventpost will assign the current time as the new timestamp and followers will be alerted that there is a new post.");
        $form = ("<form method='post'>"
            . "<input type='button' onclick='if(confirm(\"" . $message . "\")){this.form.submit();}' value='" . ($this->is_public() ? "Unpublish" : "Publish") . "' />"
            . "<input type='hidden' name='$attr_name' value='" . ($this->is_public() ? "0" : "1") . "' />"
            . "<input type='hidden' name='event_id' value='" . $this->get_event_id() . "' />"
            . "</form>");
        return $form;
    }

    public function get_deletion_form()
    {
        $attr_name = "delete_eventpost";
        $form = ("<form method='post'>"
            . "<input type='button' onclick='if(confirm(\"Are you sure? Deleting a eventpost removes all its data from the database. This action is irreversible.\")){this.form.submit();}' value='Delete' />"
            . "<input type='hidden' name='$attr_name' value='1' />"
            . "<input type='hidden' name='event_id' value='" . $this->get_event_id() . "' />"
            . "</form>");
        return $form;
    }

    public static function get_new_eventpost_button()
    {
        $form = ("<form action='" . eventpost::$eventpost_detail_link . "' method='post'>"
            . "<input type='submit' name='sumbit' value='Add eventpost' />"
            . "<input type='hidden' name='event_id' value='new' />"
            . "</form>");
        return $form;
    }

    public static function get_all($order_by_attr, $asc = "1", $limit = null, $where = null, $where_attr = null)
    {
        $sort_attr = eventpost::get_attr_name($order_by_attr);
        $id = eventpost::get_attr_name("event_id");

        $sel = $id . (isset($sort_attr) ? ",$sort_attr" : "");
        $frm = "eventpost";
        $ord = eventpost::get_attr_order_by($order_by_attr, $asc);

        // query the database using the my_sql api
        $result = my_sql::select($sel, $frm, $where, $where_attr, $ord, null, null, $limit);

        // create eventposts for each row
        $events = array();
        foreach ($result as $row) {
            $eventpost = new eventpost($row[$id]); // create eventpost object for each row
            array_push($events, $eventpost);
        }

        return $events;
    }

    public function get_table_entry($columns)
    {
        $row = "<tr>";
        foreach ($columns as $col) {
            $row .= "<td>" . $this->get_table_data($col) . "</td>";
        }
        $row .= "</tr>";
        return $row;
    }

    public function get_vertical_table($columns, $editable = false)
    {
        $table = ($editable ? "<form method='post' id='eventpost_form_" . $this->get_event_id() . "'>" : "") . "<table>";
        foreach ($columns as $col) {
            $table .= ("<tr>"
                . "<th>" . $this->get_table_header($col) . "</th>"
                . "<td>" . $this->get_table_data($col, $editable) . "</td>"
                . "</tr>");
        }
        $table .= "</table>" . ($editable ? ("<input type='hidden' name='event_id' value='" . $this->get_event_id() . "' />"
            . "<br><input type='submit' name='eventpost_form_submit' value='Save Changes' />"
            . "</form>") : "");
        return $table;
    }

    public static function get_table_header($column)
    {
        switch ($column) {
            case "event_id":
                return "event ID";
                break;
            case "media_group_id":
                return "Media Group ID";
                break;
            case "media":
                return "Attached Media";
                break;
            case "title":
                return "Title";
                break;
            case "timestamp":
                return "Date and Time";
                break;
            case "text":
                return "Text";
                break;
            case "eventpost_detail":
                return "eventpost Details";
                break;
            case "is_public":
                return "eventpost Visibility";
                break;
            default:
                return null;
        }
    }

    public function get_table_data($column, $editable = false)
    {
        switch ($column) {
            case "event_id":
                return $this->get_event_id();
                break;
            case "media_group_id":
                return $this->get_media_group_id();
                break;
            case "media":
                return $editable ? $this->get_media_edit_input() : $this->get_media();
            case "title":
                return $editable ? $this->get_title_edit_input() : $this->get_title();
                break;
            case "timestamp":
                return $this->get_timestamp();
                break;
            case "text":
                return $editable ? $this->Get_text_edit_input() : $this->get_text();
                break;
            case "eventpost_detail":
                return $this->get_eventpost_detail_link();
                break;
            case "is_public":
                return ($this->is_public() ? "<b style='color:green'>Published</b>" : "<b style='color:red'>Unpublished</b>");
                break;
            default:
                return null;
        }
    }

    public static function get_attr_order_by($column, $asc)
    {
        switch ($column) {
            case "event_id":
                return "eventID" . ($asc ? " ASC" : " DESC");
                break;
            case "media_group_id":
                return "mediaGroupID" . ($asc ? " ASC" : " DESC");
                break;
            case "title":
                return "title" . ($asc ? " ASC" : " DESC");
                break;
            case "text":
                return "text" . ($asc ? " ASC" : " DESC");
                break;
            case "is_public":
                return "isPublic" . ($asc ? " ASC" : " DESC");
                break;
            case "eventpost_detail":
            case "timestamp":
            default:
                return "timestamp" . ($asc ? " ASC" : " DESC");
        }
    }

    public static function get_attr_name($attr)
    {
        switch ($attr) {
            case "event_id":
                return "eventID";
                break;
            case "media_group_id":
                return "mediaGroupID";
                break;
            case "title":
                return "title";
                break;
            case "timestamp":
                return "timestamp";
                break;
            case "text":
                return "text";
                break;
            case "is_public":
                return "isPublic";
                break;
            default:
                return null;
        }
    }


    /********************
     * Define the setters
     * ******************
     */

    public function set_title($val)
    {
        $result = my_sql::update("eventpost", array("title"), array($val), "eventID=:0", array($this->get_event_id()));
        if (isset($result) && $result != false) $this->title = $val;
        return $result;
    }

    public function set_text($val)
    {
        $result = my_sql::update("eventpost", array("text"), array($val), "eventID=:0", array($this->get_event_id()));
        if (isset($result) && $result != false) $this->text = $val;
        return $result;
    }

    public function set_public($is_public = 1)
    {
        $result = my_sql::update("eventpost", array("isPublic"), array($is_public), "eventID=:0", array($this->get_event_id()));
        $this->set_timestamp();
        if (isset($result) && $result != false) $this->is_public = $is_public;
        return $result;
    }

    public function set_timestamp($reset = false)
    {
        $date = (new DateTime())->setTimezone(new DateTimeZone('America/Vancouver'));
        $datetime = $reset ? null : $date->format('Y-m-d H:i:s');
        $this->timestamp = $datetime;
        return my_sql::update("eventpost", array("timestamp"), array($datetime), "eventID=:0", array($this->get_event_id()));
    }

    public function set_media_group_id($id)
    {
        $this->media_group_id = $id;
    }


    /*****************************
     * Define additional functions
     * ***************************
     */

    public function insert_into_database()
    {
        $result = my_sql::insert_get_last_id(
            "eventpost",
            array("eventID", "mediaGroupID", "title", "text", "timestamp", "isPublic"),
            array($this->event_id, $this->media_group_id, $this->title, $this->text, $this->timestamp, $this->is_public)
        );
        return $result;
    }

    public function check_attr_update()
    {
        foreach ($_POST as $key => $val) {
            switch ($key) {
                case "eventpost_title":
                    $this->set_title($val);
                    break;
                case "eventpost_text":
                    $this->set_text($val);
                    break;
                case "eventpost_is_public":
                    $this->set_public($val);
                    break;
                case "eventpost_form_submit":
                    echo "<script type='text/javascript'>window.onload = function(){alert('All changes to \"" . $this->get_title() . "\" have been saved.')};</script>";
                    break;
                case "delete_eventpost":
                    $this->delete_from_database();
                    break;
            }
        }
    }

    public function delete_from_database()
    {
        $result = my_sql::delete("eventpost", "eventID=:0", array($this->get_event_id()));
        if ($result != false) {
            //echo "<script type='text/javascript'>window.onload=function(){alert(\"eventpost successfully deleted.\"); window.location.assign(\"profile_page.php\");};</script>";
        }
        return $result;
    }
}
