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

/*
This function accepts 5 different arrays from the datatype being queried:
	1.	$boolean_vars
			An array containing strings of names of all valid boolean-type variables of the queried datatype,

	2.	$datetime_vars
			An array containing strings of names of all valid datetime-type variables of the queried datatype,

	3.	$integer_vars
			An array containing strings of names of all valid integer-type variables of the queried datatype,

	4.	$string_vars
			An array containing strings of names of all valid string-type variables of the queried datatype,

	5.	$variable_mapping
			An array where the keys are the names of all variables of the queried datatypes,
			and the values are the appropriate representations of those variables in MySQL.

This function reads the conditional statements from the "conditions" parameter in $_GET,
processes and checks the input for errors,
and returns a string containing all the conditions properly formatted for the MySQL query calling it.
*/
function parse_conditions($boolean_vars, $datetime_vars, $integer_vars, $string_vars, $variable_mapping)
{
	
	// Quickly check if the "conditions" parameter is set.
	if(!isset($_GET['conditions']) || empty($_GET['conditions']))
	{
		return "";
	}
	
	// Otherwise, parse all conditions and extract the
	// negate, variable, and argument values from them.
	preg_match_all("~(?<negate>-)?(?<variable>[a-zA-Z_]+):(?<argument>(?:\"[^\"]*\")|(?:[^\"\s]*))(?=(?:\s+)|(?:$))~", $_GET['conditions'], $conditions, PREG_SET_ORDER);

	$return_array = array();
	
	foreach($conditions as &$condition)
	{
		$condition = remove_numeric_indexes($condition);
		$condition['negate'] = (bool) !empty($condition['negate']);
		$condition['argument'] = trim($condition['argument'], "\"");

		//assign the proper type : (default = "string")
		$condition['type'] = "string";

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

		//Determine whether or not it's a range-type condition.
		if(preg_match("~^(?<arg>.*)(?<!\.\.)\.\.(?!\.\.)(?<arg_2>.*)$~", $condition['argument'], $argument))
		{
			$condition['arg'] = $argument['arg'];

			// if the two arguments are identical, treat it like a
			// "variable = arg"-type condition statement.
			// this can simplify some of the process.
			if($argument['arg'] === $argument['arg_2'])
			{
				$condition['operator'] = "=";
			}

			else
			{
				$condition['operator'] = "..";	// .. = range operator
				$condition['arg_2'] = $argument['arg_2'];

				// if it's not a string-type comparison, account for the "*..arg" and "arg..*" shortcuts.
				if(!($condition['type'] === "string"))
				{

					// *..arg
					if(preg_match("~^\s*\*\s*$~", $condition['arg']) && !preg_match("~^\s*\*\s*$~", $condition['arg_2']))
					{
						$condition['operator'] = "<=";
						$condition['arg'] = $condition['arg_2'];
						unset($condition['arg_2']);

					}// arg..*
					elseif(!preg_match("~^\s*\*\s*$~", $condition['arg']) && preg_match("~^\s*\*\s*$~", $condition['arg_2']))
					{
						$condition['operator'] = ">=";
						unset($condition['arg_2']);
					}
				}
			}
			
		}//Else, assume that it's a comparison-type. (<, <=, =, >=, or >)
		elseif(preg_match("~^(?<operator>[><][=]?|[>!<]?=)?(?<arg>.*)$~", $condition['argument'], $argument))
		{
			$condition['arg'] = $argument['arg'];
			
			// If there is a defined comparison operator, get it.
			if(!empty($argument['operator']))
			{
				$condition['operator'] = $argument['operator'];
			}
			else	// otherwise, assume the operator is "=".
			{
				$condition['operator'] = "=";
			}
		}
		
		//get the true values of the arguments according to type
		switch($condition['type'])
		{
			case 'boolean':
			$condition['arg_val'] = parse_bool($condition['arg']);
			if(isset($condition['arg_2']))
			{
				$condition['arg_val_2'] = parse_bool($condition['arg_2']);
			}
				break;
			
			case 'datetime':
			$condition['arg_val'] = parse_datetime($condition['arg']);
			if(isset($condition['arg_2']))
			{
				$condition['arg_val_2'] = parse_datetime($condition['arg_2']);
			}
				break;
			
			case 'integer':
			$condition['arg_val'] = parse_integer($condition['arg']);
			if(isset($condition['arg_2']))
			{
				$condition['arg_val_2'] = parse_integer($condition['arg_2']);
			}
				break;
			
			// case 'string':
			default:
			$condition['arg_val'] = $condition['arg'];
			if(isset($condition['arg_2']))
			{
				$condition['arg_val_2'] = $condition['arg_2'];
			}
				break;
		}

		//If the first argument is of an incorrect type, return error.
		if(is_null($condition['arg_val']))
		{
			return_error(400, "The \"".$condition['variable']."\" variable expects an argument of type \"".$condition['type']."\"; the argument \"".$condition['arg']."\" is not of type \"".$condition['type']."\".", "bad_parameter");
		}

		// cover some remaining edge cases with range-type conditions.
		if($condition['operator'] === "..")
		{

			// If the second argument is of an incorrect type, return error.
			if(is_null($condition['arg_val_2']))
			{
				return_error(400, "The \"".$condition['variable']."\" variable expects an argument of type \"".$condition['type']."\"; the argument \"".$condition['arg_2']."\" is not of type \"".$condition['type']."\".", "bad_parameter");
			}

			//if the two arguments are functionally equivalent, simplify
			//by turning it into a single-argument "="-type condition
			if($condition['arg_val'] === $condition['arg_val_2'])
			{
				$condition['operator'] = "=";
				unset($condition['arg_2']);
				unset($condition['arg_val_2']);

			}
		}

		// variables of type boolean can only handle conditions of type "=" or "!=",
		// as well as true..true and false..false, which should bave been
		// simplified at this point in the code.
		if(($condition['type'] === "boolean") && !(($condition['operator'] === "=") || ($condition['operator'] === "!=")))
		{
			return_error(400, "The \"".$condition['variable']."\" variable is of type \"".$condition['type']."\", and can only handle conditions of type \"=\". (Note that range conditions like \"true..true\" and \"false..false\" will be automatically converted into \"= true\" and \"= false\", respectively.)", "bad_parameter");
		}

		// satisfy the negate property, if possible, by
		// inverting all non-range operators.
		if($condition['negate'])
		{
			$condition['negate'] = (boolean) FALSE;
			switch ($condition['operator'])
			{
				case "<":
					$condition['operator'] = ">=";
					break;
				
				case "<=":
					$condition['operator'] = ">";
					break;
				
				case "=":
					$condition['operator'] = "!=";
					break;
				
				case "!=":
					$condition['operator'] = "=";
					break;
				
				case ">=":
					$condition['operator'] = "<";
					break;
				
				case ">":
					$condition['operator'] = "<=";
					break;
				
				case "..":
					// there is no simple inversion of the range operator;
					// we'll just include a  " NOT" later...
					$condition['negate'] = (boolean) TRUE;
					break;
			}
		}

		//format how the output will look, for MySQL's sake
		switch($condition['type'])
		{
			// normally, PHP prints out raw booleans as 0 or 1; need to fix that for MySQL
			case 'boolean':
			$condition['arg'] = ($condition['arg_val']) ? "TRUE" : "FALSE";
			if(isset($condition['arg_val_2']))
			{
				$condition['arg_2'] = ($condition['arg_val_2']) ? "TRUE" : "FALSE";
			}
				break;
			
			// add quotes around the datetime
			case 'datetime':
			$condition['arg'] = "\"".$condition['arg_val']."\"";
			if(isset($condition['arg_val_2']))
			{
				$condition['arg_2'] = "\"".$condition['arg_val_2']."\"";
			}
				break;
			
			case 'integer':
			$condition['arg'] = "".$condition['arg_val']."";
			if(isset($condition['arg_val_2']))
			{
				$condition['arg_2'] = "".$condition['arg_val_2']."";
			}
				break;
			
			// case 'string': need to add quotes around the string
			default:
			$condition['arg'] = "\"".$condition['arg_val']."\"";
			if(isset($condition['arg_val_2']))
			{
				$condition['arg_2'] = "\"".$condition['arg_val_2']."\"";
			}
				break;
		}

		// Finally, begin constructing the MySQL query for these conditions.
		if($condition['operator'] === "..")
		{
			$negate_string = ($condition['negate']) ? " NOT" : "";
			$condition['MySQL_condition'] = "(".$variable_mapping[$condition['variable']].$negate_string." BETWEEN ".$condition['arg']." AND ".$condition['arg_2'].")";
		}
		else
		{
			$condition['MySQL_condition'] = "(".$variable_mapping[$condition['variable']]." ".$condition['operator']." ".$condition['arg'].")";
		}

		array_push($return_array, $condition['MySQL_condition']);
	}

	return implode(" AND ", $return_array);
	
}




/**/

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

/**/

function parse_integer($argument)
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

/**/

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


/*
	function __construct($new_negate, $new_variable, $new_type, $argument)
	{
		
		$argument = trim($argument, "\"");
		$argument = trim($argument);
		
		//Determine whether or not it's a range-type.
		if(preg_match("~^(?<arg>\S+\s*\S*)(?<!\.\.)\s*\.\.\s*(?!\.\.)(?<arg_2>\S*\s*\S+)$~", $argument, $match))
		{
			$this->op = "..";
			$this->arg = $match['arg'];
			$this->arg_2 = $match['arg_2'];
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