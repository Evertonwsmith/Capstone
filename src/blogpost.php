<?php
require_once 'my_sql.php';
include_once 'my_sql_cred.php';
require_once 'mail/mail.php';
include_once 'mail/mail_cred.php';
require_once 'web_info/url.php';

class blogpost
{

    private $blog_id, $media_group_id, $title, $timestamp, $text, $is_public;
    private static $blogpost_detail_link = "blogpost_detail.php";
    private $relevance = null;

    public function __construct($blog_id)
    {
        if ($blog_id === "new") {
            $this->blog_id = null;
            $this->media_group_id = null;
            $this->title = "New Blogpost";
            $this->timestamp = null;
            $this->text = "";
            $this->is_public = "0";
            $blog_id = $this->insert_into_database();
        }

        $data = my_sql::select("*", "blogpost", "blogID=:0", array("$blog_id"));
        if (count($data) == 1) {
            $data = $data[0];
            $this->blog_id = $data['blogID'];
            $this->media_group_id = $data['mediaGroupID'];
            $this->title = $data['title'];
            $this->timestamp = $data['timestamp'];
            $this->text = $data['text'];
            $this->is_public = $data['isPublic'];
        } else {
            //echo "Blogpost, $blog_id, does not exist!";
            return null;
        }
    }

    /****************
     * Define getters
     * **************
     */

    public function get_blog_id()
    {
        return $this->blog_id;
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
        //TODO: return all pictures / audio associated with the media_group_id
    }

    public function get_blogpost_detail_link()
    {
        return "<a href='" . blogpost::$blogpost_detail_link . "?blog_id=" . $this->get_blog_id() . "'>View / Edit Blogpost</a>";
    }

    public function is_public()
    {
        return isset($this->is_public) && $this->is_public ? true : false;
    }

    public function get_relevance()
    {
        return $this->relevance;
    }

    public function get_media_edit_input()
    {
        $files = "<div style='overflow-y:scroll; width: 100%; height: 20rem; whitespace: no-wrap;'>";
        //TODO: return all pictures / audio associated with the media_group_id with add / delete options
        $mgid = $this->get_media_group_id();
        if (isset($mgid) && my_sql::exists("mediagroup", "mediaGroupID=:0", array($mgid))) {
            //Add script for deleting cards
            $files .= "<script>
                function delete_media_card(elem, id, table, filename){
                    if(confirm('Delete \"'+filename+'\"?')){
                        $.ajax('blogpost_remove_media.php', {
                            type: 'POST',  // http method
                            data: {
                                table: table,
                                id: id,
                                filename: filename
                            },  // data to submit
                            success: function (data, status, xhr) {
                                console.log(data);
                                let card = elem.parentElement;
                                card.remove();
                            },
                            error: function (jqXhr, textStatus, errorMessage) {
                                //console.log('Error' + errorMessage);
                            }
                        });
                    }
                }
            </script>";
            //Output all images
            if (my_sql::exists("image", "mediaGroupID=:0", array($mgid))) {
                $images = my_sql::select("imageID, filename", "image", "mediaGroupID=:0", array($mgid));
                foreach ($images as $img) {
                    $img_link = "<div class='img-container-preview'><img src='../" . $img['filename'] . "' alt='file not found'></div>";
                    $embed_img_link = "<div class=\"img-container\"><img src=\"../" . $img['filename'] . "\" alt=\"file not found\"></div>";
                    $files .= "
                    <div class='media-card'>
                        <div id='img_" . $img['imageID'] . "_toast' class='toast hide'>
                            <div class='toast-body'>
                                Selection has been copied to clipboard!
                            </div>
                        </div>
                        <div class='media-card-inner'>
                            <div class='text-copy' onmousedown='$(this).find(\"input\").click();'><input value='$embed_img_link' class='copy-only' onclick='this.select();document.execCommand(\"copy\");$(\"#img_" . $img['imageID'] . "_toast\").toast(\"show\");' style='width: 100%;' aria-disabled='true'/></div>
                            " . $img_link . "
                        </div>
                        <button onclick='delete_media_card(this, \"" . $img['imageID'] . "\", \"image\", \"" . $img['filename'] . "\");'>Delete</button>
                    </div>
                    ";
                }
            }
            //Output all audio
            if (my_sql::exists("audio", "mediaGroupID=:0", array($mgid))) {
                $audio_files = my_sql::select("audioID, filename", "audio", "mediaGroupID=:0", array($mgid));
                foreach ($audio_files as $audio) {
                    $audio_link = "<div><audio controls><source src='../" . $audio['filename'] . "'>Your browser does not support the audio tag</audio></div>";
                    $embed_audio_link = "<div><audio class=\"default-audio\" controls><source src=\"../" . $audio['filename'] . "\"></audio></div>";
                    $files .= "
                    <div class='media-card'>
                        <div id='audio_" . $audio['audioID'] . "_toast' class='toast hide'>
                            <div class='toast-body'>
                                Selection has been copied to clipboard!
                            </div>
                        </div>
                        <div class='media-card-inner'>
                            <div class='text-copy' onmousedown='$(this).find(\"input\").click();'><input value='$embed_audio_link' class='copy-only' onclick='this.select();document.execCommand(\"copy\");$(\"#audio_" . $audio['audioID'] . "_toast\").toast(\"show\");' style='width: 100%;' aria-disabled='true'/></div>
                            " . $audio_link . "
                        </div>
                        <button onclick='delete_media_card(this, \"" . $audio['audioID'] . "\", \"audio\", \"" . $audio['filename'] . "\");'>Delete</button>
                    </div>
                    ";
                }
            }
        }
        $files .= "</div>";
        return $files;
    }

