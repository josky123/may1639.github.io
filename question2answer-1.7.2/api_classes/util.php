<?php
/**
 * @author Robert Kloster <robert.kloster@yahoo.com>
 * @access public
 * @abstract
 * 
 * This class represents a generic object that can be queried 
 * for with an API call.  This class also contains most, if 
 * not all of the logic for processing an API call.  This 
 * makes it a simple matter to create new API calls for new 
 * callable objects; simply create a class that properly 
 * extends CallableData.
 */
abstract class CallableData
{
	/**
	 * Returns a boolean value representing whether or not the 
	 * URL represents an API call for this type of object
	 * 
	 * @access protected
	 * @abstract
	 * @return boolean True if URL is making a call for 
	 * this type of CallableData, false otherwise
	 */
	abstract protected function is_valid_call();

	/**
	 * Creates and returns a CallableData object of the same 
	 * type as this one
	 * 
	 * @access public
	 * @abstract
	 * @param string[] $row A row returned from a query 
	 * result table for this type of CallableData
	 * @return CallableData A CallableData object of 
	 * the same type as this one
	 */
	abstract public function construct_from_row($row);

	/**
	 * Returns an array containing the names of all variables 
	 * of this type of CallableData that are boolean values
	 * 
	 * @access protected
	 * @abstract
	 * @return string[] Array of boolean variable names
	 */
	abstract protected function valid_bool_vars();

	/**
	 * Returns an array containing the names of all variables 
	 * of this type of CallableData that are integer values
	 * 
	 * @access protected
	 * @abstract
	 * @return string[] Array of integer variable names
	 */
	abstract protected function valid_int_vars();

	/**
	 * Returns an array containing the names of all variables 
	 * of this type of CallableData that are date values
	 * 
	 * @access protected
	 * @abstract
	 * @return string[] Array of date variable names
	 */
	abstract protected function valid_date_vars();

	/**
	 * Returns an array containing the names of all variables 
	 * of this type of CallableData that are string values
	 * 
	 * @access protected
	 * @abstract
	 * @return string[] Array of string variable names
	 */
	abstract protected function valid_string_vars();

	/**
	 * Returns an array where the keys are the names of all 
	 * variables of this type of CallableData, and the values 
	 * are their representative MySQL varaible names 
	 * 
	 * @access protected
	 * @abstract
	 * @return string[] Array of variable name to MySQL name 
	 * mappings
	 */
	abstract protected function valid_var_mappings();

	/**
	 * Returns the base MySQL query for CallableData objects
	 * of this type
	 * 
	 * @access protected
	 * @abstract
	 * @return string Base MySQL query for this type of 
	 * CallableData
	 */
	abstract protected function get_base_call();
	
	/**
	 * Combines multiple MySQL conditional statements 
	 * according to the specified logic, while attempting to simplify the overall statement
	 * 
	 * @access protected
	 * @static
	 * @param string[] $arr The conditional statements to 
	 * combine together
	 * @param string $logic The logical operator used to 
	 * combine all conditional statements together with; 
	 * defaults to "AND", but "OR" is an acceptable 
	 * alternative
	 * @return string Combined conditional statements
	 */
	static protected function condition_combine($arr, $logic="AND")
	{
		// Format logic input
		$logic = trim(strtoupper($logic));

		/*
		This array will contain the keys of the elements 
		in $arr that we will want to remove
		*/
		$to_remove = array();

		// Quickly remove exact duplicates
		$arr = array_unique($arr);
		
		/*
		Go through each element in $arr, locate all 
		"TRUE"'s and "FALSE"'s
		*/
		foreach ($arr as $key => $value)
		{
			/*
			Format $value to fit switch statement conditions
			*/
			$value = strtoupper($value);
			$value = preg_replace("~\s+~", " ", $value);
			$value = trim($value);
			
			// Behavior is dependent on logic
			switch ($logic)
			{
				case 'AND':
					/*
					X AND TRUE = X
					X AND FALSE = FALSE
					*/
					switch($value)
					{
						case 'NOT FALSE':
						case 'TRUE':
							array_push($to_remove, $key);
							break;
					
						case 'NOT TRUE':
						case 'FALSE':
							return "FALSE";
							break;

						default:
							break;
					}
					break;
				
				case 'OR':
					/*
					X OR TRUE = TRUE
					X OR FALSE = X
					*/
					switch($value)
					{
						case 'NOT FALSE':
						case 'TRUE':
							return "TRUE";
							break;
					
						case 'NOT TRUE':
						case 'FALSE':
							array_push($to_remove, $key);
							break;

						default:
							break;
					}
					break;
			}
		}

		
		// Remove all unnecessary elements from $arr
		foreach ($to_remove as $key => $value)
		{
			unset($arr[$value]);
		}
		
		/*
		If we removed all conditions, then the output depends on logic
		*/
		if(empty($arr))
		{
			switch($logic)
			{
				// TRUE AND TRUE AND ... AND TRUE = TRUE
				case 'AND':
					return "TRUE";
					break;
				
				// FALSE OR FALSE OR ... OR FALSE = FALSE
				case 'OR':
					return "FALSE";
					break;
			}
		}

		/*
		Combine all statements together with the specified logic
		*/
		$ret = implode(" ".$logic." ", $arr);

		/*
		Just to be on the safe side, add parenthesis for any logical statement which is composed of more than one sub-statement.
		*/
		if(1 < count($arr))
		{
			return "(".$ret.")";
		}
		else
		{
			return $ret;
		}
	}

