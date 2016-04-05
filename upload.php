<?php
$target_dir = "/opt/lampp/htdocs/excel/";
$target_file = $target_dir . basename($_FILES["file"]["name"]);
$uploadOk = true;
$fileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = false;
}
// Check file size
if ($_FILES["file"]["size"] > 20*1024*1024) {
    echo "Sorry, your file is too large.";
    $uploadOk = false;
}
// Allow certain file formats
if($fileType != "xls" && $fileType != "xlsx") {
    echo "Sorry, only xls, xlsx are allowed.";
    $uploadOk = false;
}
// Check if $uploadOk is set to 0 by an error
if (!$uploadOk) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["file"]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>