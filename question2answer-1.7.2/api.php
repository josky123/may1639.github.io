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


/** /
var_dump($ret);
exit();
/** /








$path = explode('/', ltrim($_SERVER['PATH_INFO'], "/"));



// $query = User::get_query($ids, $id_type);

// echo $query;

// echo "\n\n";

// $result = qa_db_query_raw($query);
// $result = qa_db_query_raw("SELECT * FROM `qa_users`");

// $prime = mysqli_fetch_array($result, MYSQLI_ASSOC);
// echo "\n\n\n";
// var_dump($prime);

//echo mysqli_data_seek($result, 0);

function users($id_type='user')//what should happen if the path starts with 'users'.
{
	global $path;

	if (count($path) > 1)
	{
		switch($path[1])
		{
			case 'moderators':
				
				break;
			
			default:
				$ids = $path[1];
				process_ids($ids);
				break;
		}
	}

	if(isset($ids))
	{
		$ids = explode(";", $ids);
	}

	$query = User::get_query($ids, $id_type);
	$query = paginate_query($query, mysqli_num_rows(qa_db_query_raw($query)));

	$results = qa_db_query_raw($query);
	
	$retval = array();

	while($row = mysqli_fetch_array($results, MYSQL_ASSOC))
	{
		$temp = new User($row);		
		array_push($retval, new User($row));
	}

	return $retval;
}

function answers($id_type='answer')
{
	global $path;

	if (count($path) > 1)
	{
		switch($path[1])
		{
			// case 'moderators':
				
			// 	break;
			
			default:
				$ids = $path[1];
				process_ids($ids);
				break;
		}
	}

	// $results = $db->query("SELECT p.* FROM mybb_posts p, mybb_users u WHERE u.username=\"adcoats\" && p.uid = u.uid");
	
	if(isset($ids))
	{
		$ids = explode(";", $ids);
	}

	$query = Answer::get_query($ids, $id_type);
	// echo "string";
	// return $query;

	$query = paginate_query($query, mysqli_num_rows(qa_db_query_raw($query)));

	$results = qa_db_query_raw($query);
	
	$retval = array();

	while($row = mysqli_fetch_array($results, MYSQL_ASSOC))
	{		
		array_push($retval, new Answer($row));
	}

	return $retval;
}

function comments($id_type='comment')
{
	global $path;

	if (count($path) > 1)
	{
		switch($path[1])
		{
			// case 'moderators':
				
			// 	break;
			
			default:
				$ids = $path[1];
				process_ids($ids);
				break;
		}
	}

	if(isset($ids))
	{
		$ids = explode(";", $ids);
	}

	$query = Comment::get_query($ids, $id_type);
	// echo "string";
	// return $query;
	$query = paginate_query($query, mysqli_num_rows(qa_db_query_raw($query)));

	$results = qa_db_query_raw($query);
	
	$retval = array();

	while($row = mysqli_fetch_array($results, MYSQL_ASSOC))
	{		
		array_push($retval, new Comment($row));
	}

	return $retval;
}

function posts($id_type='post')
{
	global $path;

	if (count($path) > 1)
	{
		switch($path[1])
		{
			// case 'moderators':
				
			// 	break;
			
			default:
				$ids = $path[1];
				process_ids($ids);
				break;
		}
	}

	if(isset($ids))
	{
		$ids = explode(";", $ids);
	}

	$query = Post::get_query($ids, $id_type);
	// echo "string";
	// return $query;
	$query = paginate_query($query, mysqli_num_rows(qa_db_query_raw($query)));

	$results = qa_db_query_raw($query);
	
	$retval = array();

	while($row = mysqli_fetch_array($results, MYSQL_ASSOC))
	{		
		array_push($retval, new Post($row));
	}

	return $retval;
}

function questions($id_type='question')
{
	global $path;

	if (count($path) > 1)
	{
		switch($path[1])
		{
			// case 'moderators':
				
			// 	break;
			
			default:
				$ids = $path[1];
				process_ids($ids);
				break;
		}
	}

	// $results = $db->query("SELECT p.* FROM mybb_posts p, mybb_users u WHERE u.username=\"adcoats\" && p.uid = u.uid");
	
	if(isset($ids))
	{
		$ids = explode(";", $ids);
	}
	
	$query = Question::get_query($ids, $id_type);

	$query = paginate_query($query, mysqli_num_rows(qa_db_query_raw($query)));

	$results = qa_db_query_raw($query);
	
	$retval = array();

	while($row = mysqli_fetch_array($results, MYSQL_ASSOC))
	{		
		array_push($retval, new Question($row));
	}

	return $retval;
}

function tags($id_type='tag')//what should happen if the path starts with 'users'.
{
	global $path;

	if (count($path) > 1)
	{
		switch($path[1])
		{
			case 'moderators':
				
				break;
			
			default:
				$ids = $path[1];
				process_ids($ids);
				break;
		}
	}

	if(isset($ids))
	{
		$ids = explode(";", $ids);
	}

	$query = Tag::get_query($ids, $id_type);
	$query = paginate_query($query, mysqli_num_rows(qa_db_query_raw($query)));

	$results = qa_db_query_raw($query);
	
	$retval = array();

	while($row = mysqli_fetch_array($results, MYSQL_ASSOC))
	{
		$temp = new Tag($row);		
		array_push($retval, new Tag($row));
	}

	return $retval;
}


// $return_value = array("Items" => users("user"));

switch($path[0])//selects proper function to call.
{
	case 'users':
		$return_value = array("items" => users("user"));
		break;

	case 'answers':
		$return_value = array("items" => answers("answer"));
		break;

	case 'comments':
		$return_value = array("items" => comments("comment"));
		break;

	case 'posts':
		$return_value = array("items" => posts("post"));
		break;

	case 'questions':
		$return_value = array("items" => questions("question"));
		break;

	case 'tags':
		$return_value = array("items" => tags("tag"));
		break;
	

	default:
		$return_value = 'e';
		
		$query = Question::main_query("8", "Answer");
		
		$return_value = $query;
		
		$return_value = array();

		$results = qa_db_query_raw($query);

		while($row = mysqli_fetch_array($results, MYSQL_ASSOC))
		{
			array_push($return_value, $row);
		}
		break;
}

if(!isset($return_value))
	return_error(400, 'method', 'method_not_recognized');


//This line of code gzips everything presented as output
ob_start('ob_gzhandler');

//return JSON array
exit(json_encode($return_value));
/**/
?>