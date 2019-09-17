<?php
require_once 'user.php';

class venue extends user
{

    private $venue_name, $venue_image_id, $description, $address_id;

    public function __construct($venue_name)
    {
        //Query the venue
        $data = my_sql::select("*", "venueaccount", "venueName=:0", array("$venue_name"));
        if ($data != false && count($data) > 0) {
            $data = $data[0];

            //Call the parent constructor
            parent::__construct($data['userEmail']);

            //Set the variables
            $this->venue_name = $data['venueName'];
            $this->venue_image_id = $data['venueImageID'];
            $this->description = $data['description'];
            $this->address_id = $data['addressID'];
        } else {
            return null;
        }
    }

    /**
     * Define the Getters
     */

    public function get_venue_name()
    {
        return $this->venue_name;
    }

    public function get_venue_image_id()
    {
        return $this->venue_image_id;
    }

    public function get_description()
    {
        return $this->description;
    }

    public function get_address_id()
    {
        return $this->address_id;
    }

    public function get_venue_image_path()
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

    public function get_address()
    {
        return address::address_to_string($this->address_id);
    }

    public static function get_all_names($col, $asc = "1", $limit = null, $where = null, $where_attr = null)
    {
        $sort_attr = venue::get_attr_name($col);
        $id = venue::get_attr_name("venue_name");

        $sel = $id . (isset($sort_attr) ? "," . $sort_attr : "");
        $frm = "venueaccount";
        $ord = venue::get_attr_order_by($col, $asc);

        // query the database using the my_sql api
        $results = my_sql::select($sel, $frm, $where, $where_attr, $ord, null, null, $limit);

        $names = array();
        foreach ($results as $row) {
            $name = my_sql::select("venueName", "venueaccount LEFT JOIN useraccount ON venueaccount.userEmail=useraccount.userEmail", "venueaccount.venueName=:0 AND useraccount.isActive='1'", array($row[$id]));
            if (isset($name[0])) array_push($names, $name[0][$id]);
        }
        return $names;
    }

    public static function get_table_header($col)
    {
        $parent_header = user::get_table_header($col);
        if (isset($parent_header)) return $parent_header;
        switch ($col) {
            case "venue_name":
                return "Venue Name";
            default:
                return null;
        }
    }

    public static function get_attr_name($col)
    {
        switch ($col) {
            case "venue_name":
                return "venueName";
        }

        //else return user attr_name
        return user::get_attr_name($col);
    }

    public static function get_attr_order_by($col, $asc)
    {
        switch ($col) {
            case "venue_name":
                return "venueName" . ($asc ? " ASC" : " DESC");;
        }

        //else return user attr_order_by
        return user::get_attr_order_by($col, $asc);
    }
}
