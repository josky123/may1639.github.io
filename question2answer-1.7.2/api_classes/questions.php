<?php
define("IN_MYBB", 1);
header("access-control-allow-origin: *");
require_once "./api_classes/util.php";
require_once "./api_classes/answers.php";
require_once "./api_classes/tags.php";
require_once "./api_classes/comments.php";
require_once "./api_classes/users.php";

header('Content-Type: application/json');//JSON-formatting

/**
* The class for an answer.
* Data for this comes from: https://api.stackexchange.com/docs/types/answer
*/
class Question
{

	static function question_id_query($IDs = false)
	{
		$query = "SELECT Q.* FROM `qa_posts` Q WHERE Q.type = 'Q'";
		if(!empty($IDs))
		{
			$query .= " AND Q.postid IN (".implode(",", explode(";", $IDs)).")";
		}
		return $query;
	}

	static function answer_id_query($IDs = false)
	{
		$query = "SELECT DISTINCT Q.* FROM qa_posts Q, qa_posts A WHERE A.type = 'A' AND Q.type = 'Q' AND A.parentid = Q.postid";
		if(!empty($IDs))
		{
			$query .= " AND A.postid IN (".implode(",", explode(";", $IDs)).")";
		}
		return $query;
	}

	static function main_query($IDs = false, $ID_type = "Question")
	{
		$query = "";

		switch ($ID_type)
		{
			case 'Question':
				$query = Question::question_id_query($IDs);
				break;
			
			case 'Answer':
				$query = Question::answer_id_query($IDs);
				break;
			
			default:

				break;
		}
		
		
		if(isset($_GET['fromdate']))
		{
			$query .= " AND Q.created > FROM_UNIXTIME(".$_GET['fromdate'].")";
		}


		if(isset($_GET['todate']))
		{
			$query .= " AND Q.created < FROM_UNIXTIME(".$_GET['todate'].")";
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
		$sort_name = "Q.updated";
		$var_to_col_mapping = array('activity' => 'Q.updated', 'creation' => 'Q.created', 'votes' => 'Q.netvotes');
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
		if (isset($_GET['min']))
		{
			$min = $_GET['min'];
			if(in_array($sort_type, $time_sorts))
			{
				$min = "FROM_UNIXTIME(".$min.")";
			}
			$query .= " AND ".$sort_name." > ".$min;
		}

		if (isset($_GET['max']))
		{
			$max = $_GET['max'];
			if(in_array($sort_type, $time_sorts))
			{
				$max = "FROM_UNIXTIME(".$max.")";
			}
			$query .= " AND ".$sort_name." < ".$max;
		}

		$query .= " ORDER BY ".$sort_name;
		if(isset($_GET['order']) && in_array($_GET['order'], array("asc", "desc")))
		{
			$query .= " ".$_GET['order'];
		}
		return $query;
	}

	/** /
	var $accepted_answer_id;
	var $answer_count;
	var $answers;
	/**/
	var $body;
	/** /
	var $body_markdown;
	var $bounty_amount;
	var $bounty_closes_date;
	var $bounty_user;
	var $can_close;
	var $can_flag;
	var $close_vote_count;
	var $closed_date;
	var $closed_details;
	var $closed_reason;
	var $comment_count;
	var $comments;
	var $community_owned_date;
	/**/
	var $creation_date;
	/** /
	var $delete_vote_count;
	var $down_vote_count;
	var $downvoted;
	var $favorite_count;
	var $favorited;
	var $is_answered;
	var $last_activity_date;
	/**/
	var $last_edit_date;
	var $last_editor;
	/** /
	var $link;
	var $locked_date;
	var $migrated_from;
	var $migrated_to;
	var $notice;
	/**/
	var $owner;
	/** /
	var $protected_date;
	/**/
	var $question_id;
	/** /
	var $reopen_vote_count;
	var $score;
	var $share_link;
	var $tags;
	/**/
	var $title;
	/** /
	var $up_vote_count;
	var $upvoted;
	/**/
	var $view_count;
	/**/

	function __construct($row)
	{
		$this->body = $row['content'];
		$this->creation_date = $row['created'];
		//$this->last_activity_date = $row['lastpost'];
		$this->last_edit_date = $row['updated'];
		$this->last_editor = $row['lastuserid'];
		$this->owner = $row['userid'];
		$this->question_id = $row['postid'];
		$this->title = $row['title'];
		$this->view_count = $row['views'];
	}

	static function get_query($ids, $id_type='question')
	{
		global $ID_TYPES;

		if(!in_array($ID_TYPES[$id_type], array("postid, userid")))
			$id_type = 'question';
		
		$order = process_order();
		
		$sort = process_sort(array("activity", "creation"));
		
		if(isset($_GET["fromdate"]))
			$fromdate = process_date('fromdate');

		if(isset($_GET["todate"]))
			$todate = process_date('todate');
		
		if(isset($_GET["min"]))
		{
			$min = process_min_max($sort, 'min');
			if(in_array($sort, array("activity", "creation")))
			{
				$min = "FROM_UNIXTIME(".$min.")";
			}
		}

		if(isset($_GET["max"]))
		{
			$max = process_min_max($sort, 'max');
			if(in_array($sort, array("activity", "creation")))
			{
				$max = "FROM_UNIXTIME(".$max.")";
			}
		}


		$var_to_col_mapping = array("ids" => $ID_TYPES[$id_type], "activity" => "updated", "creation" => "created");



		$query = "SELECT * FROM `qa_posts` ";
		
		$use_and = true;
			$query .= " WHERE type = 'Q'";
		if(isset($fromdate) || isset($todate) || isset($min) || isset($max) || isset($ids))
		{
			if(isset($fromdate))
			{
				if($use_and)
					$query .= " AND";
				$query .= " ".$var_to_col_mapping["creation"]." > ".$fromdate;
				$use_and = true;
			}

			if(isset($todate))
			{
				if($use_and)
					$query .= " AND";
				$query .= " ".$var_to_col_mapping["creation"]." < ".$todate;
				$use_and = true;
			}

			if(isset($min))
			{
				if($use_and)
					$query .= " AND";
				$query .= " ".$var_to_col_mapping[$sort]." > ".$min;
				$use_and = true;
			}

			if(isset($max))
			{
				if($use_and)
					$query .= " AND";
				$query .= " ".$var_to_col_mapping[$sort]." < ".$max;
				$use_and = true;
			}

			if(isset($ids) && (0 < count($ids)))
			{
				if($use_and)
					$query .= " AND";
				$query .= " ".$var_to_col_mapping["ids"]." IN (";
				for($index=0; $index < count($ids); $index++)
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

	static function func($path)
	{

		echo "\n";
		var_dump($_SERVER);
		echo "\n";
		var_dump($_GET);
		echo "\n";
		var_dump($path);
		echo "\n";
	}

	/**/



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