    public function get_title_edit_input()
    {
        $attr_name = "blogpost_title";
        $disabled = $this->is_public() ? "aria-disabled='true' disabled='true' title='The blog must be unpublished before editing this information.'" : "";
        $autosave = "onchange='blogpost_save({blog_id: " . $this->get_blog_id() . ", $attr_name: this.value})'";
        $input = "<input type='text' name='$attr_name' value='' placeholder='" . $this->get_title() . "' $disabled $autosave />";
        $input .= $this->is_public() ? "<div class='error'>The blog must be unpublished before editing this information.</div>" : "";
        return $input;
    }

    public function get_text_edit_input($rows = 16, $cols = 80)
    {
        $attr_name = "blogpost_text";
        $disabled = $this->is_public() ? "aria-disabled='true' disabled='true' title='The blog must be unpublished before editing this information.'" : "";
        $autosave = "onchange='blogpost_save({blog_id: " . $this->get_blog_id() . ", $attr_name: this.value})'";
        $input = ("<textarea rows='$rows' cols='$cols' maxlength='4000' name='$attr_name' $disabled $autosave>"
            . $this->get_text()
            . "</textarea>");
        $input .= $this->is_public() ? "<div class='error'>The blog must be unpublished before editing this information.</div>" : "";
        $input .= "<button class='' onclick='toggle_quick_copy(this);'>Quick Copy Menu</button><br>
        <div id='quick-copy' style='display:none;border:1px solid #666;border-radius:0.5rem;padding:1rem;margin:0.5rem;'>
            <div class='row justify-content-center'>";
        $copy_text = ["New Line" => "<br>", "Divider" => "<br><hr><br>", "Header" => "<h2>  </h2>", "Paragrah" => "<p>  </p>", "Bold" => "<strong>  </strong>", "Italics" => "<i>  </i>", "Underline" => "<ins>  </ins>", "Strike Through" => "<del>  </del>", "Subscript" => "<sub>  </sub>", "Superscript" => "<sup>  </sup>", "Bullet Point" => "<li>  </li>", "Container" => "<div style=\"max-width:40rem;height:30rem;\">  </div>"];
        foreach ($copy_text as $name => $text) {
            $container = 0;
            if ($name === "Container") {
                $input .= "<div style='text-align: center;'>";
                $container = 1;
            }
            $id_name = str_replace(" ", "_", $name);
            $input .= "
            <div style='margin:0.5rem;'>
                <div class='copy-text-title'>$name</div>
                <div class='text-copy-container'>
                    <div id='" . $id_name . "_toast' class='toast hide'>
                        <div class='toast-body'>Selection has been copied to clipboard!</div>
                    </div>
                    <div class='text-copy' onmousedown='$(this).find(\"input\").click();'>
                        <input " . ($container ? "id='sizer-container'" : "") . "class='copy-only' disable='true' aria-disable='true' value ='$text' onclick='this.select();document.execCommand(\"copy\");$(\"#" . $id_name . "_toast\").toast(\"show\");' />
                    </div>
                </div>
            </div>";
        }
        $input .= "
            <div>
                <label id='width-label'>Width: 40</label><br>
                <input id='width-sizer' type='range' min='0' max='100' value='40' onblur='close_sizer();' oninput='set_sizer(this.value, $(\"#height-sizer\").val());' />
            </div>
            <div>
                <label id='height-label'>Height: 30</label><br>
                <input id='height-sizer' type='range' min='0' max='100' value='30' onblur='close_sizer();' oninput='set_sizer($(\"#width-sizer\").val(), this.value);' />
            </div>
        </div>";
        $input .= "<script>
        function toggle_quick_copy(elem){
            if($('#quick-copy').css('display')==='none'){
                $('#quick-copy').css({'display':'inline-block'});
            } else {
                $('#quick-copy').css({'display':'none'});
            }
            $(elem).toggleClass('current-page');
        }

        function set_sizer(width, height){
            $('#sizer-container').val('<div style=\"max-width:' + width + 'rem;height:' + height + 'rem;\">  </div>');
            $('#width-label').html('Width: '+width);
            $('#height-label').html('Height: '+height);
            $('#sample-sizer').css({'width': ''+width+'rem','height': ''+height+'rem'});
        }

        function close_sizer(){
            $('#sample-sizer').css({'width':0,'height':0});
        }
        </script>";
        $input .= "</div>
        </div><br>";
        $input .= "<div style='position:absolute;z-index:99;background-color:#000000AA;left:0;overflow:visible;box-shadow:0 0 2rem #FF0080;'>
            <div id='sample-sizer' style='max-width:100%; max-height:100%;'>
                <div style='position:absolute;top:0;right:0;bottom:0;left:0;overflow:hidden;color:white;'><div style='position:absolute;top:10%;bottom:50%;left:5%;right:5%;'><h3>This is an overlay to show you how big the container will be.<br>Click anywhere to close...</h3></div></div>
            </div>
        </div>";
        return $input;
    }

