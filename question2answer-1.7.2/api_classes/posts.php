<?php
header("access-control-allow-origin: *");
header('Content-Type: application/json');

require_once "./api_classes/util.php";

require_once "./api_classes/comments.php";
require_once "./api_classes/tags.php";
require_once "./api_classes/users.php";


class Post
{
	
	var $body;
	var $creation_date;
	var $down_vote_count;
	var $last_activity_date;
	var $last_edit_date;
	var $owner;
	var $post_id;
	var $post_type;
	var $score;
	var $title;
	var $up_vote_count;

/**/

	static function get_variable_mapping()
	{
		return array(
			"body" => "P.`body`",
			"creation_date" => "P.`creation_date`",
			"down_vote_count" => "P.`down_vote_count`",
			"last_activity_date" => "P.`last_activity_date`",
			"last_edit_date" => "P.`last_edit_date`",
			"owner" => "P.`owner`",
			"post_id" => "P.`post_id`",
			"post_type" => "P.`post_type`",
			"score" => "P.`score`",
			"title" => "P.`title`",
			"up_vote_count" => "P.`up_vote_count`"
			);
	}

/**/

	static function get_boolean_vars()
	{
		return array();
	}

/**/

	static function get_datetime_vars()
	{
		return array(
			"creation_date",
			"last_activity_date",
			"last_edit_date"
		);
	}

/**/

	static function get_integer_vars()
	{
		return array(
			"down_vote_count",
			"owner",
			"post_id",
			"score",
			"up_vote_count"
		);
	}

/**/

	static function get_string_vars()
	{
		return array(
			"body",
			"post_type",
			"title"
		);
	}

/**/

	function __construct($row)
	{
		$this->body = $row['body'];
		$this->creation_date = $row['creation_date'];
		$this->down_vote_count = (integer) $row['down_vote_count'];
		$this->last_activity_date = $row['last_activity_date'];
		$this->last_edit_date = $row['last_edit_date'];
		$this->owner = (integer) $row['owner'];
		$this->post_id = (integer) $row['post_id'];
		$this->post_type = $row['post_type'];
		$this->score = (integer) $row['score'];
		$this->title = $row['title'];
		$this->up_vote_count = (integer) $row['up_vote_count'];
	}

	const ID = "posts";

	const BASE_QUERY = "SELECT
	P.`content`	AS	`body`,
	P.`created`	AS	`creation_date`,
	P.`downvotes`	AS	`down_vote_count`,
	IFNULL(P.`updated`,P.`created`)	AS	`last_activity_date`,
	P.`updated`	AS	`last_edit_date`,
	P.`userid`	AS	`owner`,
	P.`postid`	AS	`post_id`,
	CASE
		WHEN P.`type` IN ('A','A_HIDDEN','A_QUEUED')
			THEN 'answer'
		WHEN P.`type` IN ('Q','Q_HIDDEN','Q_QUEUED')
			THEN 'question'
		END	AS	`post_type`,
	P.`netvotes`	AS	`score`,
	P.`title`	AS	`title`,
	P.`upvotes`	AS	`up_vote_count`
FROM
	`qa_posts` P
WHERE
	P.`type` IN ('A','A_HIDDEN','A_QUEUED','Q','Q_HIDDEN','Q_QUEUED')";


	/**
	This function returns the base query, appended with the proper
	conditional to search for posts by their post IDs.
	*/
	static function base_ID_query($IDs = false)
	{
		$query = self::BASE_QUERY;
		if($IDs)
		{
			$query .= " AND P.`postid` IN (".$IDs.")";
		}
		return $query;
	}

	/**
	This function returns the base query, appended with the proper
	conditional to search for posts by their parent IDs.
	*/
	static function base_parent_ID_query($IDs = false)
	{
		$query = self::BASE_QUERY;
		if($IDs)
		{
			$query .= " AND P.`parentid` IN (".$IDs.")";
		}
		return $query;
	}

	/**
	This function returns the base query, appended with the proper
	conditional to search for posts by their user IDs.
	*/
	static function base_user_ID_query($IDs = false)
	{
		$query = self::BASE_QUERY;
		if($IDs)
		{
			$query .= " AND P.`userid` IN (".$IDs.")";
		}
		return $query;
	}


	/**
	This function takes in a semicolon-delimited formatted list of post IDs
	and returns a query which returns only the valid post IDs.
	*/
	static function get_valid_IDs($IDs)
	{
		return "SELECT DISTINCT P.`postid` FROM `qa_posts` P WHERE P.`type` IN ('A','A_HIDDEN','A_QUEUED','Q','Q_HIDDEN','Q_QUEUED') AND P.`postid` IN (".$IDs.")";
	}

	/**
	This function takes in a semicolon-delimited formatted list of post IDs
	and returns a query which returns only the parent IDs of the valid post IDs.
	*/
	static function get_valid_parent_IDs($IDs)
	{
		return "SELECT DISTINCT P.`parentid` FROM `qa_posts` P WHERE P.`type` IN ('A','A_HIDDEN','A_QUEUED','Q','Q_HIDDEN','Q_QUEUED') AND P.`postid` IN (".$IDs.")";
	}

	/**
	This function takes in a semicolon-delimited formatted list of post IDs
	and returns a query which returns only the user IDs of the valid post IDs.
	*/
	static function get_valid_user_IDs($IDs)
	{
		return "SELECT DISTINCT P.`userid` FROM `qa_posts` P WHERE P.`type` IN ('A','A_HIDDEN','A_QUEUED','Q','Q_HIDDEN','Q_QUEUED') AND P.`postid` IN (".$IDs.")";
	}

