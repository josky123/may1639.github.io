<?php
require_once "./api_classes/util.php";

class Comment extends CallableData
{
	var $body;
	var $comment_id;
	var $creation_date;
	var $edited;
	var $owner;
	var $post_id;
	var $score;

	public function is_valid_call()
	{
		return preg_match("~^/comments$~", $_SERVER['PATH_INFO']);
	}

	public function construct_from_row($row)
	{
		return new Comment($row);
	}

	function __construct($row)
	{
		$this->body = $row['body'];
		$this->comment_id = (integer) $row['comment_id'];
		$this->creation_date = $row['creation_date'];
		$this->edited = (boolean) $row['edited'];
		$this->owner = (integer) $row['owner'];
		$this->post_id = (integer) $row['post_id'];
		$this->score = (integer) $row['score'];
	}

	protected function valid_bool_vars()
	{
		return array(
			"edited"
		);
	}

	protected function valid_int_vars()
	{
		return array(
			"comment_id",
			"owner",
			"post_id",
			"score"
		);
	}

	protected function valid_date_vars()
	{
		return array(
			"creation_date"
		);
	}

	protected function valid_string_vars()
	{
		return array(
			"body"
		);
	}

	protected function valid_var_mappings()
	{
		return array(
			"body" => "C.`body`",
			"comment_id" => "C.`comment_id`",
			"creation_date" => "C.`creation_date`",
			"edited" => "C.`edited`",
			"owner" => "C.`owner`",
			"post_id" => "C.`post_id`",
			"score" => "C.`score`"
		);
	}

	protected function get_base_call()
	{
		return "SELECT C.* FROM
	(SELECT
		C.`content`	AS	`body`,
		C.`postid`	AS	`comment_id`,
		C.`created`	AS	`creation_date`,
		C.`updated` IS NOT NULL	AS	`edited`,
		C.`userid`	AS	`owner`,
		C.`parentid`	AS	`post_id`,
		C.`netvotes`	AS	`score`
	FROM
		`qa_posts` C
	WHERE
		C.`type` IN ('C','C_HIDDEN','C_QUEUED')) C";
	}
}


// header("access-control-allow-origin: *");
// header('Content-Type: application/json');

// require_once "./api_classes/util.php";

// require_once "./api_classes/answers.php";
// require_once "./api_classes/posts.php";
// require_once "./api_classes/questions.php";
// require_once "./api_classes/tags.php";
// require_once "./api_classes/users.php";

// /**
// * The class for an answer.
// * Data for this comes from: https://api.stackexchange.com/docs/types/answer
// */
// class Comment
// {
// 	var $body;
// 	var $comment_id;
// 	var $creation_date;
// 	var $edited;
// 	var $owner;
// 	var $post_id;
// 	var $score;
	
// /**/

// 	static function get_variable_mapping()
// 	{
// 		return array(
// 			"body" => "C.`body`",
// 			"comment_id" => "C.`comment_id`",
// 			"creation_date" => "C.`creation_date`",
// 			"edited" => "C.`edited`",
// 			"owner" => "C.`owner`",
// 			"post_id" => "C.`post_id`",
// 			"score" => "C.`score`"
// 			);
// 	}

// /**/

// 	static function get_boolean_vars()
// 	{
// 		return array(
// 			"edited"
// 		);
// 	}
// 	return array(
// 			"creation_date"
// 		);
// 	}

// /**/

// 	static function get_integer_vars()
// 	{
// 		return array(
// 			"comment_id",
// 			"owner",
// 			"post_id",
// 			"score"
// 		);
// 	}

// /**/

// 	static function get_string_vars()
// 	{
// 		return array(
// 			"body"
// 		);
// 	}


// 	function __construct($row)
// 	{
// 		$this->body = $row['body'];
// 		$this->comment_id = (integer) $row['comment_id'];
// 		$this->creation_date = $row['creation_date'];
// 		$this->edited = (boolean) $row['edited'];
// 		$this->owner = (integer) $row['owner'];
// 		$this->post_id = (integer) $row['post_id'];
// 		$this->score = (integer) $row['score'];
// 	}

// 	const ID = "comments";

// 	const BASE_QUERY = "SELECT
// 	C.`content`	AS	`body`,
// 	C.`postid`	AS	`comment_id`,
// 	C.`created`	AS	`creation_date`,
// 	C.`updated` IS NOT NULL	AS	`edited`,
// 	C.`userid`	AS	`owner`,
// 	C.`parentid`	AS	`post_id`,
// 	C.`netvotes`	AS	`score`
// FROM
// 	`qa_posts` C
// WHERE
// 	C.`type` IN ('C','C_HIDDEN','C_QUEUED')";

