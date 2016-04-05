<?php
// header("access-control-allow-origin: *");
// header('Content-Type: application/json');

// require_once "./api_classes/util.php";

// require_once "./api_classes/answers.php";
// require_once "./api_classes/comments.php";
// require_once "./api_classes/posts.php";
// require_once "./api_classes/questions.php";

// /**
// The class for a Tag.
// Data for this comes from: https://api.stackexchange.com/docs/types/tag
// */
// class Tag
// {
// 	/**
// 	These are the variable names of the data object.
// 	*/
// 	var $count;
// 	var $last_activity_date;
// 	var $name;

// 	/**
// 	This is the constructor for an instance of this object.
// 	The input parameter should be a row returned from the MySQL query.
// 	*/
// 	function __construct($row)
// 	{
// 		$this->count = $row['count'];
// 		$this->last_activity_date = $row['last_activity_date'];
// 		$this->name = $row['name'];

// 	}

// 	/**
// 	This is the identifier for tag-type objects.
// 	*/
// 	const ID = "tags";

// 	/**
// 	This is the base query for getting all the data for tags from
// 	the qa_words and qa_posttags tables.
// 	*/
// 	const BASE_QUERY = "SELECT
// 	W.`tagcount` AS `count`,
// 	MAX(PT.`postcreated`) AS `last_activity_date`,
// 	W.`word` AS `name`
// FROM
// 	`qa_words` W
// JOIN
// 	`qa_posttags` PT
// ON
// 	W.`wordid` = PT.`wordid`
// GROUP BY
// 	W.`word`";


// 	/**
// 	This function returns the base query, appended with the proper
// 	conditional to search for tags by their tag IDs.
// 	*/
// 	static function base_ID_query($IDs = false)
// 	{
// 		$query = self::BASE_QUERY;
// 		if($IDs)
// 		{
// 			$query .= " HAVING W.`word` IN (".$IDs.")";
// 		}
// 		return $query;
// 	}

// 	/**
// 	This function returns the base query, appended with the proper
// 	conditional to search for tags by the generic post (question, answer, or comment) IDs that they appear in.
// 	*/
// 	static function base_generic_post_ID_query($IDs = false)
// 	{
// 		return self::base_ID_query(self::get_tag_IDs_by_generic_post_IDs($IDs));
// 	}

// 	/**
// 	This function takes in a comma-delimited formatted list of tag IDs
// 	and returns a query which returns all generic post IDs that the tags are attached to.
// 	*/
// 	static function get_generic_post_IDs($IDs = false)
// 	{
// 		return "SELECT DISTINCT
// 	TW.`postid`
// FROM
// 	`qa_tagwords` TW
// JOIN
// 	`qa_words` W
// ON
// 	TW.`wordid` = W.`wordid`
// WHERE
// 	W.`word` IN (".$IDs.")";
// 	}

// 	/**
// 	Takes In: a formatted list of any type of postid.
// 	spits out: all tag IDs related to the postids.
// 	*/
// 	static function get_tag_IDs_by_generic_post_IDs($IDs = false)
// 	{
// 		return "SELECT DISTINCT
// 	W.`word`
// FROM
// 	`qa_words` W
// JOIN
// 	`qa_tagwords` TW
// ON
// 	W.`wordid` = TW.`wordid`
// WHERE
// 	TW.`postid` IN (".$IDs.")";
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
// 		/tags
// 		*/
// 		if(preg_match("/^\/".self::ID."$/", $path))
// 		{
// 			return self::main_query();
// 		}

// 		/**
// 		/tags/{IDs}
// 		*/
// 		if(preg_match("/^\/".self::ID."\/(;*\s*[^;\s]+\s*;*)+$/", $path))
// 		{
// 			$IDs = preg_replace("/^\/".self::ID."\//", "", $path);
// 			return self::main_query(format_alphabetic_IDs($IDs));
// 		}

// 		/**
// 		/questions/{IDs}/tags
// 		*/
// 		if(preg_match("/^\/".Question::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
// 		{
// 			$IDs = preg_replace("/^\/".Question::ID."\//", "", $path);
// 			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
// 			return self::main_query(format_numeric_IDs($IDs), Question::ID);
// 		}

// 		/**
// 		/comments/{IDs}/tags
// 		*/
// 		if(preg_match("/^\/".Comment::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
// 		{
// 			$IDs = preg_replace("/^\/".Comment::ID."\//", "", $path);
// 			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
// 			return self::main_query(format_numeric_IDs($IDs), Comment::ID);
// 		}

