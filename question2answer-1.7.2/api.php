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

$ret_type = NULL;

$types = array(new quest);
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


/**
These files contain the logic of each data object.
*/
require_once "./api_classes/answers.php";
require_once "./api_classes/comments.php";
require_once "./api_classes/posts.php";
require_once "./api_classes/questions.php";
require_once "./api_classes/tags.php";
require_once "./api_classes/users.php";

/**
This file will allow us to execute database queries off of Q2A's code.
*/
require './qa-include/qa-base.php';


/**
The path must be specified; api.php holds no functionality without a path.
*/
if(!isset($_SERVER['PATH_INFO']))
{
	return_error(404, 'path', 'path_missing');
}

/**
$output_type will store the ID of the datatype that will be returned.
*/
$output_type = false;

/**
$query contains either
	A:	false, and therefore the API call is not handled by this specific
		datatype, or
	B:	a string containing the MySQL query we need to run.
*/
if($query = Question::get_query($_SERVER['PATH_INFO']))
{
	/**
	The output will be a list of question-type objects.
	*/
	$output_type = Question::ID;
}
elseif($query = Answer::get_query($_SERVER['PATH_INFO']))
{
	$output_type = Answer::ID;
}
elseif($query = Comment::get_query($_SERVER['PATH_INFO']))
{
	$output_type = Comment::ID;
}
elseif($query = Post::get_query($_SERVER['PATH_INFO']))
{
	$output_type = Post::ID;
}
elseif($query = User::get_query($_SERVER['PATH_INFO']))
{
	$output_type = User::ID;
}
elseif($query = Tag::get_query($_SERVER['PATH_INFO']))
{
	$output_type = Tag::ID;
}

/**
Return error if none of the currently implemented API callsets can handle
this API call.
*/
if(!$output_type)
{
	return_error(404, 'call', 'call_not_supported');
}


/**
The results of running the MySQL query.
*/
$results = qa_db_query_raw($query);

/**
This array will contain all the data objects returned from the query.
*/
$return_value = array();


/**
Go through each row of the results.
*/
while($row = mysqli_fetch_array($results, MYSQL_ASSOC))
{
	/**
	$tuple = a row from the result set that is properly formatted as an
	appropriate object.
	*/
	$tuple;
	
	/**
	Properly format $tuple.
	*/
	switch($output_type)
	{
		case Question::ID:
			$tuple = new Question($row);
			break;

		case Answer::ID:
			$tuple = new Answer($row);
			break;

		case Comment::ID:
			$tuple = new Comment($row);
			break;

		case Post::ID:
			$tuple = new Post($row);
			break;

		case User::ID:
			$tuple = new User($row);
			break;

		case Tag::ID:
			$tuple = new Tag($row);
			break;
	}
	
	/**
	Add $tuple to the list of objects to be returned.
	*/
	array_push($return_value, $tuple);
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