	/**
	 * Returns an error object with details of the error as a JSON object
	 * 
	 * @access public
	 * @static
	 * @param string $name Brief description of the error
	 * @param string $message More detailed description of 
	 * the error
	 * @param mixed $problem The variable that is 
	 * contributing to the problem
	 */
	static public function return_error($name, $message, $problem)
	{
		$ERROR = array('Error Name' => $name, 'Error Message' => $message, 'Error Problem' => $problem);
		ob_start('ob_gzhandler');
		exit(json_encode($ERROR));
	}

	/**
	 * Searches the 'conditions' parameter in $_GET for all 
	 * conditions involving the given boolean variable name
	 * 
	 * @access protected
	 * @param string $bool_var_name The name of the boolean 
	 * variable to be searched for
	 * @return string String containing all MySQL conditional 
	 * statements involving the specified boolean variable
	 */
	protected function parse_bool_condition($bool_var_name)
	{
		/*
		Regex used for searching for a this specific boolean 
		variable name
		*/
		$regex = "~(?<!\S)(?<negate>-)?".$bool_var_name.":(?<argument>(?i:true|false)|(?:\"\s*(?i:true|false)\s*\"))(?!\S)~";
		
		// Get all conditions with this boolean variable name
		preg_match_all($regex, $_GET['conditions'], $bool_matches, PREG_SET_ORDER);

		// Remove the discovered conditions out of 'conditions' in GET
		$_GET['conditions'] = preg_replace($regex, "", $_GET['conditions']);

		/*
		Since there aren't many different ways to form 
		conditional statements about booleans, let's just get 
		the value the variable should be and store it here
		*/
		$bool_value = NULL;

		/*If we have a match, search through all matching
		conditions for this variable
		*/
		if(!empty($bool_matches))
		{
			foreach($bool_matches as $key => $match)
			{
				// argument: the main body of the condition
				$match['argument'] = trim($match['argument'], "\"");
				$match['argument'] = trim($match['argument']);
				$match['argument'] = strtolower($match['argument']);

				/*
				Translate string value of argument to boolean
				*/
				$match['argument'] = (boolean) (($match['argument'] != 'false') && ($match['argument'] == 'true'));
				
				/*
				negate: whether or not to include results 
				that match the condition
				*/
				if(!empty($match['negate']))
				{
					$match['argument'] = !$match['argument'];
				}


				if(is_null($bool_value))
				{
					$bool_value = $match['argument'];
				}
				elseif($bool_value != $match['argument'])
				{
					// (X IS TRUE) AND (X IS FALSE) = FALSE
					return "FALSE";
				}
			}
		}

		/*
		'No conditions detected' means 'return "TRUE" for
		this variable'
		*/
		if(is_null($bool_value))
		{
			return "TRUE";
		}
		else
		{
			$mapping = $this->valid_var_mappings();

			return $mapping[$bool_var_name]." = ".($bool_value ? "TRUE" : "FALSE");
		}
	}

	/**
	 * Creates a MySQL conditional statement from all input 
	 * conditions that involve boolean variables.
	 * 
	 * @access protected
	 * @return string A MySQL conditional statement from all 
	 * input conditions that involve boolean variables.
	 */
	protected function get_boolean_conditions()
	{
		$valid_bool_vars = $this->valid_bool_vars();
		$arr = array();
		if(!empty($valid_bool_vars))
		{
			foreach($valid_bool_vars as $key => $var_name)
			{
				array_push($arr, $this->parse_bool_condition($var_name));
			}
		}
		return $this->condition_combine($arr);
	}