// 		/**
// 		/answers/{IDs}/tags
// 		*/
// 		if(preg_match("/^\/".Answer::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
// 		{
// 			$IDs = preg_replace("/^\/".Answer::ID."\//", "", $path);
// 			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
// 			return self::main_query(format_numeric_IDs($IDs), Answer::ID);
// 		}

// 		/**
// 		/posts/{IDs}/tags
// 		*/
// 		if(preg_match("/^\/".Post::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
// 		{
// 			$IDs = preg_replace("/^\/".Post::ID."\//", "", $path);
// 			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
// 			return self::main_query(format_numeric_IDs($IDs), Post::ID);
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
			
// 			case Question::ID:
// 				$query = self::base_generic_post_ID_query(Question::get_valid_IDs($IDs));
// 				break;

// 			case Comment::ID:
// 				$query = self::base_generic_post_ID_query(Comment::get_valid_IDs($IDs));
// 				break;
			
// 			case Answer::ID:
// 				$query = self::base_generic_post_ID_query(Answer::get_valid_IDs($IDs));
// 				break;
			
// 			case Post::ID:
// 				$query = self::base_generic_post_ID_query(Post::get_valid_IDs($IDs));
// 			default:
// 				break;
// 		}

// 		$query = "SELECT Tags.* FROM (".$query.") Tags WHERE TRUE";
		
// 		if(isset($_GET['fromdate']))
// 		{
// 			$query .= " AND Tags.`last_activity_date` >= FROM_UNIXTIME(".$_GET['fromdate'].")";
// 		}

// 		if(isset($_GET['todate']))
// 		{
// 			$query .= " AND Tags.`last_activity_date` <= FROM_UNIXTIME(".$_GET['todate'].")";
// 		}

// 		if(isset($_GET['inname']))
// 		{
// 			$inname = $_GET['inname'];
// 			$query .= " AND Tags.`name` LIKE '%".$inname."%'";
// 		}

// 		$sort_type = "popular";
// 		$sort_name = "Tags.`count`";

// 		$var_to_col_mapping = array('popular' => 'Tags.`count`', 'activity' => 'Tags.`last_activity_date`', 'name' => 'Tags.`name`');
		
// 		if(isset($_GET['sort']))
// 		{
// 			if(in_array($_GET['sort'], array('popular', 'activity', 'name')))
// 			{
// 				$sort_type = $_GET['sort'];
// 				$sort_name = $var_to_col_mapping[$sort_type];
// 			}
// 			else
// 			{
// 				return_error(400, 'sort', 'bad_parameter');
// 			}
// 		}

// 		$time_sorts = array("activity");
// 		$string_sorts = array("name");

// 		if(isset($_GET['min']))
// 		{
// 			$min = $_GET['min'];
// 			if(in_array($sort_type, $time_sorts))
// 			{
// 				$min = "FROM_UNIXTIME(".$min.")";
// 			}
// 			elseif(in_array($sort_type, $string_sorts))
// 			{
// 				$min = "'".$min."'";
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
// 			elseif(in_array($sort_type, $string_sorts))
// 			{
// 				$max = "'".$max."'";
// 			}
// 			$query .= " AND ".$sort_name." <= ".$max;
// 		}

// 		$query .= " ORDER BY ".$sort_name;
		
// 		$order = "desc";

// 		if(isset($_GET['order']) && in_array($_GET['order'], array("asc", "desc")))
// 		{
// 			$order = $_GET['order'];
// 		}
// 		$query .= " ".$order;

// 		/**
// 		The default pagesize (by StackExchange standards) is 30.
// 		*/
// 		$pagesize = 30;

// 		/**
// 		Process custom-specified pagesize, if defined.
// 		*/
// 		if(isset($_GET['pagesize']))
// 		{
// 			/**
// 			Determine pagesize validity
// 			*/
// 			if(!is_numeric($_GET['pagesize']))
// 				return_error(400, 'pagesize', 'bad_parameter');
		
// 			/**
// 			1 <= pagesize <= 100
// 			*/
// 			$pagesize = max(1, min(100, $_GET["pagesize"]));
// 		}

// 		/**
// 		The default page (by StackExchange standards) is 1.
// 		*/
// 		$page = 1;
	
// 		/**
// 		Process custom-specified page, if defined.
// 		*/
// 		if(isset($_GET["page"]))
// 		{
// 			if(!is_numeric($_GET['page']))
// 				return_error(400, 'page', 'bad_parameter');

// 			$page = $_GET["page"];
// 		}

// 		/**
// 		Augment query with limit and offset operators.
// 		*/
// 		$query .= " LIMIT ".$pagesize." OFFSET ".($page - 1) * $pagesize;

// 		return $query;
// 	}
// }
	

?>