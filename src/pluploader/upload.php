<?php

$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

//Get a file name
$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];

//Remove any slashes in the filename
$fileName = str_replace("/", "", $fileName);


if (!file_exists($directory)) {
	mkdir($directory, 0777, true);
}


//Remove any special chars
$fileName = preg_replace('/[^A-Za-z0-9.\-]/', '-', $fileName);
//Total path of file:
$filePath = $directory . $fileName;

// Open temp file
$out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");

if ($out) {
	// Read binary input stream and append it to temp file
	$in = @fopen($_FILES['file']['tmp_name'], "rb");

	if ($in) {
		while ($buff = fread($in, 4096))
			fwrite($out, $buff);
	} else
		die('{"OK": 0, "info": "Failed to open input stream."}');

	@fclose($in);
	@fclose($out);

	@unlink($_FILES['file']['tmp_name']);
} else
	die('{"OK": 0, "info": "Failed to open output stream."}');


// Check if file has been uploaded
if (!$chunks || $chunk == $chunks - 1) {
	// Strip the temp .part suffix off 
	rename("{$filePath}.part", $filePath);
	//Set variable to communicate completion
	$upload_complete = 1;
}

//This is now moved to the helper upload file on a per-page basis
//die('{"OK": 1, "info": "Upload successful."}');
