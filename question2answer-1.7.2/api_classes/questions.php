<?php
define("IN_MYBB", 1);
header("access-control-allow-origin: *");
require_once "./api_classes/util.php";
header('Content-Type: application/json');//JSON-formatting

/**
* The class for an answer.
* Data for this comes from: https://api.stackexchange.com/docs/types/answer
*/
class Question
{
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
		
		/** /
		if(isset($_GET["fromdate"]))
			$fromdate = process_date('fromdate');

		if(isset($_GET["todate"]))
			$todate = process_date('todate');
		/**/

		if(isset($_GET["min"]))
			$min = process_min_max($sort, 'min');

		if(isset($_GET["max"]))
			$max = process_min_max($sort, 'max');


		$var_to_col_mapping = array("ids" => $ID_TYPES[$id_type], "activity" => "updated", "creation" => "created");



		$query = "SELECT * FROM `qa_posts` ";
		
		if(true || isset($fromdate) || isset($todate) || isset($min) || isset($max) || isset($ids))
		{
			$use_and = true;
			$query .= " WHERE type = 'Q'";
			
			/** /
			if(isset($fromdate))
			{
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
			/**/

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