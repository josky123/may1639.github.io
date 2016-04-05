<?php
require_once "./api_classes/util.php";

class Comment extends CallableData
{
	var $comment_id;
	var $post_id;
	var $body;
	var $owner;
	var $score;
	var $edited;
	var $creation_date;

	public function is_valid_call()
	{
		return preg_match("~^/comments$~", $_SERVER['PATH_INFO']);
	}

	public function construct_from_row($row)
	{
		return new Comment($row);
	}

	function __construct($row)
	{
		$this->body = $row['body'];
		$this->comment_id = (integer) $row['comment_id'];
		$this->creation_date = $row['creation_date'];
		$this->edited = (boolean) $row['edited'];
		$this->owner = (integer) $row['owner'];
		$this->post_id = (integer) $row['post_id'];
		$this->score = (integer) $row['score'];
	}

	protected function valid_bool_vars()
	{
		return array(
			"edited"
		);
	}

	protected function valid_int_vars()
	{
		return array(
			"comment_id",
			"owner",
			"post_id",
			"score"
		);
	}

	protected function valid_date_vars()
	{
		return array(
			"creation_date"
		);
	}

	protected function valid_string_vars()
	{
		return array(
			"body"
		);
	}

	protected function valid_var_mappings()
	{
		return array(
			"body" => "C.`body`",
			"comment_id" => "C.`comment_id`",
			"creation_date" => "C.`creation_date`",
			"edited" => "C.`edited`",
			"owner" => "C.`owner`",
			"post_id" => "C.`post_id`",
			"score" => "C.`score`"
		);
	}

	protected function get_base_call()
	{
		return "SELECT C.* FROM
	(SELECT
		C.`content`	AS	`body`,
		C.`postid`	AS	`comment_id`,
		C.`created`	AS	`creation_date`,
		C.`updated` IS NOT NULL	AS	`edited`,
		C.`userid`	AS	`owner`,
		C.`parentid`	AS	`post_id`,
		C.`netvotes`	AS	`score`
	FROM
		`qa_posts` C
	WHERE
		C.`type` IN ('C','C_HIDDEN','C_QUEUED')) C";
	}
}
?>