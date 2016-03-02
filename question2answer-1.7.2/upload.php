<?php

	// specifies the directory where the file is going to be placed
	$target_dir = "uploads/";

	// allowed file types
	$allowed = array("application/x-zip-compressed", "application/x-compressed");

	$numfiles = count($_FILES["files"]["name"]);

	for ($count = 0; $count < $numfiles; $count++)
	{
		
		// specifies the path of the file to be uploaded
		$target_file = $target_dir . basename($_FILES["files"]["name"][$count]);

		// upload status: 1=good, 0=bad
		$uploadOk = 1;

		// holds the file extension of the file
		$fileType = pathinfo($target_file,PATHINFO_EXTENSION);

		
		
		// //Check if file is a zip file
	 //    if(in_array($_FILES["files"]["type"][$count], $allowed)
	 //    {
	 //        echo "File is a zip file.";
	 //        $uploadOk = 1;
	 //    } else {
	 //        echo "File is not a zip file.";
	 //        $uploadOk = 0;
	 //    }
		
	// 	// Check if file already exists
		if (file_exists($target_file)) 
		{
		    echo "File already exists.";
		    $uploadOk = 0;
		}
		
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) 
		{
		    echo "Error, file was not uploaded.";
		} else  // if everything is ok, try to upload file
		{
		    if (move_uploaded_file($_FILES["files"]["tmp_name"][$count], $target_file)) 
		    {
		        echo "The file ". basename( $_FILES["files"]["name"][$count]). " has been uploaded.";
		    } else {
		        echo "There was an error uploading your file.";
		    }
		}
	}
	echo var_dump($_FILES);
?> 