// 	/**
// 	This function returns the base query, appended with the proper
// 	conditional to search for comments by their post IDs.
// 	*/
// 	static function base_ID_query($IDs = false)
// 	{
// 		$query = self::BASE_QUERY;
// 		if($IDs)
// 		{
// 			$query .= " AND C.`postid` IN (".$IDs.")";
// 		}
// 		return $query;
// 	}

// 	/**
// 	This function returns the base query, appended with the proper
// 	conditional to search for comments by their parent IDs.
// 	*/
// 	static function base_parent_ID_query($IDs = false)
// 	{
// 		$query = self::BASE_QUERY;
// 		if($IDs)
// 		{
// 			$query .= " AND C.`parentid` IN (".$IDs.")";
// 		}
// 		return $query;
// 	}

// 	/**
// 	This function returns the base query, appended with the proper
// 	conditional to search for comments by their user IDs.
// 	*/
// 	static function base_user_ID_query($IDs = false)
// 	{
// 		$query = self::BASE_QUERY;
// 		if($IDs)
// 		{
// 			$query .= " AND C.`userid` IN (".$IDs.")";
// 		}
// 		return $query;
// 	}

// 	/**
// 	This function takes in a semicolon-delimited formatted list of comment IDs
// 	and returns a query which returns only the valid comment IDs.
// 	*/
// 	static function get_valid_IDs($IDs)
// 	{
// 		return "SELECT DISTINCT C.`postid` FROM `qa_posts` C WHERE C.`type` IN ('C','C_HIDDEN','C_QUEUED') AND C.`postid` IN (".$IDs.")";
// 	}

// 	/**
// 	This function takes in a semicolon-delimited formatted list of comment IDs
// 	and returns a query which returns only the parent IDs of the valid comment IDs.
// 	*/
// 	static function get_valid_parent_IDs($IDs)
// 	{
// 		return "SELECT DISTINCT C.`parentid` FROM `qa_posts` C WHERE C.`type` IN ('C','C_HIDDEN','C_QUEUED') AND C.`postid` IN (".$IDs.")";
// 	}

// 	/**
// 	This function takes in a semicolon-delimited formatted list of comment IDs
// 	and returns a query which returns only the user IDs of the valid comment IDs.
// 	*/
// 	static function get_valid_user_IDs($IDs)
// 	{
// 		return "SELECT DISTINCT C.`userid` FROM `qa_posts` C WHERE C.`type` IN ('C','C_HIDDEN','C_QUEUED') AND C.`postid` IN (".$IDs.")";
// 	}



// 	/**
// 	Read in a given path, and determine if it returns these types of objects.
// 	the $path should be set to $_SERVER['PATH_INFO'].
// 	The returned value will be either;
// 		A: false, if the given path does not return this data object, or
// 		B: a query to pass to the database in order to acquire the proper results.
// 	*/
// 	static function get_query($path)
// 	{
// 		$IDs = false;

// 		/**
// 		/comments
// 		*/
// 		if(preg_match("/^\/".self::ID."$/", $path))
// 		{
// 			return self::main_query();
// 		}

// 		/**
// 		/comments/{IDs}
// 		*/
// 		if(preg_match("/^\/".self::ID."\/(;*\s*[0-9]+\s*;*)+$/", $path))
// 		{
// 			$IDs = preg_replace("/\/".self::ID."\//", "", $path);
// 			return self::main_query(format_numeric_IDs($IDs));
// 		}

// 		/**
// 		/answers/{IDs}/comments
// 		*/
// 		if(preg_match("/^\/".Answer::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
// 		{
// 			$IDs = preg_replace("/^\/".Answer::ID."\//", "", $path);
// 			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
// 			return self::main_query(format_numeric_IDs($IDs), Answer::ID);
// 		}

// 		/**
// 		/questions/{IDs}/comments
// 		*/
// 		if(preg_match("/^\/".Question::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
// 		{
// 			$IDs = preg_replace("/^\/".Question::ID."\//", "", $path);
// 			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
// 			return self::main_query(format_numeric_IDs($IDs), Question::ID);
// 		}


// 		/**
// 		/posts/{IDs}/comments
// 		*/
// 		if(preg_match("/^\/".Post::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
// 		{
// 			$IDs = preg_replace("/^\/".Post::ID."\//", "", $path);
// 			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
// 			return self::main_query(format_numeric_IDs($IDs), Post::ID);
// 		}

// 		/**
// 		/users/{IDs}/comments
// 		*/
// 		if(preg_match("/^\/".User::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
// 		{
// 			$IDs = preg_replace("/^\/".User::ID."\//", "", $path);
// 			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
// 			return self::main_query(format_numeric_IDs($IDs), User::ID);
// 		}

