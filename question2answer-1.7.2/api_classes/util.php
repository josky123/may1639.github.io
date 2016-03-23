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
	
	preg_match_all($regex, $conditions, $matches, PREG_SET_ORDER);
	
	foreach ($matches as &$qualifier)
	{
		$qualifier = remove_numeric_indexes($qualifier);

		//negate should contain a boolean for whether or not we should negate the condition.
		$qualifier['negate'] = (bool) !empty($qualifier['negate']);
		
		//gets rid of the clinging quote marks.
		$qualifier['args'] = trim($qualifier['args'], "\"");

		$args = remove_numeric_indexes($args);
		
		$qualifier['args'] = $args;
	}

	return $match;
}

/**/
function parse_conditions($boolean_vars, $datetime_vars, $integer_vars, $string_vars, $variable_mapping)
{
	$regex = "~(?<negate>-)?(?<variable>[a-zA-Z_]+):(?<argument>(?:\"[^\"]*\")|(?:[^\"\s]*))(?=(?:\s+)|(?:$))~";
	$conditions_string = $_GET['conditions'];

	preg_match_all($regex, $conditions_string, $conditions, PREG_SET_ORDER);

	foreach($conditions as &$condition)
	{
		$condition = remove_numeric_indexes($condition);
		$condition['negate'] = (bool) !empty($condition['negate']);
		$condition['argument'] = trim($condition['argument'], "\"");

		//assign the proper type :

		//integer-type condition
		if(in_array($condition['variable'], $boolean_vars))
		{
			$condition['type'] = "boolean";
		
		
		}//datetime-type condition
		elseif(in_array($condition['variable'], $datetime_vars))
		{
			$condition['type'] = "datetime";
		
		
		}//integer-type condition
		elseif(in_array($condition['variable'], $integer_vars))
		{
			$condition['type'] = "integer";
		

		}//string-type condition
		elseif(in_array($condition['variable'], $string_vars))
		{
			$condition['type'] = "string";
		

		}//Not a valid parameter to search by.
		else
		{
			return_error(400, "The input parameter \"".$condition['variable']."\" is not valid for this type of search.", "bad_parameter");
		}

		//get the proper value to use in the MySQL query.
		$condition['variable'] = $variable_mapping[$condition['variable']];
	}

		/**
		START WORK HERE!!!
		START WORK HERE!!!
		START WORK HERE!!!
		START WORK HERE!!!
		START WORK HERE!!!
		START WORK HERE!!!
		START WORK HERE!!!
		START WORK HERE!!!
		START WORK HERE!!!
		*/

	ob_start('ob_gzhandler');
	exit(json_encode($conditions));

}

/** /

function parse_bool($argument)
{
	if(is_null($argument))
		return NULL;

	if(preg_match("~^\s*true\s*$~i", $argument))
	{
		return (bool) true;
	}
	
	if(preg_match("~^\s*false\s*$~i", $argument))
	{
		return (bool) false;
	}
	
	return NULL;
}

/** /

function parse_int($argument)
{
	if(is_null($argument))
		return NULL;
		
	if(preg_match("~^\s*(?:-)?\s*([0-9]+)\s*$~", $argument))
	{
		$argument = preg_replace("~\s+~", "", $argument);
		$argument = (int) intval($argument);
		return $argument;
	}
	return NULL;
}

/** /

function parse_datetime($argument)
{
	if(is_null($argument))
		return NULL;

	if(preg_match("~^\s*(?<year>\d{4})-(?<month>\d{2})-(?<day>\d{2})(?<time>\s+.*)?\s*$~", $argument, $match))
	{
		$ret = $match['year']."-".$match['month']."-".$match['day'];

		if(!empty($match['time']))
		{
			$time = $match['time'];
			if(preg_match("~^\s*(?<hour>(?:[0-1]\d)|(?:2[0-3])):(?<minute>[0-5]\d):(?<second>[0-5]\d)\s*$~", $time, $time_match))
			{
				$ret .=  " ".$time_match['hour'].":".$time_match['minute'].":".$time_match['second'];
			}
			else
			{
				return NULL;
			}
		}
		if(!checkdate($match['month'], $match['day'], $match['year']))
		{
			return_error(400, "The provided date \"".$match['year']."-".$match['month']."-".$match['day']."\" is not a valid date.", "bad_parameter");
		}
		return $ret;
	}

	return NULL;
}

/**/

/**

IMPORTANT: GET THIS DONE BEFORE CONSTRUCTING!
preg_match("~(?<negate>-)?(?<variable>[a-zA-Z_]+):(?<argument>(?:\"[^\"]*\")|(?:[^\"\s]*))(?=(?:\s+)|(?:$))~", $qualifier, $match);

$new_type = Question::get_proper_variable_type($match['variable']);

*/

/*
	function __construct($new_negate, $new_variable, $new_type, $argument)
	{
		
		$argument = trim($argument, "\"");
		$argument = trim($argument);
		
		//Determine whether or not it's a range-type.
		if(preg_match("~^(?<arg>\S+\s*\S*)(?<!\.\.)\s*\.\.\s*(?!\.\.)(?<arg2>\S*\s*\S+)$~", $argument, $match))
		{
			$this->op = "..";
			$this->arg = $match['arg'];
			$this->arg2 = $match['arg2'];
		}//Else, determine whether or not it's a comparison-type.
		elseif(preg_match("~^(?<op>[><](?:=)?)?\s*(?<arg>\S+\s*\S*)$~", $argument, $match))
		{
			$this->op = $match['op'];
			$this->arg = $match['arg'];

		}//else, assume it's an equals-type.
		else
		{
			$this->op = "=";
			$this->arg = $argument;
		}
	}
*/



?>