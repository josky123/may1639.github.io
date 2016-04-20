<?php

require_once "./api_classes/util.php";

class Tag extends CallableData
{
	var $name;
	var $count;
	var $last_activity_date;
	
	protected function is_valid_call()
	{
		return preg_match("~^/tags$~", $_SERVER['PATH_INFO']);
	}

	public function construct_from_row($row)
	{
		return new Tag($row);
	}

	function __construct($row)
	{
		$this->count = (integer) $row['count'];
		$this->last_activity_date = $row['last_activity_date'];
		$this->name = $row['name'];
	}

	protected function valid_bool_vars()
	{
		return array();
	}

	protected function valid_int_vars()
	{
		return array(
			"count"
		);
	}

	protected function valid_date_vars()
	{
		return array(
			"last_activity_date"
		);
	}

	protected function valid_string_vars()
	{
		return array(
			"name"
		);
	}

	protected function valid_var_mappings()
	{
		return array(
			"count" => "T.`count`",
			"last_activity_date" => "T.`last_activity_date`",
			"name" => "T.`name`"
		);
	}

	protected function get_base_call()
	{
		return "SELECT T.* FROM
	(SELECT
		W.`tagcount` AS `count`,
		MAX(PT.`postcreated`) AS `last_activity_date`,
		W.`word` AS `name`
	FROM
		`qa_words` W
	JOIN
		`qa_posttags` PT
	ON
	W.`wordid` = PT.`wordid`
GROUP BY
	W.`word`) T";
	}
}
?>