// 		/**
// 		/tags/{IDs}/questions
// 		*/
// 		if(preg_match("/^\/".Tag::ID."\/(;*\s*[^;\s]+\s*;*)+\/".self::ID."$/", $path))
// 		{
// 			$IDs = preg_replace("/^\/".Tag::ID."\//", "", $path);
// 			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
// 			return self::main_query(format_alphabetic_IDs($IDs), Tag::ID);
// 		}

// 		/**
// 		If all of that failed, return false; this API call doesn't return
// 		this type of data object.
// 		*/
// 		return false;
// 	}

// 	/**
// 	This function is where most of the important stuff happens;
// 	this function forms the main MySQL query to be executed.
// 		IDs = the formatted IDs to be considered in the main query.
// 			defaults to empty.
// 		$ID_type = the specific type of the objects that IDs refers to.
// 			defaults to the same object type as the one being returned.
// 	*/
// 	static function main_query($IDs = false, $ID_type = self::ID)
// 	{
// 		$query = "";

// 		/**
// 		Get the main body of the query here.
// 		Validate that the IDs are referencing valid data objects.
// 		*/
// 		switch ($ID_type)
// 		{
// 			case self::ID:
// 				$query = self::base_ID_query($IDs);
// 				break;
			
// 			case Answer::ID:
// 				$query = self::base_parent_ID_query(Answer::get_valid_IDs($IDs));
// 				break;

// 			case Question::ID:
// 				$query = self::base_parent_ID_query(Question::get_valid_IDs($IDs));
// 				break;
			
// 			case Post::ID:
// 				$query = self::base_parent_ID_query(Post::get_valid_IDs($IDs));
// 				break;
			
// 			case User::ID:
// 				$query = self::base_user_ID_query(User::get_valid_IDs($IDs));
// 				break;
			
// 			case Tag::ID:
// 				$query = self::base_ID_query(Tag::get_generic_post_IDs($IDs));
			
// 			default:
// 				break;
// 		}

// 		$query = "SELECT C.* FROM (".$query.") C WHERE TRUE";
		
// 		$conditional_requirements = parse_conditions(self::get_boolean_vars(), self::get_datetime_vars(), self::get_integer_vars(), self::get_string_vars(), self::get_variable_mapping());

// 		if(!empty($conditional_requirements))
// 		{
// 			$query .= " AND ".$conditional_requirements;
// 		}

// /*
// 		if(isset($_GET['fromdate']))
// 		{
// 			$query .= " AND C.creation_date >= FROM_UNIXTIME(".$_GET['fromdate'].")";
// 		}

// 		if(isset($_GET['todate']))
// 		{
// 			$query .= " AND C.creation_date <= FROM_UNIXTIME(".$_GET['todate'].")";
// 		}
// */
// 		$sort_type = "creation";
// 		$sort_name = "C.`creation_date`";
// /*
// 		$var_to_col_mapping = array('creation' => 'C.`creation_date`', 'votes' => 'C.`score`');
		
// 		if(isset($_GET['sort']))
// 		{
// 			if(in_array($_GET['sort'], array('creation', 'votes')))
// 			{
// 				$sort_type = $_GET['sort'];
// 				$sort_name = $var_to_col_mapping[$sort_type];
// 			}
// 			else
// 			{
// 				return_error(400, 'sort', 'bad_parameter');
// 			}
// 		}

// 		$time_sorts = array("creation");
		
// 		if(isset($_GET['min']))
// 		{
// 			$min = $_GET['min'];
// 			if(in_array($sort_type, $time_sorts))
// 			{
// 				$min = "FROM_UNIXTIME(".$min.")";
// 			}
// 			$query .= " AND ".$sort_name." >= ".$min;
// 		}

// 		if(isset($_GET['max']))
// 		{
// 			$max = $_GET['max'];
// 			if(in_array($sort_type, $time_sorts))
// 			{
// 				$max = "FROM_UNIXTIME(".$max.")";
// 			}
// 			$query .= " AND ".$sort_name." <= ".$max;
// 		}
// */
// 		$query .= " ORDER BY ".$sort_name;
		
// 		$order = "desc";

// 		if(isset($_GET['order']) && in_array($_GET['order'], array("asc", "desc")))
// 		{
// 			$order = $_GET['order'];
// 		}
// 		$query .= " ".$order;

// 		/**
// 		Augment query with limit and offset operators.
// 		*/
// 		$query .= paginate_query();
		
// 		return $query;
// 	}


// }
	

?>