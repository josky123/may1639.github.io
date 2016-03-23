<?php
header("access-control-allow-origin: *");
header('Content-Type: application/json');

require_once "./api_classes/util.php";

require_once "./api_classes/answers.php";
require_once "./api_classes/comments.php";
require_once "./api_classes/tags.php";
require_once "./api_classes/users.php";


/**
The class for a question.
This datatype was inspired by: https://api.stackexchange.com/docs/types/question
*/
class Question
{

	/**
	These are the variable names of the data object.
	*/
	var $question_id;
	var $owner;
	var $title;
	var $body;
	var $tags;
	var $view_count;
	var $score;
	var $up_vote_count;
	var $down_vote_count;
	var $answer_count;
	var $is_answered;
	var $accepted_answer_id;
	var $creation_date;
	var $last_activity_date;
	var $last_edit_date;

/**/

	static function get_variable_mapping()
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

/**/

	static function get_boolean_vars()
	{
		return array(
			"is_answered"
		);
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

/**/

	static function get_string_vars()
	{
		return array(
			"title",
			"body",
			"tags"
		);
	}

/**/


	/**
	This is the constructor for an instance of this object.
	The input parameter should be a row returned from the MySQL query.
	*/
	function __construct($row)
	{
		$this->accepted_answer_id = (integer) $row['accepted_answer_id'];
		$this->answer_count = (integer) $row['answer_count'];
		$this->body = $row['body'];
		$this->creation_date = $row['creation_date'];
		$this->down_vote_count = (integer) $row['down_vote_count'];
		$this->is_answered = (boolean) $row['is_answered'];
		$this->last_activity_date = $row['last_activity_date'];
		$this->last_edit_date = $row['last_edit_date'];
		$this->owner = (integer) $row['owner'];
		$this->question_id = (integer) $row['question_id'];
		$this->score = (integer) $row['score'];
		$this->tags = $row['tags'];
		$this->title = $row['title'];
		$this->up_vote_count = (integer) $row['up_vote_count'];
		$this->view_count = (integer) $row['view_count'];
	}

	/**
	This is the identifier for question-type objects.
	*/
	const ID = "questions";

	/**
	This is the base query for getting all the data for questions from
	the qa_posts table.
	*/
	const BASE_QUERY = "SELECT
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
	Q.`type` IN ('Q','Q_HIDDEN','Q_QUEUED')";


	/**
	This function returns the base query, appended with the proper
	conditional to search for questions by their post IDs.
	*/
	static function base_ID_query($IDs = false)
	{
		$query = self::BASE_QUERY;
		if($IDs)
		{
			$query .= " AND Q.`postid` IN (".$IDs.")";
		}
		return $query;
	}

	/**
	This function returns the base query, appended with the proper
	conditional to search for questions by their user IDs.
	*/
	static function base_user_ID_query($IDs = false)
	{
		$query = self::BASE_QUERY;
		if($IDs)
		{
			$query .= " AND Q.`userid` IN (".$IDs.")";
		}
		return $query;
	}

	/**
	This function takes in a comma-delimited formatted list of question IDs
	and returns a query which returns only the valid question IDs.
	*/
	static function get_valid_IDs($IDs)
	{
		return "SELECT DISTINCT Q.`postid` FROM `qa_posts` Q WHERE Q.`type` IN ('Q','Q_HIDDEN','Q_QUEUED') AND Q.`postid` IN (".$IDs.")";
	}

	/**
	This function takes in a comma-delimited formatted list of question IDs
	and returns a query which returns only the parent IDs of the valid question IDs.
	*/
	static function get_valid_parent_IDs($IDs)
	{
		return "SELECT DISTINCT Q.`parentid` FROM `qa_posts` Q WHERE Q.`type` IN ('Q','Q_HIDDEN','Q_QUEUED') AND Q.`postid` IN (".$IDs.")";
	}

	/**
	This function takes in a comma-delimited formatted list of question IDs
	and returns a query which returns only the user IDs of the valid question IDs.
	*/
	static function get_valid_user_IDs($IDs)
	{
		return "SELECT DISTINCT Q.`userid` FROM `qa_posts` Q WHERE Q.`type` IN ('Q','Q_HIDDEN','Q_QUEUED') AND Q.`postid` IN (".$IDs.")";
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
		/questions
		*/
		if(preg_match("/^\/".self::ID."$/", $path))
		{
			return self::main_query();
		}

		/**
		/questions/{IDs}
		*/
		if(preg_match("/^\/".self::ID."\/(;*\s*[0-9]+\s*;*)+$/", $path))
		{
			$IDs = preg_replace("/\/".self::ID."\//", "", $path);
			return self::main_query(format_numeric_IDs($IDs));
		}

		/**
		/answers/{IDs}/questions
		*/
		if(preg_match("/^\/".Answer::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
		{
			$IDs = preg_replace("/^\/".Answer::ID."\//", "", $path);
			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
			return self::main_query(format_numeric_IDs($IDs), Answer::ID);
		}

		/**
		/comments/{IDs}/questions
		*/
		if(preg_match("/^\/".Comment::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
		{
			$IDs = preg_replace("/^\/".Comment::ID."\//", "", $path);
			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
			return self::main_query(format_numeric_IDs($IDs), Comment::ID);
		}

		/**
		/users/{IDs}/questions
		*/
		if(preg_match("/^\/".User::ID."\/(;*\s*[0-9]+\s*;*)+\/".self::ID."$/", $path))
		{
			$IDs = preg_replace("/^\/".User::ID."\//", "", $path);
			$IDs = preg_replace("/\/".self::ID."$/", "", $IDs);
			return self::main_query(format_numeric_IDs($IDs), User::ID);
		}

		/**
		/tags/{IDs}/questions
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
		IDs = the formatted IDs to be considered in the main query.
			defaults to empty.
		$ID_type = the specific type of the objects that IDs refers to.
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
				$query = self::base_ID_query(Answer::get_valid_parent_IDs($IDs));
				break;

			case Comment::ID:
				$query = self::base_ID_query(Comment::get_valid_parent_IDs($IDs));
				break;
			
			case User::ID:
				$query = self::base_user_ID_query(User::get_valid_IDs($IDs));
				break;
			
			case Tag::ID:
				$query = self::base_ID_query(Tag::get_generic_post_IDs($IDs));
			default:
				break;
		}

		$query = "SELECT Q.* FROM (".$query.") Q WHERE TRUE";
		
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
		// parse_conditions(self::get_boolean_vars(), self::get_datetime_vars(), self::get_integer_vars(), self::get_string_vars(), self::get_variable_mapping());
		// exit(0);
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


		if(isset($_GET['fromdate']))
		{
			$query .= " AND Q.creation_date >= FROM_UNIXTIME(".$_GET['fromdate'].")";
		}

		if(isset($_GET['todate']))
		{
			$query .= " AND Q.creation_date <= FROM_UNIXTIME(".$_GET['todate'].")";
		}
		
		if(isset($_GET['tagged']))
		{
			$tagged = explode(";", $_GET['tagged']);
			foreach($tagged as $tag)
			{
				$query .= " AND Q.tags REGEXP \"(^|,)".$tag."($|,)\"";
			}
		}

		$sort_type = "activity";
		$sort_name = "Q.last_activity_date";

		$var_to_col_mapping = array('activity' => 'Q.last_activity_date', 'creation' => 'Q.creation_date', 'votes' => 'Q.score');
		
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
}
	

?>