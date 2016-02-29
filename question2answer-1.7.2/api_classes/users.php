<?php
header("access-control-allow-origin: *");
header('Content-Type: application/json');

require_once "./api_classes/util.php";

require_once "./api_classes/answers.php";
require_once "./api_classes/comments.php";
require_once "./api_classes/posts.php";
require_once "./api_classes/questions.php";

/**
* The class for a user.
* Data for this comes from: https://api.stackexchange.com/docs/types/user
*/
class User
{

	var $accept_rate;
	var $answer_count;
	var $badge_counts;
	var $creation_date;
	var $display_name;
	var $down_vote_count;
	var $last_access_date;
	var $last_modified_date;
	var $link;
	var $question_count;
	var $reputation;
	var $up_vote_count;
	var $user_id;

	function __construct($row)
	{
		$this->accept_rate = $row['accept_rate'];
		$this->answer_count = $row['answer_count'];
		$this->badge_counts = $row['badge_counts'];
		$this->creation_date = $row['creation_date'];
		$this->display_name = $row['display_name'];
		$this->down_vote_count = $row['down_vote_count'];
		$this->last_access_date = $row['last_access_date'];
		$this->last_modified_date = $row['last_modified_date'];
		$this->link = $row['link'];
		$this->question_count = $row['question_count'];
		$this->reputation = $row['reputation'];
		$this->up_vote_count = $row['up_vote_count'];
		$this->user_id = $row['user_id'];
	}

	const ID = "users";
	
	const BASE_QUERY = "SELECT
	P.`aselecteds`	AS	`accept_rate`,
	P.`aposts`	AS	`answer_count`,
	B.`badge_counts`	AS	`badge_counts`,
	U.`created`	AS	`creation_date`,
	U.`handle`	AS	`display_name`,
	P.`downvoteds`	AS	`down_vote_count`,
	U.`loggedin`	AS	`last_access_date`,
	U.`written`	AS	`last_modified_date`,
	CONCAT('http://may1639.sd.ece.iastate.edu/question2answer-1.7.2/index.php/user/', U.`handle`)	AS	`link`,
	P.`qposts`	AS	`question_count`,
	P.`points`	AS	`reputation`,
	P.`upvoteds`	AS	`up_vote_count`,
	U.`userid`	AS	`user_id`
FROM
	`qa_users` U
JOIN
	`qa_userpoints` P
ON
	U.`userid` = P.`userid`
JOIN
	(SELECT
     	COUNT(B.`badge_slug`) AS `badge_counts`,
     	B.`user_id`
     FROM
     	`qa_userbadges` B
     GROUP BY
     	B.`user_id`) B
ON
	B.`user_id` = U.`userid`
WHERE
	TRUE";

	/**
	This function returns the base query, appended with the proper
	conditional to search for users by their user IDs.
	*/
	static function base_ID_query($IDs = false)
	{
		$query = self::BASE_QUERY;
		if($IDs)
		{
			$query .= " AND U.`userid` IN (".$IDs.")";
		}
		return $query;
	}
	
