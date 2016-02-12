<?php
define("IN_MYBB", 1);
header("access-control-allow-origin: *");
header('Content-Type: application/json');//JSON-formatting

$ID_TYPES = array('user' => 'userid', 'answer' => 'postid', 'question' => 'postid', 'comment' => 'postid', 'post' => 'postid', 'tag' => 'tagid');

function paginate_query($query, $n)
{
	$pagesize = 30;
	if(isset($_GET['pagesize']))
	{
		if(false === filter_input(INPUT_GET, 'pagesize', FILTER_VALIDATE_INT))
			return_error(400, 'pagesize', 'bad_parameter');
		
		$pagesize = max(0, min(100, $_GET["pagesize"]));
	}
	
	if(($pagesize < 1) || ($n < 1))
		return array();

	$pagesize = min($pagesize, $n);
	
	$hard_limit = $n / $pagesize;

	if(0 < $n % $pagesize)
		$hard_limit++;

	$page = 1;
	
	if(isset($_GET["page"]))
	{
		if(false === filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT))
			return_error(400, 'page', 'bad_parameter');
		
		$page = max(1, min($hard_limit, $_GET["page"]));
	}

	// echo "\n$query\n";
	// return $query;
	
	$query .= " LIMIT ".$pagesize." OFFSET ".($page - 1) * $pagesize;
	return $query;
}

function return_error($id, $message, $name)
{
	$return_value = array('error_id' => $id, 'error_message' => $message, 'error_name' => $name);
	ob_start('ob_gzhandler');
	exit(json_encode($return_value));
}

/**/

function process_order()
{
	if(isset($_GET["order"]))
	{
		if(!in_array($_GET["order"], array("asc", "desc")))
			return_error(400, 'order', 'bad_parameter');
		return $_GET["order"];
	}
	return "desc";
}

/**/

function process_sort($mapping)
{
	if(isset($_GET["sort"]))
	{
		if(!in_array($_GET["sort"], $mapping))
			return_error(400, 'sort', 'bad_parameter');
		return $_GET["sort"];
	}
	return $mapping[0];
}

/**/

function process_date($date)
{
	/**/
	if((false === filter_input(INPUT_GET, $date, FILTER_VALIDATE_INT)) || $_GET[$date] < 0)
		return_error(400, $date, 'bad_parameter');
	/**/
	return "FROM_UNIXTIME(".$_GET[$date].")";
}

/**/

function process_min_max($sort, $min_max)
{
	if($sort == "name")
	{
		if(!is_string($_GET[$min_max]))
			return_error(400, $min_max, 'bad_parameter');
	}
	else if(false === filter_input(INPUT_GET, $min_max, FILTER_VALIDATE_INT))
			return_error(400, $min_max, 'bad_parameter');
	return $_GET[$min_max];
}

/**/

function process_inname()
{
	if(!is_string($_GET["inname"]))
		return_error(400, 'inname', 'bad_parameter');
	return $_GET["inname"];
}

/**/

function process_ids($ids)
{
	if(!preg_match("/^[0-9]+(;[0-9]+)*$/", $ids))
		return_error(404, 'no method found with this name', 'no_method');
}

/**/




// function func($path)
// {
// 	echo "\n";
// 	var_dump($_SERVER);
// 	echo "\n";
// 	var_dump($_GET);
// 	echo "\n";
// 	var_dump($path);
// 	echo "\n";
// }

?>