    public function get_is_public_edit_form()
    {
        $attr_name = "blogpost_is_public";
        $message = "if(confirm(\"Publish blogpost: " . $this->get_title() . " ?\")){";
        $save =  "onclick='" . ($this->is_public() ? "" : $message) . "blogpost_save({blog_id: " . $this->get_blog_id() . ", $attr_name: this.value}, true);" . ($this->is_public() ? "" : "}") . "'";
        $input = "<button $save value='" . ($this->is_public() ? "0" : "1") . "'>" . ($this->is_public() ? "Unpublish" : "Publish") . "</button>";
        return $input;
    }

    public function get_deletion_form()
    {
        $attr_name = "delete_blogpost";
        $form = ("<form method='post'>"
            . "<input type='button' onclick='if(confirm(\"Are you sure? Deleting a blogpost removes all its data from the database. This action is irreversible.\")){this.form.submit();}' value='Delete' />"
            . "<input type='hidden' name='$attr_name' value='1' />"
            . "<input type='hidden' name='blog_id' value='" . $this->get_blog_id() . "' />"
            . "</form>");
        return $form;
    }

    public static function get_new_blogpost_button()
    {
        $form = ("<form action='" . blogpost::$blogpost_detail_link . "' method='post'>"
            . "<input type='submit' name='sumbit' value='Add New Blogpost' />"
            . "<input type='hidden' name='blog_id' value='new' />"
            . "</form>");
        return $form;
    }

    public static function get_all_ids($order_by_attr, $asc = "1", $limit = null, $only_public = false)
    {
        $sort_attr = blogpost::get_attr_name($order_by_attr);
        $id = blogpost::get_attr_name("blog_id");

        $sel = $id . (isset($sort_attr) ? ",$sort_attr" : "");
        $frm = "blogpost";
        $where = $only_public ? "isPublic='1'" : "";
        $ord = blogpost::get_attr_order_by($order_by_attr, $asc);

        // query the database using the my_sql api
        $result = my_sql::select($sel, $frm, $where, null, $ord, null, null, $limit);

        // create blogposts for each row
        $ids = array();
        foreach ($result as $row) {
            array_push($ids, $row[$id]);
        }

        return $ids;
    }