	/**
	This function takes in a semicolon-delimited formatted list of user IDs
	and returns a query which returns only the valid user IDs.
	*/
	static function get_valid_IDs($IDs)
	{
		return "SELECT DISTINCT U.`userid` FROM `qa_users` U WHERE U.`userid` IN (".$IDs.")";
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
		/users
		*/
		if(preg_match("/^\/".self::ID."$/", $path))
		{
			return self::main_query();
		}

		/**
		/users/{IDs}
		*/
		if(preg_match("/^\/".self::ID."\/(;*\s*[0-9]+\s*;*)+$/", $path))
		{
			$IDs = preg_replace("/\/".self::ID."\//", "", $path);
			return self::main_query(format_numeric_IDs($IDs));
		}

		/**
		/answers/{IDs}/users
		*/
		if(preg_match("/^\/".Answer::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
		{
			$IDs = preg_replace("/^\/".Answer::ID."\//", "", $path);
			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
			return self::main_query(format_numeric_IDs($IDs), Answer::ID);
		}

		/**
		/comments/{IDs}/users
		*/
		if(preg_match("/^\/".Comment::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
		{
			$IDs = preg_replace("/^\/".Comment::ID."\//", "", $path);
			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
			return self::main_query(format_numeric_IDs($IDs), Comment::ID);
		}

		/**
		/questions/{IDs}/users
		*/
		if(preg_match("/^\/".Question::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
		{
			$IDs = preg_replace("/^\/".Question::ID."\//", "", $path);
			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
			return self::main_query(format_numeric_IDs($IDs), Question::ID);
		}

		/**
		/posts/{IDs}/users
		*/
		if(preg_match("/^\/".Post::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
		{
			$IDs = preg_replace("/^\/".Post::ID."\//", "", $path);
			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
			return self::main_query(format_numeric_IDs($IDs), Post::ID);
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

			case Answer::ID:
				$query = self::base_ID_query(Answer::get_valid_user_IDs($IDs));
				break;
			
			case Comment::ID:
				$query = self::base_ID_query(Comment::get_valid_user_IDs($IDs));
				break;
			
			case Question::ID:
				$query = self::base_ID_query(Question::get_valid_user_IDs($IDs));
				break;

			case Post::ID:
				$query = self::base_ID_query(Post::get_valid_user_IDs($IDs));
				break;

			default:
				break;
		}

		$query = "SELECT U.* FROM (".$query.") U WHERE TRUE";
		
		if(isset($_GET['fromdate']))
		{
			$query .= " AND U.creation_date >= FROM_UNIXTIME(".$_GET['fromdate'].")";
		}

		if(isset($_GET['todate']))
		{
			$query .= " AND U.creation_date <= FROM_UNIXTIME(".$_GET['todate'].")";
		}

		if(isset($_GET['inname']))
		{
			$inname = $_GET['inname'];
			$query .= " AND U.`display_name` LIKE '%".$inname."%'";
		}

		$sort_type = "reputation";
		$sort_name = "U.`reputation`";

		$var_to_col_mapping = array('reputation' => 'U.`reputation`', 'creation' => 'U.`creation_date`', 'name' => 'U.`display_name`', 'modified' => 'U.`last_modified_date`');
		
		if(isset($_GET['sort']))
		{
			if(in_array($_GET['sort'], array('reputation', 'creation', 'name', 'modified')))
			{
				$sort_type = $_GET['sort'];
				$sort_name = $var_to_col_mapping[$sort_type];
			}
			else
			{
				return_error(400, 'sort', 'bad_parameter');
			}
		}

		$time_sorts = array("creation", "modified");
		$string_sorts = array("name");

		if(isset($_GET['min']))
		{
			$min = $_GET['min'];
			if(in_array($sort_type, $time_sorts))
			{
				$min = "FROM_UNIXTIME(".$min.")";
			}
			elseif(in_array($sort_type, $string_sorts))
			{
				$min = "'".$min."'";
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
			elseif(in_array($sort_type, $string_sorts))
			{
				$max = "'".$max."'";
			}
			$query .= " AND ".$sort_name." <= ".$max;
		}

		$query .= " ORDER BY ".$sort_name;
		
		$order = "desc";

		if(isset($_GET['order']) && in_array($_GET['order'], array("asc", "desc")))
		{
			$order = $_GET['order'];
		}
		$query .= " ".$order;

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
		$query .= " LIMIT ".$pagesize." OFFSET ".($page - 1) * $pagesize;

		return $query;
	}


/*
	static function get_query($ids, $id_type='user')
	{
		global $ID_TYPES;
		
		if(!in_array($ID_TYPES[$id_type], array("userid")))
			$id_type = 'user';

		$order = process_order();
	
		$sort = process_sort(array("reputation", "creation", "name", "modified"));

		
		if(isset($_GET["fromdate"]))
			$fromdate = process_date('fromdate');

		if(isset($_GET["todate"]))
			$todate = process_date('todate');
		
		if(isset($_GET["min"]))
		{
			$min = process_min_max($sort, 'min');
			if(in_array($sort, array("creation", "modified")))
			{
				$min = "FROM_UNIXTIME(".$min.")";
			}
		}

		if(isset($_GET["max"]))
		{
			$max = process_min_max($sort, 'max');
			if(in_array($sort, array("creation", "modified")))
			{
				$max = "FROM_UNIXTIME(".$max.")";
			}
		}

		if(isset($_GET["inname"]))
			$inname = process_inname();

		$var_to_col_mapping = array("ids" => $ID_TYPES[$id_type], "reputation" => "qa_userpoints.points", "creation" => "created", "name" => "handle", "modified" => "written");
	
		$query = "SELECT qa_users.*, qa_userpoints.points FROM qa_users, qa_userpoints";
		

		$use_and = true;
		$query .= " WHERE qa_users.userid = qa_userpoints.userid";
		if(isset($fromdate) || isset($todate) || isset($min) || isset($max) || isset($inname) || isset($ids))
		{	
			if(isset($fromdate))
			{
				if($use_and)
					$query .= " AND";
				$query .= " qa_users.".$var_to_col_mapping["creation"]." > ".$fromdate;
				$use_and = true;
			}

			if(isset($todate))
			{
				if($use_and)
					$query .= " AND";
				$query .= " qa_users.".$var_to_col_mapping["creation"]." < ".$todate;
				$use_and = true;
			}
			
			if(isset($min))
			{
				if($use_and)
					$query .= " AND";
				$query .= " qa_users.".$var_to_col_mapping[$sort]." > ";
				if($sort == "name")
					$query .= "'".$min."'";
				else
					$query .= $min;
				$use_and = true;
			}

			if(isset($max))
			{
				if($use_and)
					$query .= " AND";
				$query .= " qa_users.".$var_to_col_mapping[$sort]." < ";
				if($sort == "name")
					$query .= "'".$max."'";
				else
					$query .= $max;
				$use_and = true;
			}

			if(isset($inname))
			{
				if($use_and)
					$query .= " AND";
				$query .= " qa_users.".$var_to_col_mapping["name"]." LIKE '%".$inname."%'";
				$use_and = true;
			}

			if(isset($ids) && (0 < count($ids)))
			{
				if($use_and)
					$query .= " AND";
				$query .= " qa_users.".$var_to_col_mapping["ids"]." IN (";
				for ($index=0; $index < count($ids); $index++)
				{
					if(0 < $index)
					{
						$query .= ", ";
					}
					$query .= "".$ids[$index];
				}
				$query .= ")";
			}
		}
		
		$query .= " ORDER BY ".$var_to_col_mapping[$sort];
		
		if($order == "desc")
			$query .= " DESC";
		
		return $query;
	}

/**/
}
	

?>