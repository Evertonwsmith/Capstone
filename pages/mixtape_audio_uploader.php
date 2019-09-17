<?php
include('header.php');
require_once '../src/product.php';
if (!isset($_SESSION['user_email'])) {
    header("location:home.php");
} else {
    $user_email = $_SESSION['user_email'];
}

if (isset($_SESSION['product_id'], $_SESSION['quantity'])) {
    $product = new product($_SESSION['product_id']);
} else {
    exit("<div class='error'>Error: product_id or quantity not set in SESSION.</div>");
}

$max_filesize = 200; //max allowable filesize in MB
$filetypes = "wav,FLAC,mp3,png,jpg,jpeg,txt"; //allowable filetypes
$media_group_id = my_sql::insert_get_last_id("mediagroup", array("mediaGroupID"), array(null)); //Reserve a mediaGroupID
if ($media_group_id == false) {
    //Alert error
    echo "<p class='error'>There was an unkown error while reserving a media group ID. Please try again or contact administration.</p>";
    include 'footer.php';
    exit();
}

//Set SESSION upload_directory and media_group_id
$_SESSION['upload_directory'] = "mixtape/" . $media_group_id . "/";
$_SESSION['media_group_id'] = $media_group_id;

/**
 * TODO: set orderID to a session variable
 */

?>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">

<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <script type="text/javascript" src="../src/pluploader/js/plupload.full.min.js"></script>
</head>
<div class="row">
    <div class="col-12">
        <br>
        <h2>Welcome to Sleepovers Record Builder</h2>
        <hr>
    </div>
    <div class="w-100"></div>
    <div class="col-12 col-lg-5 col-xl-5 mixtape_upload">
        <h2>Product Information</h2>
        <hr>
        <br>
        <h4>Product Name:</h4>
        <div class="mixtape_item_info">
            <p><?php echo $product->get_name(); ?></p>
        </div>
        <br>
        <h4>Product Description:</h4>
        <div class="mixtape_item_info">
            <p><?php echo $product->get_description(); ?></p>
        </div>
        <br>
        <div id="mixtape_item_price" style="float: left;">
            <h5 class="">Price:</h5>
            <p style="padding-top: 10%;"> <?php echo $product->get_price(); ?> </p>
        </div>
        <div id="mixtape_item_quantity" style="float: left;">
            <h5 class="">Quantity:</h5>
            <input type="number" value="<?php echo $_SESSION['quantity']; ?>" min=1 <?php echo $product->get_max_quantity() > 0 ? "max='" . $product->get_max_quantity() . "'" : ""; ?> onclick="set_session({quantity:this.value});" />
        </div>
    </div>
    <div class="col-1"></div>
    <div class="col-12 col-lg-6 col-xl-6 mixtape_upload" id="mixtape_uploader">
        <p class="lead">Add songs and images for your mixtape here</p>
        <p>
            <button class="btn btn-primary mixtape_info_button" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample" style="background-color: #ff0080; border-color: #ff0080;">
                Upload Instructions
            </button>
        </p>
        <div class="collapse" id="collapseExample">
            <div class="card card-body">
                <p style="color: black;">To get started select songs to be uploaded by pressing the "Add Song" button</p>
                <p style="color: black;">After choosing the song files you want you can give each song a different name by altering the text field next to each song</p>
                <p style="color: black;">To remove a song press the "Remove" button next to the song you wish to delete</p>
                <p style="color: black;">When you have all the songs that you want press "Upload Files".</p>
                <p style="color: black;">Finally click "Add to Cart" afterwards to add your custom vinyl to your cart.</p>
            </div>
        </div>
        <br />
        <div id="container">
            <button onclick="let pick = document.getElementById('pickfiles');pick.click();">Add File<input type="file" style="display: none;" accept="audio/*" id="pickfiles" value="[Select file]" /></button>
            <button id="upload-trigger" onclick="set_file_names();disable_inputs();document.getElementById('uploadfiles').click();">
                Upload Files
            </button>
            <input type="button" id="uploadfiles" style="display: none;" />
        </div>
        <div class="d-flex flex-row-reverse">
            <div id="cart_container"></div>
        </div>
        <br />
        <pre id="console"></pre>
        <div id="filelist"></div>
        <hr>
    </div>
</div>

<script type="text/javascript">
    // Custom example logic
    var uploader = new plupload.Uploader({
        runtimes: 'html5,flash,silverlight,html4',
        browse_button: 'pickfiles', // you can pass an id...
        container: document.getElementById('container'), // ... or DOM Element itself
        url: '../src/mixtape_audio_upload.php',
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
                            "<div class='upload-progress-bar row justify-content-center'></div>");
                        document.getElementById('filelist').append(new_div);
                    }
                });
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
                        document.getElementById("console").innerHTML = "<div class='error'>There was a problem uploading one or more files, please try again.</div>";
                        return;
                    }
                }
                if (files.length > 0) {
                    //Complete
                    let id = "add_to_cart";
                    if (document.getElementById(id) !== null) return; //button already exists
                    let add_to_cart = document.createElement("button");
                    add_to_cart.id = id;
                    add_to_cart.innerHTML = "Add to Cart";
                    add_to_cart.onclick = (
                        function(event) {
                            event.preventDefault();
                            var newForm = jQuery('<form>', {
                                'action': '../pages/cart.php',
                                'method': 'post',
                                'target': '_top'
                            }).append(jQuery('<input>', {
                                'name': 'mixtape_cart_item',
                                'value': '1',
                                'type': 'hidden'
                            }));
                            newForm.hide().appendTo("body").submit();
                        });
                    let container = document.getElementById("cart_container");
                    container.appendChild(add_to_cart);
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

    function disable_inputs() {
        let files = document.getElementById("filelist");
        let inputs = files.getElementsByTagName("input");
        let buttons = files.getElementsByTagName("button");
        for (let i = 0; i < inputs.length; i++) {
            inputs[i].disabled = true;
        }
        for (let i = 0; i < buttons.length; i++) {
            buttons[i].disabled = true;
        }
    }
</script>
<?php echo "<script src='../src/javascripts/cart.js'></script>" ?>
<?php include 'footer.php' ?>