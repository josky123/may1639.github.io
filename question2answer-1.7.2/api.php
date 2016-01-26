<?php

require_once "./api_classes/util.php";
require_once "./api_classes/users.php";
require_once "./api_classes/questions.php";
require_once "./api_classes/answers.php";
require './qa-include/qa-base.php';

header("access-control-allow-origin: *");
header('Content-Type: application/json');

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


// $return_value = array("Items" => users("user"));

switch($path[0])//selects proper function to call.
{
	case 'users':
		$return_value = array("items" => users("user"));
		break;
	
	case 'questions':
		$return_value = array("items" => questions("questions"));
		break;
	
	default:
		break;
}

if(!isset($return_value))
	return_error(400, 'method', 'method_not_recognized');

/** /

User::func($path);

echo "\nFIN";

/**/
//This line of code gzips everything presented as output
ob_start('ob_gzhandler');

//return JSON array
exit(json_encode($return_value));


// mysqli_free_result($result);
?>