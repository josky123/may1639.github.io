<?php
header("access-control-allow-origin: *");
header('Content-Type: application/json');


abstract class CallableData
{
	abstract protected function is_valid_call();
	abstract protected function valid_bool_vars();
	abstract protected function valid_int_vars();
	abstract protected function valid_date_vars();
	abstract protected function valid_string_vars();
	abstract protected function valid_var_mappings();
	abstract protected function get_base_call();
	
	protected function condition_combine($arr, $logic="AND")
	{
		$logic = strtoupper($logic);
		$logic = trim($logic);
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

	protected function get_string_conditions()
	{
		return "TRUE";
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
		$to_remove = array();
		
		foreach($sort_arr as $key => &$sort_element)
		{
			$sort_element = trim($sort_element);
			if(empty($sort_element))
			{
				array_push($to_remove, $key);
				continue;
			}

			$regex = "~^\s*(?<var_name>[a-zA-Z_]+)(?:\s*[:]\s*(?<order>(?i:ASC|DESC)))?\s*$~";
			if(!preg_match($regex, $sort_element, $match))
			{
				echo "The following is not a valid sorting argument: \"".$sort_element."\"";
				exit(0);
			}
			var_dump($match);
		}

		foreach($to_remove as $key => $value)
		{
			unset($sort_arr[$value]);
		}
		return "";
	}

	public function get_query()
	{
		if(!$this->is_valid_call())
			return false;
		$query = $this->get_base_call();
		$filters = array();
		$all_conditions = $this->condition_combine(
			array(
				$this->get_normal_conditions(),
				$this->get_search_conditions()
			)
		);

		if(preg_match("i~^\s*TRUE\s*$~", $all_conditions))
		{
			$all_conditions = "";
		}

		if(!empty($all_conditions))
		{
			$query .= " WHERE ".$all_conditions;
		}


		return $query;
	}
}

/**

*/

class quest extends CallableData
{
	public function is_valid_call()
	{
		return preg_match("~^/questions$~", $_SERVER['PATH_INFO']);
	}

	protected function valid_bool_vars()
	{
		return array(
			"is_answered"
		);
	}

	protected function valid_int_vars()
	{
		return array(
			"question_id",
			"owner",
			"view_count",
			"score",
			"up_vote_count",
			"down_vote_count",
			"answer_count",
			"accepted_answer_id"
		);
	}

	protected function valid_date_vars()
	{
		return array(
			"creation_date",
			"last_activity_date",
			"last_edit_date"
		);
	}

	protected function valid_string_vars()
	{
		return array(
			"title",
			"body",
			"tags"
		);
	}

	protected function valid_var_mappings()
	{
		return array(
			"question_id" => "Q.`question_id`",
			"owner" => "Q.`owner`",
			"title" => "Q.`title`",
			"body" => "Q.`body`",
			"tags" => "Q.`tags`",
			"view_count" => "Q.`view_count`",
			"score" => "Q.`score`",
			"up_vote_count" => "Q.`up_vote_count`",
			"down_vote_count" => "Q.`down_vote_count`",
			"answer_count" => "Q.`answer_count`",
			"is_answered" => "Q.`is_answered`",
			"accepted_answer_id" => "Q.`accepted_answer_id`",
			"creation_date" => "Q.`creation_date`",
			"last_activity_date" => "Q.`last_activity_date`",
			"last_edit_date" => "Q.`last_edit_date`"
		);
	}

	protected function get_base_call()
	{
		return "SELECT Q.* FROM
			(SELECT
				Q.`selchildid` AS `accepted_answer_id`,
				Q.`acount` AS `answer_count`,
				Q.`content` AS `body`,
				Q.`created` AS `creation_date`,
				Q.`downvotes` AS `down_vote_count`,
				(0 < Q.`acount`) AS `is_answered`,
				IFNULL(Q.`updated`, Q.`created`) AS `last_activity_date`,
				Q.`updated` AS `last_edit_date`,
				Q.`userid` AS `owner`,
				Q.`postid` AS `question_id`,
				Q.`netvotes` AS `score`,
				Q.`notify` AS `share_link`,
				Q.`tags` AS `tags`,
				Q.`title` AS `title`,
				Q.`upvotes` AS `up_vote_count`,
				Q.`views` AS `view_count`
			FROM
				`qa_posts` Q
			WHERE
				Q.`type` IN ('Q','Q_HIDDEN','Q_QUEUED')
			) Q";
	}
}


$types = array(new quest);
foreach($types as $type)
{
	echo $type->get_query();
}
exit(0);











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