	/**
	 * Searches the 'conditions' parameter in $_GET for all 
	 * conditions involving the given integer variable name
	 * 
	 * @access protected
	 * @param string $int_var_name The name of the integer 
	 * variable to be searched for
	 * @return string String containing all MySQL conditional 
	 * statements involving the specified integer variable
	 */
	protected function parse_int_condition($int_var_name)
	{
		/*
		Regex used for searching for a this specific integer 
		variable name
		*/
		$regex = "~(?<!\S)(?<negate>-)?".$int_var_name.":(?<argument>[^\s\"]+|(?:\"[^\"]+\"))(?!\S)~";
		
		// Get all conditions with this integer variable name
		preg_match_all($regex, $_GET['conditions'], $int_matches, PREG_SET_ORDER);
		
		// Remove the discovered conditions out of 'conditions' in GET
		$_GET['conditions'] = preg_replace($regex, "", $_GET['conditions']);

		// If we have any matches, go on...
		if(!empty($int_matches))
		{
			/*
			includes: an array of all integers that the 
			variable may be equal to
			*/
			$includes = array();

			/*
			excludes: an array of all integers that the 
			variable may not be equal to
			*/
			$excludes = array();

			/*
			comparisons: an array of all logical statements 
			involving <, >, <=, >=, or ranges
			*/
			$comparisons = array();
			

			// Go through all matches
			foreach($int_matches as $key => &$match)
			{
				/*
				negate: a boolean representing whether or not 
				the returned results should satisfy this 
				condition
				*/
				$match['negate'] = (boolean) !empty($match['negate']);

				// argument: the main body of the condition
				$match['argument'] = trim($match['argument'], "\"");
				$match['argument'] = trim($match['argument']);

				// check to see if the argument is for a range-type condition
				if(preg_match("~^\s*(?<arg>-?[0-9]+)\s*\.\.\s*(?<arg_2>-?[0-9]+)\s*$~", $match['argument'], $arg_match))
				{
					// operator: represents the type of condition
					$arg_match['operator'] = "..";

					// arg: the first (and often only) argument of the condition
					$arg_match['arg'] = intval($arg_match['arg']);

					// arg_2: the second argument of a range condition
					$arg_match['arg_2'] = intval($arg_match['arg_2']);
				}
				elseif(preg_match("~^\s*(?<operator>(?:[>!<]?=)|(?:[><]?=?))\s*(?<arg>-?[0-9]+)\s*$~", $match['argument'], $arg_match))
				{
					$arg_match['arg'] = intval($arg_match['arg']);
					
					// If no operator is found, assume operator is =
					if(empty($arg_match['operator']))
					{
						$arg_match['operator'] = "=";
					}
				}
				
				$match['operator'] = $arg_match['operator'];
				$match['arg'] = $arg_match['arg'];
				if(isset($arg_match['arg_2']))
				{
					$match['arg_2'] = $arg_match['arg_2'];
				}

				/*
				Most conditions can be changed to an equivalent negated condition 
				just by changing the operator
				*/
				if($match['negate'])
				{
					// Assume we can change the operator to make a negated condition
					$match['negate'] = false;
					
					switch($match['operator'])
					{
						case '=':
							$match['operator'] = "!=";
							break;
						
						case '!=':
							$match['operator'] = "=";
							break;
						
						case '<':
							$match['operator'] = ">=";
							break;
						
						case '>=':
							$match['operator'] = "<";
							break;
						
						case '>':
							$match['operator'] = "<=";
							break;
						
						case '<=':
							$match['operator'] = ">";
							break;

						/*
						There is no easy way to change a range into a negated range, 
						so just change negate back to true and handle it later
						*/
						case '..':
							$match['negate'] = true;
							break;
					}
				}

				// Get the variable name to MySQL name mapping array
				$mapping = $this->valid_var_mappings();

				// Process the condition based on the operator.
				switch($match['operator'])
				{
					case '=':
						array_push($includes, $match['arg']);
						break;
					
					case '!=':
						array_push($excludes, $match['arg']);
						break;
					
					case '<':
					case '>=':
					case '>':
					case '<=':
						array_push($comparisons, $mapping[$int_var_name]." ".$match['operator']." ".$match['arg']);
						break;

					case '..':
						if($match['negate'])
						{
							array_push($comparisons, $mapping[$int_var_name]." NOT BETWEEN ".$match['arg']." AND ".$match['arg_2']);
						}
						else
						{
							array_push($comparisons, $mapping[$int_var_name]." BETWEEN ".$match['arg']." AND ".$match['arg_2']);
						}
						break;
				}
			}

			// Remove duplicate values in includes list
			$includes = array_unique($includes);
			if(count($includes) > 0)
			{
				/*
				if there's more than one element, then 
				add condition "X IN (elements)" to $conditions
				*/
				if(count($includes) > 1)
				{
					array_push($comparisons, $mapping[$int_var_name]." IN (".implode(", ", $includes).")");
				}
				else
				{
					/*
					prepare to get first (and only) element 
					of array
					*/
					reset($includes);

					/*
					Add condition "X = element" to $conditions
					*/
					array_push($comparisons, $mapping[$int_var_name]." = ".current($includes));
				}
			}

			// Remove duplicate values in excludes list
			$excludes = array_unique($excludes);
			if(count($excludes) > 0)
			{
				/*
				if there's more than one element, then 
				add condition "X NOT IN (elements)" to $conditions
				*/
				if(count($excludes) > 1)
				{
					array_push($comparisons, $mapping[$int_var_name]." NOT IN (".implode(", ", $excludes).")");
				}
				else
				{
					/*
					prepare to get first (and only) element 
					of array
					*/
					reset($excludes);

					/*
					Add condition "X != element" to $conditions
					*/
					array_push($comparisons, $mapping[$int_var_name]." != ".current($excludes));
				}
			}

			// Remove duplicate values in comparisons list
			$comparisons = array_unique($comparisons);
			if(count($comparisons) > 0)
			{
				// AND all conditions together
				$comparisons = $this->condition_combine($comparisons);
			}
			else
			{
				$comparisons = "TRUE";
			}

			return $comparisons;
		}
		else
		{
			return "TRUE";
		}
	}

