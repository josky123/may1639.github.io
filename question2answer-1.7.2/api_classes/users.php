<?php


require_once "./api_classes/util.php";

class User extends CallableData
{
	var $user_id;
	var $display_name;
	var $reputation;
	var $up_vote_count;
	var $down_vote_count;
	var $badge_count;
	var $question_count;
	var $answer_count;
	var $accept_rate;
	var $creation_date;
	var $last_access_date;
	var $last_modified_date;
	
	protected function is_valid_call()
	{
		return preg_match("~^/users$~", $_SERVER['PATH_INFO']);
	}

	public function construct_from_row($row)
	{
		return new User($row);
	}

	function __construct($row)
	{
		$this->accept_rate = (integer) $row['accept_rate'];
		$this->answer_count = (integer) $row['answer_count'];
		$this->badge_count = (integer) $row['badge_count'];
		$this->creation_date = $row['creation_date'];
		$this->display_name = $row['display_name'];
		$this->down_vote_count = (integer) $row['down_vote_count'];
		$this->last_access_date = $row['last_access_date'];
		$this->last_modified_date = $row['last_modified_date'];
		$this->question_count = (integer) $row['question_count'];
		$this->reputation = (integer) $row['reputation'];
		$this->up_vote_count = (integer) $row['up_vote_count'];
		$this->user_id = (integer) $row['user_id'];
	}

	protected function valid_bool_vars()
	{
		return array();
	}

	protected function valid_int_vars()
	{
		return array(
			"accept_rate",
			"answer_count",
			"badge_count",
			"down_vote_count",
			"question_count",
			"reputation",
			"up_vote_count",
			"user_id"
		);
	}

	protected function valid_date_vars()
	{
		return array(
			"creation_date",
			"last_access_date",
			"last_modified_date"
		);
	}

	protected function valid_string_vars()
	{
		return array(
			"display_name"
		);
	}

	protected function valid_var_mappings()
	{
		return array(
			"accept_rate" => "U.`accept_rate`",
			"answer_count" => "U.`answer_count`",
			"badge_count" => "U.`badge_count`",
			"creation_date" => "U.`creation_date`",
			"display_name" => "U.`display_name`",
			"down_vote_count" => "U.`down_vote_count`",
			"last_access_date" => "U.`last_access_date`",
			"last_modified_date" => "U.`last_modified_date`",
			"question_count" => "U.`question_count`",
			"reputation" => "U.`reputation`",
			"up_vote_count" => "U.`up_vote_count`",
			"user_id" => "U.`user_id`"
		);
	}

	protected function get_base_call()
	{
		return "SELECT U.* FROM
	(SELECT
		P.`aselecteds`	AS	`accept_rate`,
		P.`aposts`	AS	`answer_count`,
		B.`badge_count`	AS	`badge_count`,
		U.`created`	AS	`creation_date`,
		U.`handle`	AS	`display_name`,
		P.`downvoteds`	AS	`down_vote_count`,
		U.`loggedin`	AS	`last_access_date`,
		U.`written`	AS	`last_modified_date`,
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
			COUNT(B.`badge_slug`) AS `badge_count`,
			B.`user_id`
		FROM
			`qa_userbadges` B
		GROUP BY
			B.`user_id`) B
	ON
		B.`user_id` = U.`userid`
	) U";
	}
}
?>