    public static function get_all($order_by_attr, $asc = "1", $limit = null)
    {
        $blogposts = blogpost::get_all_ids($order_by_attr, $asc, $limit);
        foreach ($blogposts as $index => $prod_id) {
            $blogposts[$index] = new blogpost($prod_id);
        }
        return $blogposts;
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
        $table = "<table class='vertical'>";
        foreach ($columns as $col) {
            $table .= ("<tr>"
                . "<th>" . $this->get_table_header($col, $editable) . "</th>"
                . "<td>" . $this->get_table_data($col, $editable) . "</td>"
                . "</tr>");
        }
        $table .= "</table>";
        //Add autosave script
        $table .= "<script>
            function blogpost_save(post, reload){
                $.ajax('blogpost_save.php', {
                    type: 'POST',  // http method
                    data: post,
                    success: function (data, status, xhr) {
                        //console.log(data);
                        if(reload){
                            location.reload();
                        }
                    },
                    error: function (jqXhr, textStatus, errorMessage) {
                        //console.log('Error' + errorMessage);
                    }
                });
            }
        </script>";
        return $table;
    }

    public static function get_table_header($column, $editable = false)
    {
        switch ($column) {
            case "blog_id":
                return "Blog ID";
                break;
            case "media_group_id":
                return "Media Group ID";
                break;
            case "media":
                return "Attached Media";
                break;
            case "title":
                return $editable ? "<span class='required'>*</span>  Title" : "Title";
                break;
            case "timestamp":
                return "Date and Time";
                break;
            case "text":
                return $editable ? "
                    <span class='required'>*</span> 
                    Text 
                    <div class='help-hover'><b>[?]</b>
                        <div>
                            To format the blogpost text, you can use HTML code and inline CSS. Help on this topic can be found <a href='https://www.w3schools.com/html/html_basic.asp'>here</a>.
                        </div>
                    </div>" : "Text";
                break;
            case "blogpost_detail":
                return "Blogpost Details";
                break;
            case "is_public":
                return "Blogpost Visibility";
                break;
            case "search_relevance":
                return "Search Relevance";
                break;
            default:
                return null;
        }
    }

    public function get_table_data($column, $editable = false)
    {
        switch ($column) {
            case "blog_id":
                return $this->get_blog_id();
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
            case "blogpost_detail":
                return $this->get_blogpost_detail_link();
                break;
            case "is_public":
                return ($this->is_public() ? "<b style='color:#70c1b3'>Published</b>" : "<b style='color:#ff0080'>Unpublished</b>");
                break;
            case "search_relevance":
                return $this->get_relevance();
                break;
            default:
                return null;
        }
    }

    public static function get_attr_order_by($column, $asc)
    {
        switch ($column) {
            case "blog_id":
                return "blogID" . ($asc ? " ASC" : " DESC");
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
            case "search_relevance":
                return null;
            case "blogpost_detail":
            case "timestamp":
            default:
                return "timestamp" . ($asc ? " ASC" : " DESC");
        }
    }