	/**
	 * Creates a MySQL conditional statement from all input 
	 * conditions that involve integer variables.
	 * 
	 * @access protected
	 * @return string A MySQL conditional statement from all 
	 * input conditions that involve integer variables.
	 */
	protected function get_integer_conditions()
	{

		$valid_int_vars = $this->valid_int_vars();
		$arr = array();
		if(!empty($valid_int_vars))
		{
			foreach($valid_int_vars as $key => $var_name)
			{
				array_push($arr, $this->parse_int_condition($var_name));
			}
		}
		return $this->condition_combine($arr);
	}

	/**
	 * Parses a given date and determines whether or not it 
	 * is valid; if it is valid, return the date in proper format
	 * 
	 * @access protected
	 * @static
	 * @param string $date The date to be checked
	 * @return mixed If the date is valid, return the formatted 
	 * date as an array of date components; otherwise, return 
	 * boolean value FALSE.
	 */
	static protected function is_valid_date($date)
	{
		// Check if the $date has valid date syntax
		if(!preg_match("~^\s*(?<year>\d{4})-(?<month>\d{2})-(?<day>\d{2})(?:\s+(?<hour>\d{2}):(?<minute>\d{2}):(?<second>\d{2}))?\s*$~", $date, $match))
		{
			return false;
		}

		// Convert to integer values
		$match['year'] = intval($match['year']);
		$match['month'] = intval($match['month']);
		$match['day'] = intval($match['day']);

		// Check calendar to verify that this day really exists
		if(!checkdate($match['month'], $match['day'], $match['year']))
		{
			return false;
		}

		// If time values were specified, process them
		if(!empty($match['hour']))
		{
			$match['hour'] = intval($match['hour']);
			$match['minute'] = intval($match['minute']);
			$match['second'] = intval($match['second']);

			// hour: integer value between 0 and 23 (inclusive)
			if((0 <= $match['hour']) && ($match['hour'] <= 23))
			{
				// minute: integer value between 0 and 59 (inclusive)
				if((0 <= $match['minute']) && ($match['minute'] <= 59))
				{
					// second: integer value between 0 and 59 (inclusive)
					if((0 > $match['second']) || ($match['second'] > 59))
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			// if time was not specified, remove empty entries
			unset($match['hour']);
			unset($match['minute']);
			unset($match['second']);
		}

		return $match;
	}

	/**
	 * Returns whether or not the time is specified for 
	 * this formatted date
	 * 
	 * @access protected
	 * @static
	 * @param int[] $formatted_date A date that has been 
	 * properly formatted by is_valid_date
	 * @return boolean True if the time values are 
	 * specified, false otherwise.
	 */
	static protected function is_full_date_time($formatted_date)
	{
		return isset($formatted_date['hour']);
	}

	/**
	 * Presents the specified formatted date as a 
	 * single string formatted to be compatible 
	 * with MySQL
	 * 
	 * @access protected
	 * @static
	 * @param int[] $formatted_date A date that has been 
	 * properly formatted by is_valid_date
	 * @return string A string representation of 
	 * $formatted_date to be used with MySQL
	 */
	static protected function print_date($formatted_date)
	{
		$ret = "\"";
		$ret .= sprintf("%04d", $formatted_date['year']);
		$ret .= "-".sprintf("%02d", $formatted_date['month']);
		$ret .= "-".sprintf("%02d", $formatted_date['day']);
		$ret .= " ".sprintf("%02d", $formatted_date['hour']);
		$ret .= ":".sprintf("%02d", $formatted_date['minute']);
		$ret .= ":".sprintf("%02d", $formatted_date['second']);
		$ret .= "\"";
		return $ret;
	}

	/**
	 * Searches the 'conditions' parameter in $_GET for all 
	 * conditions involving the given date variable name
	 * 
	 * @access protected
	 * @param string $date_var_name The name of the date 
	 * variable to be searched for
	 * @return string A string containing all MySQL conditional 
	 * statements involving the specified date variable
	 */
	protected function parse_date_condition($date_var_name)
	{
		$regex = "~(?<!\S)(?<negate>-)?".$date_var_name.":(?<argument>[^\s\"]+|(?:\"[^\"]+\"))(?!\S)~";
		preg_match_all($regex, $_GET['conditions'], $date_matches, PREG_SET_ORDER);

		// Remove the discovered conditions out of 'conditions' in GET
		$_GET['conditions'] = preg_replace($regex, "", $_GET['conditions']);

		if(!empty($date_matches))
		{
			/*
			includes: an array of all dates and 
			times that the variable may be equal to
			*/
			$includes = array();
			/*
			excludes: an array of all formatted dates and 
			times that the variable may not be equal to
			*/
			$excludes = array();
			/*
			conditions: an array of all logical 
			statements that involve this date variable
			*/
			$conditions = array();

			foreach($date_matches as $key => &$match)
			{
				/*
				negate: a boolean representing whether or not 
				the returned results should satisfy this 
				condition
				*/
				$match['negate'] = (boolean) !empty($match['negate']);
				
				// argument: the main body of the condition
				$match['argument'] = trim($match['argument'], "\"");
				$match['argument'] = trim($match['argument']);
				
				// check to see if the argument is for a range-type condition
				if(preg_match("~^\s*(?<arg>[^\.\*]+)\s*\.\.\s*(?<arg_2>[^\.\*]+)\s*$~", $match['argument'], $arg_match))
				{
					$arg_match['operator'] = "..";
				}
				elseif(preg_match("~^\s*(?<operator>(?:[>!<]?=)|(?:[><]?=?))\s*(?<arg>[^\.\*]+)\s*$~", $match['argument'], $arg_match))
				{
					// If no operator is given, assume operator is =
					if(empty($arg_match['operator']))
					{
						$arg_match['operator'] = "=";
					}
				}

				$match['operator'] = $arg_match['operator'];
				
				/*
				Most conditions can be changed to an equivalent negated condition 
				just by changing the operator
				*/
				if($match['negate'])
				{
					// Assume we can change the operator to make a negated condition
					$match['negate'] = FALSE;
					switch($match['operator'])
					{
						case '=':
							$match['operator'] = "!=";
							break;
						
						case '!=':
							$match['operator'] = "=";
							break;
						
						case '<':
							$match['operator'] = ">=";
							break;
						
						case '>=':
							$match['operator'] = "<";
							break;
						
						case '>':
							$match['operator'] = "<=";
							break;
						
						case '<=':
							$match['operator'] = ">";
							break;

						/*
						There is no easy way to change a range into a negated range, 
						so just change negate back to true and handle it later
						*/
						case '..':
							$match['negate'] = TRUE;
							break;
					}
				}

				// Format the date arguments
				$match['arg'] = $this->is_valid_date($arg_match['arg']);
				
				if(is_bool($match['arg']))
				{
					$this->return_error("Date Formatting Error", "The given date and time parameter is invalid", $arg_match['arg']);
				}

				if(isset($arg_match['arg_2']))
				{
					$match['arg_2'] = $this->is_valid_date($arg_match['arg_2']);
					
					if(is_bool($match['arg_2']))
					{
						$this->return_error("Date Formatting Error", "The given date and time parameter is invalid", $arg_match['arg_2']);
					}
				}

				// Format incomplete date arguments
				switch ($match['operator'])
				{
					case '!=':
						/*
						If the date doesn't specify a time, treat the condition 
						as a negated range expression over the given date's day
						*/
						if(!$this->is_full_date_time($match['arg']))
						{
							$match['arg']['hour'] = 0;
							$match['arg']['minute'] = 0;
							$match['arg']['second'] = 0;

							$match['arg_2'] = array();
						
							$match['arg_2']['year'] = $match['arg']['year'];
							$match['arg_2']['month'] = $match['arg']['month'];
							$match['arg_2']['day'] = $match['arg']['day'];

							$match['operator'] = "..";
							$match['negate'] = TRUE;
						}
						break;

					case '<':
					case '>=':
					case '..':
						if(!$this->is_full_date_time($match['arg']))
						{
							$match['arg']['hour'] = 0;
							$match['arg']['minute'] = 0;
							$match['arg']['second'] = 0;
						}
						break;

					case '>':
					case '<=':
						if(!$this->is_full_date_time($match['arg']))
						{
							$match['arg']['hour'] = 23;
							$match['arg']['minute'] = 59;
							$match['arg']['second'] = 59;
						}
						break;
				}

				// Don't forget to complete argument 2 if need be
				if($match['operator'] == "..")
				{
					if(!$this->is_full_date_time($match['arg_2']))
					{
						$match['arg_2']['hour'] = 23;
						$match['arg_2']['minute'] = 59;
						$match['arg_2']['second'] = 59;
					}
				}


				$mapping = $this->valid_var_mappings();
				
				switch($match['operator'])
				{
					/*= arguments may be complete dates and times
					or just dates*/
					case '=':
						array_push($includes, $match);
						break;
					/*
					By this point, we can conclude that != arguments 
					have both dates and times, so we can just print them
					*/
					case '!=':
						array_push($excludes, $this->print_date($match['arg']));
						break;
					
					// Form the MySQL statements and add them to $conditions
					case '<':
					case '>=':
					case '>':
					case '<=':
						array_push($conditions, $mapping[$date_var_name]." ".$match['operator']." ".$this->print_date($match['arg']));
						break;
					case '..':
						if(!$match['negate'])
						{
							array_push($conditions, $mapping[$date_var_name]." BETWEEN ".$this->print_date($match['arg'])." AND ".$this->print_date($match['arg_2']));
						}
						else
						{
							array_push($conditions, $mapping[$date_var_name]." NOT BETWEEN ".$this->print_date($match['arg'])." AND ".$this->print_date($match['arg_2']));
						}
						break;
				}
			}

			// Process any $includes elements
			if(count($includes) > 0)
			{
				// Contains overall conditions for included date ranges
				$included_conditions = array();
				
				// Contains specific dates and times
				$included_values = array();
				
				foreach($includes as $key => &$match)
				{
					if($this->is_full_date_time($match['arg']))
					{
						// Add complete dates and times to $included_values
						array_push($included_values, $this->print_date($match['arg']));
					}
					else
					{
						// Set up a range statement for incomplete dates
						$match['arg']['hour'] = 0;
						$match['arg']['minute'] = 0;
						$match['arg']['second'] = 0;

						$match['arg_2'] = array();
						
						$match['arg_2']['year'] = $match['arg']['year'];
						$match['arg_2']['month'] = $match['arg']['month'];
						$match['arg_2']['day'] = $match['arg']['day'];
						
						$match['arg_2']['hour'] = 23;
						$match['arg_2']['minute'] = 59;
						$match['arg_2']['second'] = 59;

						array_push($included_conditions, $mapping[$date_var_name]." BETWEEN ".$this->print_date($match['arg'])." AND ".$this->print_date($match['arg_2']));
					}
				}

				/*
				Process any specific dates and times, 
				add their statements to $included_conditions
				*/
				if(count($included_values) > 0)
				{
					if(count($included_values) > 1)
					{
						array_push($included_conditions, $mapping[$date_var_name]." IN (".implode(", ", $included_values).")");
					}
					else
					{
						
						// prepare to get first (and only) element of array
						reset($included_values);


						// Add condition "X = element" to $included_conditions
						array_push($included_conditions, $mapping[$date_var_name]." = ".current($included_values));
					}
				}

				// BIG THING TO NOTE: $included_conditions get ORed together
				array_push($conditions, $this->condition_combine($included_conditions, "OR"));
			}
			
			/*
			Process any $excludes elements, 
			which are all complete and MySQL-formatted dates and times
			*/
			if(count($excludes) > 0)
			{
				if(count($excludes) > 1)
				{
					array_push($conditions, $mapping[$date_var_name]." NOT IN (".implode(", ", $excludes).")");
				}
				else
				{
					
					// prepare to get first (and only) element of array
					reset($excludes);

					// Add condition "X != element" to $conditions
					array_push($conditions, $mapping[$date_var_name]." != ".current($excludes));
				}
			}

			if(count($conditions) > 0)	
			{
				return $this->condition_combine($conditions);
			}
		}
		return "TRUE";
	}

	/**
	 * Creates a MySQL conditional statement from all input 
	 * conditions that involve date variables.
	 * 
	 * @access protected
	 * @return string A MySQL conditional statement from all 
	 * input conditions that involve date variables.
	 */
	protected function get_datetime_conditions()
	{
		$valid_date_vars = $this->valid_date_vars();
		$arr = array();
		if(!empty($valid_date_vars))
		{
			foreach($valid_date_vars as $key => $var_name)
			{
				array_push($arr, $this->parse_date_condition($var_name));
			}
		}
		return $this->condition_combine($arr);
	}

	/**
	 * Searches the 'conditions' parameter in $_GET for all 
	 * conditions involving the given string variable name
	 * 
	 * @access protected
	 * @param string $string_var_name The name of the string 
	 * variable to be searched for
	 * @return string String containing all MySQL conditional 
	 * statements involving the specified string variable
	 */
	protected function parse_string_condition($string_var_name)
	{
		/*
		Regex used for searching for a this specific integer 
		variable name
		*/
		$regex = "~(?<!\S)(?<negate>-)?".$string_var_name.":(?<argument>[^\s\"]+|(?:\"[^\"]+\"))(?!\S)~";
		
		preg_match_all($regex, $_GET['conditions'], $string_matches, PREG_SET_ORDER);

		// Remove the discovered conditions out of 'conditions' in GET
		$_GET['conditions'] = preg_replace($regex, "", $_GET['conditions']);

		// Parse every condition
		if(!empty($string_matches))
		{
			/*
			conditions: an array containing all MySQL-formatted 
			contitional statements that this string variable should fulfill
			*/
			$conditions = array();

			foreach($string_matches as $key => &$match)
			{
				/*
				negate: a boolean representing whether or not 
				the returned results should satisfy this 
				condition
				*/
				$match['negate'] = (boolean) !empty($match['negate']);
				
				// argument: the main body of the condition
				$match['argument'] = trim($match['argument'], "\"");
				
				$mapping = $this->valid_var_mappings();

				if($match['negate'])
				{
					array_push($conditions, $mapping[$string_var_name]." NOT LIKE \"%".$match['argument']."%\"");
				}
				else
				{
					array_push($conditions, $mapping[$string_var_name]." LIKE \"%".$match['argument']."%\"");
				}
			}

			$conditions = array_unique($conditions);
			if(0 < count($conditions))
			{
				return $this->condition_combine($conditions);
			}
		}
		return "TRUE";
	}

	/**
	 * Creates a MySQL conditional statement from all input 
	 * conditions that involve specific string variables.
	 * 
	 * @access protected
	 * @return string A MySQL conditional statement from all 
	 * input conditions that involve specific string variables.
	 */
	protected function get_string_conditions()
	{
		$valid_string_vars = $this->valid_string_vars();
		$arr = array();
		if(!empty($valid_string_vars))
		{
			foreach($valid_string_vars as $key => $var_name)
			{
				array_push($arr, $this->parse_string_condition($var_name));
			}
		}
		return $this->condition_combine($arr);
	}

	/**
	 * Creates a MySQL conditional statement from all input 
	 * conditions that involve a specific variable.
	 * 
	 * @access protected
	 * @return string A MySQL conditional statement from all 
	 * input conditions that involve a specific variable.
	 */
	protected function get_normal_conditions()
	{
		return $this->condition_combine(
			array(
				$this->get_boolean_conditions(),
				$this->get_integer_conditions(),
				$this->get_datetime_conditions(),
				$this->get_string_conditions()
			)
		);
	}

	/**
	 * Creates a MySQL conditional statement from all input 
	 * conditions that involve collective string variables.
	 * 
	 * @access protected
	 * @return string A MySQL conditional statement from all 
	 * input conditions that involve collective string variables.
	 */
	protected function get_search_conditions()
	{
		$valid_string_vars = $this->valid_string_vars();
		
		// Search in all string variables by default if the 'in' parameter in GET isn't set
		$in_string_list = $this->valid_string_vars();

		// Get user specified 'in' parameters
		if(!empty($_GET['in']))
		{
			$in_string_list = array();
			$in_parameter = explode(",", $_GET['in']);
			foreach($in_parameter as $key => $var_name)
			{
				$var_name = trim($var_name);
				if((!empty($var_name)) && in_array($var_name, $valid_string_vars))
				{
					array_push($in_string_list, $var_name);
				}
			}
			$in_string_list = array_unique($in_string_list);
		}

		/*
		If the user doesn't specify an input string, 
		disregard all general search terms
		*/
		if(empty($in_string_list))
		{
			return "TRUE";
		}

		// Regex to extract out all general search terms
		$regex = "~(?<!\S)(?<negate>(?i:NOT\s+))?(?<search_term>(?:[^\s\":]+)|(?:\"[^\"]+\"))(?!\S)~";
		
		preg_match_all($regex, $_GET['conditions'], $search_matches, PREG_SET_ORDER);
		
		// Remove the discovered search terms out of 'conditions' in GET
		$_GET['conditions'] = preg_replace($regex, "", $_GET['conditions']);
		
		// If there are no search terms, return "TRUE"
		if(empty($search_matches))
		{
			return "TRUE";
		}

		// includes: an array of search terms to require
		$includes = array();
		
		// excludes: an array of search terms to avoid
		$excludes = array();
		

		foreach($search_matches as $key => $match)
		{
			/*
			negate: a boolean representing whether or not 
			the returned results should satisfy this 
			condition
			*/
			$match['negate'] = trim($match['negate']);
			$match['negate'] = (boolean) (!empty($match['negate']));

			// search_term: the term to search for
			$match['search_term'] = trim($match['search_term'], "\"");
			
			if($match['negate'])
			{
				array_push($excludes, $match['search_term']);
			}
			else
			{
				array_push($includes, $match['search_term']);
			}
		}

		$includes = array_unique($includes);
		$excludes = array_unique($excludes);

		/*
		conditions: an array containing the 
		MySQL-formatted search term conditions
		*/
		$conditions = array();

		$mapping = $this->valid_var_mappings();

		// Process included search terms
		if(!empty($includes))
		{
			foreach($includes as $key1 => $search_term)
			{
				/*
				var_arr: array that contains all MySQL-formatted 
				conditions for the current search term
				*/
				$var_arr = array();

				foreach ($in_string_list as $key2 => $string_var)
				{
					array_push($var_arr, $mapping[$string_var]." LIKE \"%".$search_term."%\"");
				}

				array_push($conditions, $this->condition_combine($var_arr, "OR"));
			}
		}

		// Process excluded search terms
		if(!empty($excludes))
		{
			foreach($excludes as $key1 => $search_term)
			{
				foreach ($in_string_list as $key2 => $string_var)
				{
					array_push($conditions, $mapping[$string_var]." NOT LIKE \"%".$search_term."%\"");
				}
			}
		}

		return $this->condition_combine($conditions);
	}

	/**
	 * Creates a MySQL sorting statement from the 'sort'
	 * input parameter in GET
	 * 
	 * @access protected
	 * @return string a MySQL sorting statement from the 'sort'
	 * input parameter in GET, or empty string if 'sort' is not set
	 */
	protected function get_sort_conditions()
	{
		if(empty($_GET['sort']))
		{
			return "";
		}

		$sort_parameter = trim($_GET['sort']);
		$sort_parameter = preg_replace("~\s+~", " ", $sort_parameter);
		

		$sort_arr = explode(",", $sort_parameter);

		$mapping = $this->valid_var_mappings();

		$sort = array();

		foreach($sort_arr as $key => &$sort_element)
		{
			$sort_element = trim($sort_element);

			if(empty($sort_element))
			{
				continue;
			}

			$regex = "~^\s*(?<var_name>[a-zA-Z_]+)(?:\s*[:]\s*(?<order>(?i:ASC|DESC)))?\s*$~";
			if(!preg_match($regex, $sort_element, $match))
			{
				$this->return_error("Sorting Error", "The given variable is invalid for sorting", $sort_element);
			}

			// Default order is descending
			if(!isset($match['order']))
			{
				$match['order'] = "DESC";
			}

			$match['order'] = strtoupper($match['order']);

			if(!array_key_exists($match['var_name'], $mapping))
			{
				$this->return_error("Sorting Variable Error", "The given variable does not exist in this call", $match['var_name']);
			}

			array_push($sort, $mapping[$match['var_name']]." ".$match['order']);
		}

		// If there's an order to sort in, generate it
		if(!empty($sort))
		{
			return " ORDER BY ".implode(", ", $sort);
		}

		return "";
	}

	/**
	 * Creates a MySQL limit/offset statement from the 
	 * 'page' and 'pagesize' input parameters in GET
	 * 
	 * @access protected
	 * @return string A MySQL limit/offset statement from the 
	 * 'page' and 'pagesize' input parameters in GET
	 */
	protected function paginate_query()
	{
		// Default pagesize
		$pagesize = 30;

		if(isset($_GET['pagesize']))
		{
			if(!is_numeric($_GET['pagesize']))
				$this->return_error("Pagesize Error", "The given pagesize parameter is invalid; should be an integer from between 1 and 100 (inclusive)", $_GET['pagesize']);

			// Re-size pagesize to fit between 1 and 100 inclusive
			$pagesize = max(1, min(100, $_GET["pagesize"]));
		}

		// Default page
		$page = 1;

		if(isset($_GET["page"]))
		{
			if(!is_numeric($_GET['page']))
				$this->return_error("Page Error", "The given page parameter is invalid; should be a positive (0 < page) integer", $_GET['page']);
			$page = max(1, $_GET["page"]);
		}

		return " LIMIT ".$pagesize." OFFSET ".($page - 1) * $pagesize;
	}

	/**
	 * Arguably the most important function, this 
	 * returns either a valid MySQL query to be run
	 * for this API call, or NULL, if the URL doesn't
	 * describe this kind of API call
	 * 
	 * @access public
	 * @return string NULL if the URL doesn't describe 
	 * this kind of API call, a valid MySQL query to 
	 * be run for this API call otherwise
	 */
	public function get_query()
	{
		//Check if the call is for this type of CallableData
		if(!$this->is_valid_call())
		{
			return NULL;
		}

		// Get basic query
		$query = $this->get_base_call();
		
		// Get all conditions of every type
		$all_conditions = $this->condition_combine(
			array(
				$this->get_normal_conditions(),
				$this->get_search_conditions()
			)
		);

		// Add any conditions to the query
		if(trim($all_conditions) != "TRUE")
		{
			$query .= " WHERE ".$all_conditions;
		}

		// Add the sorting conditions
		$query .= $this->get_sort_conditions();

		// Add the limit and offset conditions
		$query .= $this->paginate_query();

		return $query;
	}
}

?>