<?php
if (!isset($_SESSION['user_email'])) {
    header("location:home.php");
} else {
    $user_email = $_SESSION['user_email'];
}

if (!isset($blog_id)) {
    exit();
}

$max_filesize = 200; //max allowable filesize in MB
$filetypes = "jpg,jpeg,png,gif,mp3,wav,FLAC,"; //allowable filetypes

//Set SESSION blog_id
$_SESSION['blog_id'] = $blog_id;
?>

<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <script type="text/javascript" src="../src/pluploader/js/plupload.full.min.js"></script>
</head>
<div id="filelist"></div>
<div id="container">
    <button onclick="let pick = document.getElementById('pickfiles');pick.click();">Upload File<input type="file" style="display: none;" accept="image/*" id="pickfiles" value="[Select file]" /></button>
    <input type="button" id="uploadfiles" style="display: none;" />
</div>
<br />
<pre id="console" class='error'></pre>

<script type="text/javascript">
    // Custom example logic
    var uploader = new plupload.Uploader({
        runtimes: 'html5,flash,silverlight,html4',
        browse_button: 'pickfiles', // you can pass an id...
        container: document.getElementById('container'), // ... or DOM Element itself
        url: '../src/blogpost_file_upload.php',
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
                    let elem = document.getElementById(file.id);
                    if (elem !== null && elem !== undefined) {
                        remove_file(elem);
                    } else {
                        let dot_index = file.name.lastIndexOf(".");
                        let name = (file.name).substring(0, dot_index);
                        let ext = (file.name).substring(dot_index, (file.name).length);
                        let new_div = document.createElement("div");
                        new_div.id = file.id;
                        new_div.innerHTML = (file.name + " (" + plupload.formatSize(file.size) + ")" +
                            "<div class='upload-progress-bar row justify-content-center'></div>");
                        document.getElementById('filelist').append(new_div);
                    }
                });

                if (files.length > 0) {
                    //upload file
                    document.getElementById('uploadfiles').click();
                }
            },
            UploadProgress: function(up, file) {
                document.getElementById(file.id).getElementsByClassName('upload-progress-bar')[0].innerHTML = "<div class='upload-progress-fill' style='right: " + (100 - file.percent) + "%;'></div><div class='upload-progress-text'>" + file.percent + "%</div>";
            },
            Error: function(up, err) {
                document.getElementById('console').appendChild(document.createTextNode("\nError #" + err.code + ": " + err.message));
            },
            UploadComplete: function(up, files) {
                for (let i = 0; i < files.length; i++) {
                    if (files[i].status != plupload.DONE) {
                        document.getElementById("console").innerHTML = "There was a problem uploading one or more files, please try again.";
                        return;
                    }
                }
                if (files.length > 0) {
                    //Complete
                    $.ajax('blogpost_save.php', {
                        type: 'POST', // http method
                        data: {
                            blog_id: 'session',
                            blogpost_file_upload: '1'
                        }, // data to submit
                        success: function(data, status, xhr) {
                            console.log(data);
                            location.reload();
                        },
                        error: function(jqXhr, textStatus, errorMessage) {
                            //console.log('Error' + errorMessage);
                        }
                    });
                }
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
        let elem = e.parentElement;
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