<?php
define("IN_MYBB", 1);
header("access-control-allow-origin: *");
require_once "./api_classes/util.php";
header('Content-Type: application/json');//JSON-formatting

/**
* The class for an answer.
* Data for this comes from: https://api.stackexchange.com/docs/types/answer
*/
class Post
{
	/** /
	var $accepted;
	/**/
	var $post_id;
	/** /
	var $awarded_bounty_amount;
	var $awarded_bounty_users;
	/**/
	var $body;
	/** /
	var $body_markdown;
	var $can_flag;
	var $comment_count;
	var $comments;
	var $community_owned_date;
	/**/
	var $creation_date;
	/** /
	var $down_vote_count;
	var $is_accepted;
	/**/
	var $last_activity_date;
	var $last_edit_date;
	var $last_editor;
	/** /
	var $link;
	var $locked_date;
	/**/
	var $owner;
	var $parent_post_id;
	/** /
	var $score;
	var $share_link;
	var $tags;
	/**/
	var $title;
	/** /
	var $up_vote_count;
	var $upvoted;
	/**/
	var $type;

	function __construct($row)
	{
		$this->type = $row['type'];
		$this->post_id = $row['postid'];
		$this->body = $row['content'];
		$this->creation_date = $row['created'];
		//$this->last_activity_date = $row['edittime'];
		$this->last_edit_date = $row['updated'];
		$this->last_editor = $row['lastuserid'];
		$this->owner = $row['userid'];
		$this->parent_post_id = $row['parentid'];
		$this->title = $row['title'];
	}

	static function get_query($ids, $id_type='post')
	{

		global $ID_TYPES;

		if(!in_array($ID_TYPES[$id_type], array("userid", "postid")))
			$id_type = 'post';
		

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
		
		if(isset($fromdate) || isset($todate) || isset($min) || isset($max) || isset($ids))
		{
			$use_and = false;
			$query .= " WHERE";
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