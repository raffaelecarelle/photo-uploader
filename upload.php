<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';

$uploadHandler = new App\FileUploadHandler();
echo json_encode($uploadHandler->initialize());
