<?php

require_once "./api_classes/util.php";

class Question extends CallableData
{
	var $question_id;
	var $title;
	var $body;
	var $tags;
	var $owner;
	var $view_count;
	var $score;
	var $up_vote_count;
	var $down_vote_count;
	var $is_answered;
	var $answer_count;
	var $accepted_answer_id;
	var $creation_date;
	var $last_activity_date;
	var $last_edit_date;

	protected function is_valid_call()
	{
		return preg_match("~^/questions$~", $_SERVER['PATH_INFO']);
	}

	public function construct_from_row($row)
	{
		return new Question($row);
	}

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

	protected function valid_bool_vars()
	{
		return array(
			"is_answered"
		);
	}

	protected function valid_int_vars()
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
			"body",
			"tags"
		);
	}

	protected function valid_var_mappings()
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

	protected function get_base_call()
	{
		return "SELECT Q.* FROM
	(SELECT
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
		Q.`type` IN ('Q','Q_HIDDEN','Q_QUEUED')
	) Q";

	}
}
?>