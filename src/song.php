<?php
require_once 'my_sql.php';

class song
{

    private $artist_name, $mediaGroupID, $song_number, $title;
    public $success;

    public function __construct($artist_name, $mediaGroupID, $song_number, $title)
    {

        $this->artist_name = $artist_name;
        $this->mediaGroupID = $mediaGroupID;
        $this->song_number = $song_number;
        $this->title = $title;

        $songExists = my_sql::select("*", "artistsong", "artistName = :0 AND title = :1", array("$artist_name", "$title"));
        if ($songExists) {
            $song = $songExists[0];
            $this->artist_name = $artist_name;
            $this->mediaGroupID = $song['mediaGroupID'];
            $this->song_number = $song['songNumber'];
            $this->title = $title;
            $this->success = false;
            //Song or song chunk already exists, dont add it
        } else {
            //Check if artist has initial mediaGroupID setup
            $data = my_sql::select("*", "artistsong", "artistName = :0", array("$artist_name"));
            if ($data) {
                if (count($data) >= 1) {
                    $data = $data[0];
                    //add song to artists first occuring mediagroupID
                    $this->mediaGroupID = $data['mediaGroupID'];
                    $mgID = $data['mediaGroupID'];
                    $data2 = my_sql::select("MAX(songNumber) AS songNumber", "artistsong", "mediaGroupID = :0", array("$mgID"));
                    if ($data2) {
                        if (count($data2) >= 1) {
                            $data2 = $data2[0];
                            $this->song_number = ($data2['songNumber'] + 1);
                        }
                    }
                    $this->success = $this->insert_into_database();
                }
            } else {
                //Add a new mediaGroupID and set new song to it
                $newMGID = my_sql::insert_get_last_id("mediagroup", array("mediaGroupID"), array(null));
                if ($newMGID) {
                    $this->mediaGroupID = $newMGID;
                    $this->song_number = 1;

                    $this->success = $this->insert_into_database();
                }
            }
        }
    }

    /****************
     * Define getters
     * **************
     */

    public function get_artist_name()
    {
        return $this->artist_name;
    }

    public function get_mediaGroupID()
    {
        return $this->mediaGroupID;
    }

    public function get_song_number()
    {
        return $this->song_number;
    }

    public function get_title()
    {
        return $this->title;
    }

    public function get_media()
    {
        //TODO: return all pictures / audio associated with the mediaGroupID
    }

    public function get_media_edit_input()
    {
        //TODO: return all pictures / audio associated with the mediaGroupID with add / delete options
    }

    public static function get_all($order_by_attr, $asc = "1", $limit = null)
    {
        //TODO: Get all songs maybe by artist?
    }

    public static function get_table_header($column)
    {
        switch ($column) {
            case "artist_name":
                return "Artist Name";
                break;
            case "mediaGroupID":
                return "Media Group ID";
                break;
            case "song_number":
                return "Song Number";
                break;
            case "title":
                return "Title";
                break;
            default:
                return null;
        }
    }

    public function get_table_data($column, $editable = false)
    {
        switch ($column) {
            case "artist_name":
                return $this->get_artist_name();
                break;
            case "mediaGroupID":
                return $this->get_mediaGroupID();
                break;
            case "song_number":
                return $this->get_song_number();
            case "title":
                return $this->get_title();
                break;
                return null;
        }
    }

    public static function get_attr_order_by($column, $asc)
    {
        switch ($column) {
            case "artist_name":
                return "artistName" . ($asc ? " ASC" : " DESC");
                break;
            case "mediaGroupID":
                return "mediaGroupID" . ($asc ? " ASC" : " DESC");
                break;
            case "song_number":
                return "songNumber" . ($asc ? " ASC" : " DESC");
                break;
            case "title":
                return "title" . ($asc ? " ASC" : " DESC");
                break;
            default:
                return null;
        }
    }

    public static function get_attr_name($attr)
    {
        switch ($attr) {
            case "artist_name":
                return "artistName";
                break;
            case "mediaGroupID":
                return "mediaGroupID";
                break;
            case "song_number":
                return "songNumber";
                break;
            case "title":
                return "title";
                break;
            default:
                return null;
        }
    }
    /*****************************
     * Define additional functions
     * ***************************
     */

    public function insert_into_database()
    {
        $result = my_sql::insert("artistsong", array("artistName", "mediaGroupID", "songNumber", "title"), array($this->artist_name, $this->mediaGroupID, $this->song_number, $this->title));
        return $result;
    }


    public function delete_from_database()
    {
        $result = my_sql::delete("artistsong", "artistName = :0 AND mediaGroupID = :1 AND songNumber = :2", array($this->get_artist_name(), $this->get_mediaGroupID(), $this->get_song_number()));
        if ($result != false) {
            //echo "<script type='text/javascript'>window.onload=function(){alert(\"Song deleted.\"); window.location.assign(\"update_profile.php\");};</script>";
        }
        return $result;
    }
}
