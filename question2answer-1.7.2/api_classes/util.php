<?php
header("access-control-allow-origin: *");
header('Content-Type: application/json');

function paginate_query()
{
	/**
	The default pagesize (by StackExchange standards) is 30.
	*/
	$pagesize = 30;
	/**
	Process custom-specified pagesize, if defined.
	*/
	if(isset($_GET['pagesize']))
	{
		/**
		Determine pagesize validity
		*/
		if(!is_numeric($_GET['pagesize']))
			return_error(400, 'pagesize', 'bad_parameter');
		
		/**
		1 <= pagesize <= 100
		*/
		$pagesize = max(1, min(100, $_GET["pagesize"]));
	}
	/**
	The default page (by StackExchange standards) is 1.
	*/
	$page = 1;

	/**
	Process custom-specified page, if defined.
	*/
	if(isset($_GET["page"]))
	{
		if(!is_numeric($_GET['page']))
			return_error(400, 'page', 'bad_parameter');
		$page = $_GET["page"];
	}

	/**
	Augment query with limit and offset operators.
	*/
	return " LIMIT ".$pagesize." OFFSET ".($page - 1) * $pagesize;
}

function return_error($id, $message, $name)
{
	$return_value = array('error_id' => $id, 'error_message' => $message, 'error_name' => $name);
	ob_start('ob_gzhandler');
	exit(json_encode($return_value));
}


/**
This function takes in a string of semicolon-delimited numeric IDs,
sorts them, removes duplicates, and formats them to be compatible
with MySQL.
*/
function format_numeric_IDs($IDs)
{
	$IDs = preg_replace("/\s+/", "", $IDs);
	$IDs = preg_replace("/;+/", ";", $IDs);
	$IDs = trim($IDs, ";");
	$IDs = explode(";", $IDs);
	sort($IDs);
	$IDs = array_unique($IDs);
	$IDs = implode(",", $IDs);
	return $IDs;
}

/**
This function takes in a string of semicolon-delimited alphabetic IDs,
sorts them, removes duplicates, and formats them to be compatible
with MySQL.
*/
function format_alphabetic_IDs($IDs)
{
	$IDs = preg_replace("/\s+/", " ", $IDs);
	$IDs = preg_replace("/\s*;+\s*/", ";", $IDs);
	$IDs = trim($IDs, ";");
	$IDs = explode(";", $IDs);
	sort($IDs);
	$IDs = array_unique($IDs);
	$IDs = "'".implode("','", $IDs)."'";
	return $IDs;
}

/**
This function takes in an array and removes all numeric indexed elements from it.
*/
function remove_numeric_indexes($array)
{
	foreach ($array as $key => $value)
		if (is_int($key) === true)
			unset($array[$key]);
	return $array;
}

function qualifiers()
{
	$conditions = $_GET['conditions'];
	preg_match("|<[^>]+>(.*)</[^>]+>|U", $conditions);
	$conditions = $_GET['conditions'];
	
	//matches all valid variable qualifiers.
	$regex = "~(?<negate>-)?(?<variable>[a-zA-Z_]+):(?<args>(?:\"[^\"]*\")|(?:[^\"\s]*))(?=(?:\s+)|(?:$))~";
	
	preg_match_all($regex, $conditions, $match, PREG_SET_ORDER);
	
	foreach ($match as &$qualifier)
	{
		$qualifier = remove_numeric_indexes($qualifier);

		//negate should contain a boolean for whether or not we should negate the condition.
		$qualifier['negate'] = (bool) !empty($qualifier['negate']);
		
		//gets rid of the clinging quote marks.
		$qualifier['args'] = trim($qualifier['args'], "\"");


		//BEGIN SEARCH FOR TYPE STUFF.


		//This is used to determine whether or not it's a range-type.
		preg_match("~\s*(?<arg1>\S+\s*\S*)(?<!\.\.)\s*\.\.\s*(?!\.\.)(?<arg2>\S*\s*\S+)\s*~", $qualifier['args'], $args);


		$args = remove_numeric_indexes($args);
		
		$qualifier['args'] = $args;
	}

	return $match;
}


?>