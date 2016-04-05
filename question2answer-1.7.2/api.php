<?php

/**
This class is designed to provide 3rd party applications to be able to view
the data stored on the Q2A site.

Author:	Robert Kloster

*/

/**
This section is needed in order to begin formatting into gzipped JSON.
*/
header("access-control-allow-origin: *");
header('Content-Type: application/json');

/**
This file contains the logic of many shared/common functions.
*/

require_once "./api_classes/util.php";

require_once "./api_classes/users.php";
require_once "./api_classes/posts.php";
require_once "./api_classes/questions.php";
require_once "./api_classes/answers.php";
require_once "./api_classes/comments.php";
require_once "./api_classes/tags.php";


$ret_type = NULL;

$types = array(new User, new Post, new Question, new Answer, new Comment, new Tag);

foreach($types as $type)
{
	if($type->is_valid_call())
	{
		$ret_type = $type;
		break;
	}
}

if(is_null($ret_type))
{
	echo "The given call was not recognized.";
	exit(0);
}

/**
This file will allow us to execute database queries off of Q2A's code.
*/
require './qa-include/qa-base.php';


$results = qa_db_query_raw($ret_type->get_query());

/**
This array will contain all the data objects returned from the query.
*/
$return_value = array();


/**
Go through each row of the results.
*/
while($row = mysqli_fetch_array($results, MYSQL_ASSOC))
{
	array_push($return_value, $ret_type->construct_from_row($row));
}

/**
Format array output similar to StackExchange standards.
*/
$ret = array("Items" => $return_value);

/**
This line of code gzips everything presented as output
*/
ob_start('ob_gzhandler');

/**
return JSON array
*/
exit(json_encode($ret));
?>