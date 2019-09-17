<?php

$max_filesize = 200; //max allowable filesize in MB
$filetypes = "wav,FLAC"; //allowable filetypes

if (!isset($_SESSION['user_email'])) {
	header("location:home.php");
} else {
	$user_email = $_SESSION['user_email'];
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">

<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<script type="text/javascript" src="../src/pluploader/js/plupload.full.min.js"></script>
</head>
<div id="filelist"></div>
<br />
<div id="container">
	<b id="warning"> Name file before clicking [select file]*</b><br><br>
	<b>File name:</b> <input id="song_name" type="text" name="name"><br>
	<button onclick="let pick = document.getElementById('pickfiles');pick.click();">Select Files<input type="file" style="display: none;" accept="audio/mp3, audio/wav" id="pickfiles" value="[Select file]" /></button>
	<button id="uploadfiles">Upload files</button>
</div>
<br />
<pre id="console"></pre>

<script type="text/javascript">
	// Custom example logic
	var uploader = new plupload.Uploader({
		runtimes: 'html5,flash,silverlight,html4',
		browse_button: 'pickfiles', // you can pass an id...
		container: document.getElementById('container'), // ... or DOM Element itself
		url: '../src/pluploader/upload.php',
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
					document.getElementById('filelist').innerHTML += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
					var re = /(?:\.([^.]+))?$/;
					var ext = re.exec(file.name)[1];
					if (document.getElementById('song_name').value !== "") {
						file.name = document.getElementById('song_name').value + '.' + ext;
					}
					console.log(file.name);
					console.log(<?php echo "'$directory'" ?>);
					file.name = <?php echo "'$directory'" ?> + '%$%' + file.name;
				});
			},
			UploadProgress: function(up, file) {
				document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
			},
			Error: function(up, err) {
				document.getElementById('console').appendChild(document.createTextNode("\nError #" + err.code + ": " + err.message));
			}
		}
	});
	uploader.init();
</script>
</body>

</html>