	/**
	Read in a given path, and determine if it returns these types of objects.
	the $path should be set to $_SERVER['PATH_INFO'].
	The returned value will be either;
		A: false, if the given path does not return this data object, or
		B: a query to pass to the database in order to acquire the proper results.
	*/
	static function get_query($path)
	{
		$IDs = false;

		/**
		/posts
		*/
		if(preg_match("/^\/".self::ID."$/", $path))
		{
			return self::main_query();
		}

		/**
		/posts/{IDs}
		*/
		if(preg_match("/^\/".self::ID."\/(;*\s*[0-9]+\s*;*)+$/", $path))
		{
			$IDs = preg_replace("/\/".self::ID."\//", "", $path);
			return self::main_query(format_numeric_IDs($IDs));
		}

		/**
		/comments/{IDs}/posts
		*/
		if(preg_match("/^\/".Comment::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
		{
			$IDs = preg_replace("/^\/".Comment::ID."\//", "", $path);
			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
			return self::main_query(format_numeric_IDs($IDs), Comment::ID);
		}

		/**
		/users/{IDs}/posts
		*/
		if(preg_match("/^\/".User::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
		{
			$IDs = preg_replace("/^\/".User::ID."\//", "", $path);
			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
			return self::main_query(format_numeric_IDs($IDs), User::ID);
		}

		/**
		/tags/{IDs}/posts
		*/
		if(preg_match("/^\/".Tag::ID."\/(;*\s*[^;\s]+\s*;*)+\/".self::ID."$/", $path))
		{
			$IDs = preg_replace("/^\/".Tag::ID."\//", "", $path);
			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
			return self::main_query(format_alphabetic_IDs($IDs), Tag::ID);
		}

		/**
		If all of that failed, return false; this API call doesn't return
		this type of data object.
		*/
		return false;
	}

	/**
	This function is where most of the important stuff happens;
	this function forms the main MySQL query to be executed.
		$IDs = the formatted IDs to be considered in the main query.
			defaults to false.
		$ID_type = the specific type of the objects that $IDs refers to.
			defaults to the same object type as the one being returned.
	*/
	static function main_query($IDs = false, $ID_type = self::ID)
	{
		$query = "";

		/**
		Get the main body of the query here.
		Validate that the IDs are referencing valid data objects.
		*/
		switch ($ID_type)
		{
			case self::ID:
				$query = self::base_ID_query($IDs);
				break;

			case Comment::ID:
				$query = self::base_ID_query(Comment::get_valid_parent_IDs($IDs));
				break;
			
			case User::ID:
				$query = self::base_user_ID_query(User::get_valid_IDs($IDs));
				break;
			
			case Tag::ID:
				$query = self::base_ID_query(Tag::get_generic_post_IDs($IDs));
				break;
			
			default:
				break;
		}

		$query = "SELECT P.* FROM (".$query.") P WHERE TRUE";
		
		$conditional_requirements = parse_conditions(self::get_boolean_vars(), self::get_datetime_vars(), self::get_integer_vars(), self::get_string_vars(), self::get_variable_mapping());

		if(!empty($conditional_requirements))
		{
			$query .= " AND ".$conditional_requirements;
		}
/*
		if(isset($_GET['fromdate']))
		{
			$query .= " AND P.creation_date >= FROM_UNIXTIME(".$_GET['fromdate'].")";
		}

		if(isset($_GET['todate']))
		{
			$query .= " AND P.creation_date <= FROM_UNIXTIME(".$_GET['todate'].")";
		}
*/
		$sort_type = "activity";
		$sort_name = "P.last_activity_date";
/*
		$var_to_col_mapping = array('activity' => 'P.last_activity_date', 'creation' => 'P.creation_date', 'votes' => 'P.score');
		
		if(isset($_GET['sort']))
		{
			if(in_array($_GET['sort'], array('activity', 'creation', 'votes')))
			{
				$sort_type = $_GET['sort'];
				$sort_name = $var_to_col_mapping[$sort_type];
			}
			else
			{
				return_error(400, 'sort', 'bad_parameter');
			}
		}

		$time_sorts = array("activity", "creation");
		
		if(isset($_GET['min']))
		{
			$min = $_GET['min'];
			if(in_array($sort_type, $time_sorts))
			{
				$min = "FROM_UNIXTIME(".$min.")";
			}
			$query .= " AND ".$sort_name." >= ".$min;
		}

		if(isset($_GET['max']))
		{
			$max = $_GET['max'];
			if(in_array($sort_type, $time_sorts))
			{
				$max = "FROM_UNIXTIME(".$max.")";
			}
			$query .= " AND ".$sort_name." <= ".$max;
		}
*/
		$query .= " ORDER BY ".$sort_name;
		
		$order = "desc";

		if(isset($_GET['order']) && in_array($_GET['order'], array("asc", "desc")))
		{
			$order = $_GET['order'];
		}
		$query .= " ".$order;

		/**
		Augment query with limit and offset operators.
		*/
		$query .= paginate_query();
		
		return $query;
	}


/** /
	function __get($name)
	{
		switch ($name)
			{
				case "user_type" :
				echo $this->$user_type;
				break;
			}
	}

/**/

}
	

?>