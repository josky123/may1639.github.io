<?php

require_once "./api_classes/util.php";

class Answer extends CallableData
{
	var $answer_id;
	var $body;
	var $creation_date;
	var $down_vote_count;
	var $last_activity_date;
	var $last_edit_date;
	var $owner;
	var $question_id;
	var $score;
	var $title;
	var $up_vote_count;

	public function is_valid_call()
	{
		return preg_match("~^/answers$~", $_SERVER['PATH_INFO']);
	}

	public function construct_from_row($row)
	{
		return new Answer($row);
	}

	function __construct($row)
	{
		$this->answer_id = (integer) $row['answer_id'];
		$this->body = $row['body'];
		$this->creation_date = $row['creation_date'];
		$this->down_vote_count = (integer) $row['down_vote_count'];
		$this->last_activity_date = $row['last_activity_date'];
		$this->last_edit_date = $row['last_edit_date'];
		$this->owner = (integer) $row['owner'];
		$this->question_id = (integer) $row['question_id'];
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
			"answer_id",
			"down_vote_count",
			"owner",
			"question_id",
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
			"title",
			"body"
		);
	}

	protected function valid_var_mappings()
	{
		return array(
			"answer_id" => "A.`answer_id`",
			"body" => "A.`body`",
			"creation_date" => "A.`creation_date`",
			"down_vote_count" => "A.`down_vote_count`",
			"last_activity_date" => "A.`last_activity_date`",
			"last_edit_date" => "A.`last_edit_date`",
			"owner" => "A.`owner`",
			"question_id" => "A.`question_id`",
			"score" => "A.`score`",
			"title" => "A.`title`",
			"up_vote_count" => "A.`up_vote_count`"
			);
	}

	protected function get_base_call()
	{
		return "SELECT A.* FROM
	(SELECT
		A.`postid`	AS	`answer_id`,
		A.`content`	AS	`body`,
		A.`created`	AS	`creation_date`,
		A.`downvotes`	AS	`down_vote_count`,
		IFNULL(A.`updated`, A.`created`)	AS	`last_activity_date`,
		A.`updated`	AS	`last_edit_date`,
		A.`userid`	AS	`owner`,
		A.`parentid`	AS	`question_id`,
		A.`netvotes`	AS	`score`,
		A.`title`	AS	`title`,
		A.`upvotes`	AS	`up_vote_count`
	FROM
		`qa_posts` A
	WHERE
		A.`type` IN ('A','A_HIDDEN','A_QUEUED')) A";
	}
}
?>