    public static function get_attr_name($attr)
    {
        switch ($attr) {
            case "blog_id":
                return "blogID";
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
        if (isset($val) && strlen($val) > 0) {
            $result = my_sql::update("blogpost", array("title"), array($val), "blogID=:0", array($this->get_blog_id()), true);
            if (isset($result) && $result != false) $this->title = $val;
            return $result;
        } else return false;
    }

    public function set_text($val)
    {
        $result = my_sql::update("blogpost", array("text"), array($val), "blogID=:0", array($this->get_blog_id()), true);
        if (isset($result) && $result != false) $this->text = $val;
        return $result;
    }

    public function set_public($is_public = 1)
    {
        $result = my_sql::update("blogpost", array("isPublic"), array($is_public), "blogID=:0", array($this->get_blog_id()));
        if (!isset($this->timestamp) || $this->get_timestamp() === "") {
            $this->set_timestamp();
            $this->send_notifications();
        }
        if (isset($result) && $result != false) $this->is_public = $is_public;
        return $result;
    }

    public function set_timestamp($reset = false)
    {
        $date = (new DateTime())->setTimezone(new DateTimeZone('America/Vancouver'));
        $datetime = $reset ? null : $date->format('Y-m-d H:i:s');
        $result = my_sql::update("blogpost", array("timestamp"), array($datetime), "blogID=:0", array($this->get_blog_id()));
        if (isset($result) && $result != false) {
            $this->timestamp = $datetime;
        }
        return $result;
    }

    public function set_relevance($value)
    {
        $this->relevance = $value;
    }

    public function add_media_file()
    {
        if (isset($_SESSION['blog_id'])) {
            $bid = $_SESSION['blog_id'];
            if ($bid != $this->get_blog_id()) exit("<div class='error'>ERROR: blog ID's did not match when adding media file.</div>");
            unset($_SESSION['blog_id']);
            if (isset($_SESSION['media_group_id'])) unset($_SESSION['media_group_id']);
        }
    }

    public function set_media_group_id($mgid)
    {
        if (isset($mgid)) {
            $result = my_sql::update("blogpost", array("mediaGroupID"), array($mgid), "blogID=:0", array($this->get_blog_id()));
            if ($result != false) {
                $this->media_group_id = $mgid;
            }
            return $result;
        } else {
            return false;
        }
    }


    /*****************************
     * Define additional functions
     * ***************************
     */

    public function insert_into_database()
    {
        $result = my_sql::insert_get_last_id(
            "blogpost",
            array("blogID", "mediaGroupID", "title", "text", "timestamp", "isPublic"),
            array($this->blog_id, $this->media_group_id, $this->title, $this->text, $this->timestamp, $this->is_public)
        );
        return $result;
    }

    public function check_attr_update()
    {
        foreach ($_POST as $key => $val) {
            switch ($key) {
                case "blogpost_title":
                    $this->set_title($val);
                    break;
                case "blogpost_text":
                    $this->set_text($val);
                    break;
                case "blogpost_is_public":
                    $this->set_public($val);
                    break;
                case "blogpost_file_upload":
                    $this->add_media_file();
                    break;
                case "delete_blogpost":
                    $this->delete_from_database();
                    break;
            }
        }
    }

    public function delete_from_database()
    {
        $result = my_sql::delete("blogpost", "blogID=:0", array($this->get_blog_id()));
        if ($result != false) {
            echo "<script type='text/javascript'>window.onload=function(){alert(\"Blogpost successfully deleted.\"); window.location.assign(\"admin.php?tab=blogpost\");};</script>";
        }
        return $result;
    }

    public function send_notifications()
    {
        if (my_sql::exists("useraccount", "blogOptIn='1' AND isActive='1'")) {
            $user_emails = my_sql::select("userEmail, firstName, lastName", "useraccount", "blogOptIn='1' AND isActive='1'");
            $blog_text = strip_tags($this->get_text(), "<br><h1><h2><h3><h4><h5><h6><strong><i><p><li>");
            $url = url::get_website_url() . "/pages/blog_public.php?blog_id=" . $this->get_blog_id();
            $recipients = array();
            foreach ($user_emails as $email) {
                $recipients[$email['userEmail']] = ($email['firstName'] . " " . $email['lastName']);
            }
            global $mail_sender_email, $mail_sender_password;
            $mail = new mail($mail_sender_email, $mail_sender_password);
            $mail->set_subject("Sleepovers Blog Posted an Update!");
            $mail->set_body("
            <h2>Sleepovers published a new blogpost!</h2>
            <br><hr><br>

            <a href='$url'><h2>'" . $this->get_title() . "'</h2></a>
            <i>Published on: " . $this->get_timestamp() . "</i>
            <br><br>
            " . $blog_text . "

            <br><hr><br>
            Click <a href='$url'>HERE</a> to view the full post!
            ");
            $mail->set_port(587);
            $mail->set_recipient($mail_sender_email);
            return $mail->send_mail($recipients);
        } else {
            return 0;
        }
    }
}
