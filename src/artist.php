<?php
require_once 'user.php';

class artist extends user
{

    private $artist_name, $artist_image_id, $description;

    public function __construct($artist_name)
    {
        //Query the artist
        $data = my_sql::select("*", "artistaccount", "artistName=:0", array("$artist_name"));
        if ($data != false && count($data) > 0) {
            $data = $data[0];

            //Call the parent constructor
            parent::__construct($data['userEmail']);

            //Set the variables
            $this->artist_name = $data['artistName'];
            $this->artist_image_id = $data['artistImageID'];
            $this->description = $data['description'];
        } else {
            return null;
        }
    }

    /**
     * Define the Getters
     */

    public function get_artist_name()
    {
        return $this->artist_name;
    }

    public function get_artist_image_id()
    {
        return $this->artist_image_id;
    }

    public function get_description()
    {
        return $this->description;
    }

    public function get_artist_image_path()
    {
        //TODO: return image path
        return "blank-profile-picture-973460_960_720.png";
    }

    public function get_event_post_ids()
    {
        $events = my_sql::select("eventID", "eventpost", "userEmail=:0", array($this->get_user_email()));
        if ($events != false && count($events > 0)) {
            foreach ($events as $index => $event) {
                $events[$index] = $event['eventID'];
            }
            return $events;
        } else {
            return null;
        }
    }

    public static function get_all_names($col, $asc = "1", $limit = null, $where = null, $where_attr = null)
    {
        $sort_attr = artist::get_attr_name($col);
        $id = artist::get_attr_name("artist_name");

        $sel = $id . (isset($sort_attr) ? "," . $sort_attr : "");
        $frm = "artistaccount";
        $ord = artist::get_attr_order_by($col, $asc);

        // query the database using the my_sql api
        $results = my_sql::select($sel, $frm, $where, $where_attr, $ord, null, null, $limit);

        $names = array();
        foreach ($results as $row) {
            $name = my_sql::select("artistName", "artistaccount LEFT JOIN useraccount ON artistaccount.userEmail=useraccount.userEmail", "artistaccount.artistName=:0 AND useraccount.isActive='1'", array($row[$id]));
            if (isset($name[0])) array_push($names, $name[0][$id]);
        }
        return $names;
    }

    public static function get_table_header($col)
    {
        $parent_header = user::get_table_header($col);
        if (isset($parent_header)) return $parent_header;
        switch ($col) {
            case "artist_name":
                return "Artist Name";
            default:
                return null;
        }
    }

    public static function get_attr_name($col)
    {
        switch ($col) {
            case "artist_name":
                return "artistName";
        }

        //else return user attr_name
        return user::get_attr_name($col);
    }

    public static function get_attr_order_by($col, $asc)
    {
        switch ($col) {
            case "artist_name":
                return "artistName" . ($asc ? " ASC" : " DESC");;
        }

        //else return user attr_order_by
        return user::get_attr_order_by($col, $asc);
    }
}
