<?php

abstract class CallableData
{
	abstract public function is_valid_call();
	abstract public function construct_from_row($row);
	abstract protected function valid_bool_vars();
	abstract protected function valid_int_vars();
	abstract protected function valid_date_vars();
	abstract protected function valid_string_vars();
	abstract protected function valid_var_mappings();
	abstract protected function get_base_call();
	
	protected function condition_combine($arr, $logic="AND")
	{
		$logic = trim(strtoupper($logic));
		$to_remove = array();
		$arr = array_unique($arr);
		foreach ($arr as $key => $value)
		{
			$value = strtoupper($value);
			$value = preg_replace("~\s+~", " ", $value);
			$value = trim($value);
			switch ($logic)
			{
				case 'AND':
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

		foreach ($to_remove as $key => $value)
		{
			unset($arr[$value]);
		}

		if(empty($arr))
		{
			switch($logic)
			{
				case 'AND':
					return "TRUE";
					break;
				
				case 'OR':
					return "FALSE";
					break;
			}
		}

		$ret = implode(" ".$logic." ", $arr);
		if(1 < count($arr))
		{
			return "(".$ret.")";
		}
		else
		{
			return $ret;
		}
	}

	protected function parse_bool_condition($bool_var_name)
	{
		$regex = "~(?<!\S)(?<negate>-)?".$bool_var_name.":(?<argument>(?i:true|false)|(?:\"\s*(?i:true|false)\s*\"))(?!\S)~";
		preg_match_all($regex, $_GET['conditions'], $bool_matches, PREG_SET_ORDER);
		
		$bool_value = NULL;

		if(!empty($bool_matches))
		{
			foreach($bool_matches as $key => $match)
			{
				$match['argument'] = trim($match['argument'], "\"");
				$match['argument'] = trim($match['argument']);
				$match['argument'] = strtolower($match['argument']);
				$match['argument'] = (boolean) (($match['argument'] != 'false') && ($match['argument'] == 'true'));
				
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
					echo "There is a boolean mismatch on \"".$bool_var_name."\".";
					exit(0);
				}
			}
		}
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

	protected function parse_int_condition($int_var_name)
	{
		$regex = "~(?<!\S)(?<negate>-)?".$int_var_name.":(?<argument>[^\s\"]+|(?:\"[^\"]+\"))(?!\S)~";
		preg_match_all($regex, $_GET['conditions'], $int_matches, PREG_SET_ORDER);
		if(!empty($int_matches))
		{
			$includes = array();
			$include_ranges = array();
			$excludes = array();
			$exclude_ranges = array();
			$comparisons = array();
			foreach($int_matches as $key => &$match)
			{
				$match['negate'] = (boolean) !empty($match['negate']);
				$match['argument'] = trim($match['argument'], "\"");
				$match['argument'] = trim($match['argument']);
				if(preg_match("~^\s*(?<arg>-?[0-9]+)\s*\.\.\s*\*\s*$~", $match['argument'], $arg_match))
				{
					$arg_match['arg'] = intval($arg_match['arg']);
					$arg_match['operator'] = ">=";
				}
				elseif(preg_match("~^\s*\*\s*\.\.\s*(?<arg>-?[0-9]+)\s*$~", $match['argument'], $arg_match))
				{
					$arg_match['arg'] = intval($arg_match['arg']);
					$arg_match['operator'] = "<=";
				}
				elseif(preg_match("~^\s*(?<arg>-?[0-9]+)\s*\.\.\s*(?<arg_2>-?[0-9]+)\s*$~", $match['argument'], $arg_match))
				{
					$arg_match['operator'] = "..";
					$arg_match['arg'] = intval($arg_match['arg']);
					$arg_match['arg_2'] = intval($arg_match['arg_2']);
					if ($arg_match['arg'] === $arg_match['arg_2'])
					{
						unset($arg_match['arg_2']);
						$arg_match['operator'] = "=";
					}
					else
					{
						$arg_match['operator'] = "..";
					}
				}
				elseif(preg_match("~^\s*(?<operator>(?:[>!<]?=)|(?:[><]?=?))\s*(?<arg>-?[0-9]+)\s*$~", $match['argument'], $arg_match))
				{
					$arg_match['arg'] = intval($arg_match['arg']);
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
				if($match['negate'])
				{
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

						case '..':
							$match['negate'] = true;
							break;
					}
				}
				$mapping = $this->valid_var_mappings();
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
						if(!$match['negate'])
						{
							array_push($include_ranges, $mapping[$int_var_name]." BETWEEN ".$match['arg']." AND ".$match['arg_2']);
						}
						else
						{
							array_push($exclude_ranges, $mapping[$int_var_name]." BETWEEN ".$match['arg']." AND ".$match['arg_2']);
						}
						break;
				}
			}

			$includes = array_unique($includes);
			if(count($includes) > 0)
			{
				$includes = $mapping[$int_var_name]." IN (".implode(", ", $includes).")";
			}
			else
			{
				$includes = "TRUE";
			}

			$excludes = array_unique($excludes);
			if(count($excludes) > 0)	
			{
				$excludes = $mapping[$int_var_name]." NOT IN (".implode(", ", $excludes).")";
			}
			else
			{
				$excludes = "TRUE";
			}

			$comparisons = array_unique($comparisons);
			if(count($comparisons) > 0)	
			{
				$comparisons = $this->condition_combine($comparisons);
			}
			else
			{
				$comparisons = "TRUE";
			}

			$include_ranges = array_unique($include_ranges);
			if(count($include_ranges) > 0)	
			{
				$include_ranges = $this->condition_combine($include_ranges, "OR");
			}
			else
			{
				$include_ranges = "TRUE";
			}

			$exclude_ranges = array_unique($exclude_ranges);
			if(count($exclude_ranges) > 0)	
			{
				$exclude_ranges = "NOT ".$this->condition_combine($exclude_ranges, "OR");
			}
			else
			{
				$exclude_ranges = "TRUE";
			}
			return $this->condition_combine(array($includes, $excludes, $comparisons, $include_ranges, $exclude_ranges));
		}
		else
		{
			return "TRUE";
		}
	}

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

	protected function is_valid_date($date)
	{
		if(!preg_match("~^\s*(?<year>\d{4})-(?<month>\d{2})-(?<day>\d{2})(?:\s+(?<hour>\d{2}):(?<minute>\d{2}):(?<second>\d{2}))?\s*$~", $date, $match))
		{
			return false;
		}

		$match['year'] = intval($match['year']);
		$match['month'] = intval($match['month']);
		$match['day'] = intval($match['day']);

		if(!checkdate($match['month'], $match['day'], $match['year']))
		{
			return false;
		}

		if(!empty($match['hour']))
		{
			$match['hour'] = intval($match['hour']);
			$match['minute'] = intval($match['minute']);
			$match['second'] = intval($match['second']);
			if((0 <= $match['hour']) && ($match['hour'] <= 23))
			{
				if((0 <= $match['minute']) && ($match['minute'] <= 59))
				{
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
			unset($match['hour']);
			unset($match['minute']);
			unset($match['second']);
		}

		return $match;
	}

	protected function is_full_date_time($formatted_date)
	{
		return isset($formatted_date['hour']);
	}

	protected function print_date($formatted_date)
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

	protected function parse_date_condition($date_var_name)
	{
		$regex = "~(?<!\S)(?<negate>-)?".$date_var_name.":(?<argument>[^\s\"]+|(?:\"[^\"]+\"))(?!\S)~";
		preg_match_all($regex, $_GET['conditions'], $date_matches, PREG_SET_ORDER);
		if(!empty($date_matches))
		{
			$includes = array();
			$include_ranges = array();
			$excludes = array();
			$exclude_ranges = array();
			$comparisons = array();

			foreach($date_matches as $key => &$match)
			{
				$match['negate'] = (boolean) !empty($match['negate']);
				$match['argument'] = trim($match['argument'], "\"");
				$match['argument'] = trim($match['argument']);
				if(preg_match("~^\s*(?<arg>[^\.\*]+)\s*\.\.\s*\*\s*$~", $match['argument'], $arg_match))
				{
					$arg_match['operator'] = ">=";
				}
				elseif(preg_match("~^\s*\*\s*\.\.\s*(?<arg>[^\.\*]+)\s*$~", $match['argument'], $arg_match))
				{
					$arg_match['operator'] = "<=";
				}
				elseif(preg_match("~^\s*(?<arg>[^\.\*]+)\s*\.\.\s*(?<arg_2>[^\.\*]+)\s*$~", $match['argument'], $arg_match))
				{
					$arg_match['operator'] = "..";
				}
				elseif(preg_match("~^\s*(?<operator>(?:[>!<]?=)|(?:[><]?=?))\s*(?<arg>[^\.\*]+)\s*$~", $match['argument'], $arg_match))
				{
					if(empty($arg_match['operator']))
					{
						$arg_match['operator'] = "=";
					}
				}

				$match['operator'] = $arg_match['operator'];
				
				if($match['negate'])
				{
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

						case '..':
							$match['negate'] = true;
							break;
					}
				}

				$match['arg'] = $this->is_valid_date($arg_match['arg']);
				
				if(is_bool($match['arg']))
				{
					echo "The input for variable \"".$date_var_name."\" was invalid.";
					exit(0);
				}

				if(isset($arg_match['arg_2']))
				{
					$match['arg_2'] = $this->is_valid_date($arg_match['arg_2']);
					
					if(is_bool($match['arg_2']))
					{
						echo "The input for variable \"".$date_var_name."\" was invalid.";
						exit(0);
					}
				}

				switch ($match['operator'])
				{
					case '=':
					case '!=':
						if(!$this->is_full_date_time($match['arg']))
						{
							$match['arg_2'] = array();
							$match['arg_2']['year'] = $match['arg']['year'];
							$match['arg_2']['month'] = $match['arg']['month'];
							$match['arg_2']['day'] = $match['arg']['day'];
							$match['negate'] = ($match['operator'] == "!=");
							$match['operator'] = "..";
						}
						break;

					case '<':
					case '>=':
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


				if($match['operator'] == "..")
				{
					if(!$this->is_full_date_time($match['arg']))
					{
						$match['arg']['hour'] = 0;
						$match['arg']['minute'] = 0;
						$match['arg']['second'] = 0;
					}
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
					
					case '=':
						array_push($includes, $this->print_date($match['arg']));
						break;
					
					case '!=':
						array_push($excludes, $this->print_date($match['arg']));
						break;
					/**/
					case '<':
					case '>=':
					case '>':
					case '<=':
						array_push($comparisons, $mapping[$date_var_name]." ".$match['operator']." ".$this->print_date($match['arg']));
						break;
					case '..':
						if(!$match['negate'])
						{
							array_push($include_ranges, $mapping[$date_var_name]." BETWEEN ".$this->print_date($match['arg'])." AND ".$this->print_date($match['arg_2']));
						}
						else
						{
							array_push($exclude_ranges, $mapping[$date_var_name]." BETWEEN ".$this->print_date($match['arg'])." AND ".$this->print_date($match['arg_2']));
						}
						break;
				}
			}

			$includes = array_unique($includes);
			if(count($includes) > 0)
			{
				$includes = $mapping[$date_var_name]." IN (".implode(", ", $includes).")";
			}
			else
			{
				$includes = "TRUE";
			}

			$excludes = array_unique($excludes);
			if(count($excludes) > 0)	
			{
				$excludes = $mapping[$date_var_name]." NOT IN (".implode(", ", $excludes).")";
			}
			else
			{
				$excludes = "TRUE";
			}

			$comparisons = array_unique($comparisons);
			if(count($comparisons) > 0)	
			{
				$comparisons = $this->condition_combine($comparisons);
			}
			else
			{
				$comparisons = "TRUE";
			}

			$include_ranges = array_unique($include_ranges);
			if(count($include_ranges) > 0)	
			{
				$include_ranges = $this->condition_combine($include_ranges, "OR");
			}
			else
			{
				$include_ranges = "TRUE";
			}

			$exclude_ranges = array_unique($exclude_ranges);
			if(count($exclude_ranges) > 0)	
			{
				$exclude_ranges = "NOT ".$this->condition_combine($exclude_ranges, "OR");
			}
			else
			{
				$exclude_ranges = "TRUE";
			}

			return $this->condition_combine(array($includes, $excludes, $comparisons, $include_ranges, $exclude_ranges));
		}
		else
		{
			return "TRUE";
		}
		return "TRUE";
	}

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

	protected function parse_string_condition($string_var_name)
	{
		$regex = "~(?<!\S)(?<negate>-)?".$string_var_name.":(?<argument>[^\s\"]+|(?:\"[^\"]+\"))(?!\S)~";
		preg_match_all($regex, $_GET['conditions'], $string_matches, PREG_SET_ORDER);
		if(!empty($string_matches))
		{
			$includes = array();
			$excludes = array();
			foreach($string_matches as $key => &$match)
			{
				$match['negate'] = (boolean) !empty($match['negate']);
				$match['argument'] = trim($match['argument'], "\"");
				
				$mapping = $this->valid_var_mappings();

				if($match['negate'])
				{
					array_push($excludes, $mapping[$string_var_name]." LIKE \"%".$match['argument']."%\"");
				}
				else
				{
					array_push($includes, $mapping[$string_var_name]." LIKE \"%".$match['argument']."%\"");
				}
			}

			$includes = array_unique($includes);
			if(count($includes) > 0)
			{
				$includes = $this->condition_combine($includes, "AND");
			}
			else
			{
				$includes = "TRUE";
			}

			$excludes = array_unique($excludes);
			if(count($excludes) > 0)
			{
				$excludes = "NOT ".$this->condition_combine($excludes, "OR");
			}
			else
			{
				$excludes = "TRUE";
			}
			return $this->condition_combine(array($includes, $excludes));
		}
		else
		{
			return "TRUE";
		}
	}

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

	protected function get_search_conditions()
	{
		$valid_string_vars = $this->valid_string_vars();
		$in_string_list = $this->valid_string_vars();
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

		if(empty($in_string_list))
		{
			return "TRUE";
		}

		$regex = "~(?<!\S)(?<negate>(?i:NOT\s+))?(?<search_term>(?:[^\s\":]+)|(?:\"[^\"]+\"))(?!\S)~";
		preg_match_all($regex, $_GET['conditions'], $search_matches, PREG_SET_ORDER);
		
		$includes = array();
		$excludes = array();
		
		if(empty($search_matches))
		{
			return "TRUE";
		}

		foreach($search_matches as $key => $match)
		{
			$match['negate'] = trim($match['negate']);
			$match['negate'] = (boolean) (!empty($match['negate']));
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

		$ret = array();

		$mapping = $this->valid_var_mappings();

		if(!empty($includes))
		{
			$term_arr = array();
			foreach($includes as $key1 => $search_term)
			{
				$var_arr = array();
				foreach ($in_string_list as $key2 => $string_var)
				{
					array_push($var_arr, $mapping[$string_var]." LIKE \"%".$search_term."%\"");
				}
				array_push($term_arr, $this->condition_combine($var_arr, "OR"));
			}
			array_push($ret, $this->condition_combine($term_arr));
		}

		if(!empty($excludes))
		{
			$term_arr = array();
			foreach($excludes as $key1 => $search_term)
			{
				$var_arr = array();
				foreach ($in_string_list as $key2 => $string_var)
				{
					array_push($var_arr, $mapping[$string_var]." LIKE \"%".$search_term."%\"");
				}
				array_push($term_arr, $this->condition_combine($var_arr, "OR"));
			}
			array_push($ret, "NOT ".$this->condition_combine($term_arr, "OR"));
		}

		return $this->condition_combine($ret);
	}

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


		$ret = array();

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
				echo "The following is not a valid sorting argument: \"".$sort_element."\"";
				exit(0);
			}

			if(!isset($match['order']))
			{
				$match['order'] = "DESC";
			}

			$match['order'] = strtoupper($match['order']);

			if(!array_key_exists($match['var_name'], $mapping))
			{
				echo "The following variable \"".$match['var_name']."\" is not a recognized sorting variable.";
				exit(0);
			}

			array_push($ret, $mapping[$match['var_name']]." ".$match['order']);
		}

		if(!empty($ret))
		{
			return " ORDER BY ".implode(", ", $ret);
		}
		return "";
	}

	protected function paginate_query()
	{
		$pagesize = 30;

		if(isset($_GET['pagesize']))
		{
			if(!is_numeric($_GET['pagesize']))
				return_error(400, 'pagesize', 'bad_parameter');
		
			$pagesize = max(1, min(100, $_GET["pagesize"]));
		}

		$page = 1;

		if(isset($_GET["page"]))
		{
			if(!is_numeric($_GET['page']))
				return_error(400, 'page', 'bad_parameter');
			$page = $_GET["page"];
		}

		return " LIMIT ".$pagesize." OFFSET ".($page - 1) * $pagesize;
	}

	public function get_query()
	{
		$query = $this->get_base_call();
		$filters = array();
		$all_conditions = $this->condition_combine(
			array(
				$this->get_normal_conditions(),
				$this->get_search_conditions()
			)
		);

		if(trim($all_conditions) != "TRUE")
		{
			$query .= " WHERE ".$all_conditions;
		}

		$query .= $this->get_sort_conditions();

		$query .= $this->paginate_query();

		return $query;
	}
}

?>