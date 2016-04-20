<?php


require_once "./api_classes/util.php";

class Post extends CallableData
{
	var $post_id;
	var $post_type;
	var $title;
	var $body;
	var $owner;
	var $score;
	var $up_vote_count;
	var $down_vote_count;
	var $creation_date;
	var $last_activity_date;
	var $last_edit_date;
	
	protected function is_valid_call()
	{
		return preg_match("~^/posts$~", $_SERVER['PATH_INFO']);
	}

	public function construct_from_row($row)
	{
		return new Post($row);
	}

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

	protected function valid_bool_vars()
	{
		return array();
	}

	protected function valid_int_vars()
	{
		return array(
			"down_vote_count",
			"owner",
			"post_id",
			"score",
			"up_vote_count"
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
			"body",
			"post_type",
			"title"
		);
	}

	protected function valid_var_mappings()
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

	protected function get_base_call()
	{
		return "SELECT P.* FROM
	(SELECT
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
		P.`type` IN ('A','A_HIDDEN','A_QUEUED','Q','Q_HIDDEN','Q_QUEUED')) P";
	}
}
?>