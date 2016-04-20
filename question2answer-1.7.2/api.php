<?php

/**
 * @author Robert Kloster <robert.kloster@yahoo.com>
 * 
 * This file contains the interface for the JavaSpecs' API.
 */

/**
 * This section is needed in order to begin formatting into gzipped JSON.
 */
header("access-control-allow-origin: *");
header('Content-Type: application/json');

/**
 * This file contains the logic of many shared/common functions.
 */
require_once "./api_classes/util.php";

/**
 * These files provide the details for the different types of API calls
 */
require_once "./api_classes/users.php";
require_once "./api_classes/posts.php";
require_once "./api_classes/questions.php";
require_once "./api_classes/answers.php";
require_once "./api_classes/comments.php";
require_once "./api_classes/tags.php";

/*
ret_type: will contain the reference object 
of the appropriate type as this API call
*/
$ret_type = NULL;

// query: will contain the MySQL query to be submitted
$query = NULL;

// types: an array of different CallableData reference objects
$types = array(new User, new Post, new Question, new Answer, new Comment, new Tag);

// Find the correct type of API call and get the query
foreach($types as $type)
{
	// The get_query function will return either NULL or the query we need
	$query = $type->get_query();
	
	if(!is_null($query))
	{
		$ret_type = $type;
		break;
	}
}



// If none of those fit the bill, throw an error
if(is_null($ret_type))
{
	CallableData::return_error("API Call Not Found Error", "The given URL path does not match any known API call.", "The URL path");
}


/**
 * This file will allow us to execute database queries off of Q2A's code.
 */
require './qa-include/qa-base.php';


// Query for results
$results = qa_db_query_raw($query);

/**
 * This array will contain all the data objects returned from the query.
 */
$return_value = array();

/**
 * Go through each row of the results.
 */
while($row = mysqli_fetch_array($results, MYSQL_ASSOC))
{
	array_push($return_value, $ret_type->construct_from_row($row));
}

/**
 * Format array output similar to StackExchange standards.
 */
$ret = array("Items" => $return_value);

/**
 * This line of code gzips everything presented as output
 */
ob_start('ob_gzhandler');

/**
 * return JSON array
 */
exit(json_encode($ret));
?>