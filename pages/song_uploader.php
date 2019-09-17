<?php
$page_title = 'Upload Music';
include 'header.php';
$_SESSION['song_number'] = 1;
include "song_audio_uploader.php";
include 'footer.php'; ?>