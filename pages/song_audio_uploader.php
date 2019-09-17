<?php
if (!isset($_SESSION['user_email'])) {
    header("location:home.php");
} else {
    $user_email = $_SESSION['user_email'];
    $artist = my_sql::select("*", "artistaccount", "userEmail = :0", array($user_email));
    if ($artist) {
        $artist = $artist[0];
        $artist_name = $artist['artistName'];
    } else {
        header("location:home.php");
    }
}
$max_filesize = 200; //max allowable filesize in MB
$filetypes = "wav,FLAC,mp3"; //allowable filetypes
$media_group_id = my_sql::insert_get_last_id("mediagroup", array("mediaGroupID"), array(null)); //Reserve a mediaGroupID
if ($media_group_id == false) {
    //Alert error
    echo "<p class='error'>There was an unkown error while reserving a media group ID. Please try again or contact administration.</p>";
    include 'footer.php';
    exit();
}
//Set SESSION upload_directory and media_group_id
$_SESSION['upload_directory'] = hash("sha256", $user_email) . "/songs/" . $media_group_id . "/";
$_SESSION['media_group_id'] = $media_group_id;
$_SESSION['artist_name'] = $artist_name;
/**
 * TODO: set orderID to a session variable
 */
?>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">

<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <script type="text/javascript" src="../src/pluploader/js/plupload.full.min.js"></script>
</head>
<h2>Song Upload</h2>
<div id="filelist"></div>
<br />
<div id="container">
    <button onclick="let pick = document.getElementById('pickfiles');pick.click();">Add Song<input type="file" style="display: none;" accept="audio/*" id="pickfiles" value="[Select file]" /></button>
    <button id="upload-trigger" onclick="set_file_names();document.getElementById('uploadfiles').click();">
        Upload files
    </button>
    <input type="button" id="uploadfiles" style="display: none;" />
</div>
<button onclick="window.location.href = 'update_profile.php'">Back</button><br>

<br />
<pre id="console"></pre>

<script type="text/javascript">
    // Custom example logic
    var uploader = new plupload.Uploader({
        runtimes: 'html5,flash,silverlight,html4',
        browse_button: 'pickfiles', // you can pass an id...
        container: document.getElementById('container'), // ... or DOM Element itself
        url: '../src/song_audio_upload.php',
        chunk_size: '200kb',
        max_retries: 3,
        flash_swf_url: '../js/Moxie.swf',
        silverlight_xap_url: '../js/Moxie.xap',
        filters: {
            max_file_size: '<?php echo $max_filesize; ?>mb',
            mime_types: [{
                title: "Files",
                //set filetypes accepted here
                extensions: "<?php echo $filetypes; ?>"
            }]
        },
        init: {
            PostInit: function() {
                document.getElementById('filelist').innerHTML = '';
                document.getElementById('uploadfiles').onclick = function() {
                    uploader.start();
                    return false;
                };
            },
            FilesAdded: function(up, files) {
                plupload.each(files, function(file) {
                    //Correct directory registering
                    //Set file name to new name plus old file extension
                    $elem = document.getElementById(file.id);
                    if ($elem === null || $elem === undefined) {
                        let dot_index = file.name.lastIndexOf(".");
                        let name = (file.name).substring(0, dot_index);
                        let ext = (file.name).substring(dot_index, (file.name).length);
                        let new_div = document.createElement("div");
                        new_div.id = file.id;
                        new_div.innerHTML = (file.name + " (" + plupload.formatSize(file.size) + ")" +
                            "<div class='d-flex flex-row'>" +
                            "<button onclick='remove_file(this);'>Remove</button>" + //remove entire element on click
                            "<input type='text' class='flex-grow-2' style='width:50%; max-width:22rem;' value='" + name + "' />" +
                            "<div class='ext p-2'>" + ext + "</div>" +
                            "</div>" +
                            "<div class='upload-progress-bar row justify-content-center'></div><hr>");
                        document.getElementById('filelist').append(new_div);
                    }
                });
            },
            UploadProgress: function(up, file) {
                document.getElementById(file.id).getElementsByClassName('upload-progress-bar')[0].innerHTML = "<div class='upload-progress-fill' style='right: " + (100 - file.percent) + "%;'></div><div class='upload-progress-text'>" + file.percent + "%</div>";
            },
            Error: function(up, err) {
                document.getElementById('console').appendChild(document.createTextNode("\nError #" + err.code + ": " + err.message));
            }
        }
    });
    uploader.init();

    function set_file_names() {
        var name_elem = document.getElementById("filelist").getElementsByTagName("input");
        for (let i = 0; i < name_elem.length; i++) {
            var filename = name_elem[i].value;
            var ext = name_elem[i].parentElement.getElementsByClassName("ext")[0].innerHTML;
            window.uploader.files[i].name = filename + ext;
        }
    }

    function remove_file(e) {
        let elem = e.parentElement.parentElement;
        let id = elem.id;
        let index = null;
        for (let i = 0; i < window.uploader.files.length; i++) {
            if (id == window.uploader.files[i].id) {
                index = i;
                break;
            }
        }
        if (index !== null && index !== undefined) {
            window.uploader.files.splice(index, 1);
            elem.remove(); //delete node
        }
    }
</script>
</body>

</html>