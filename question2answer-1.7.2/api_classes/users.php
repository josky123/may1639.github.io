<?php
define("IN_MYBB", 1);
header("access-control-allow-origin: *");
require_once "./api_classes/util.php";
header('Content-Type: application/json');//JSON-formatting

/**
* The class for a user.
* Data for this comes from: https://api.stackexchange.com/docs/types/user
*/
class User
{
	/** /
	var $about_me;
	var $accept_rate;
	/**/
	var $account_id;
	/** /
	var $age;
	var $answer_count;
	var $badge_counts;
	/**/
	var $creation_time;
	var $display_name;
	/** /
	var $down_vote_count;
	var $is_employee;
	/**/
	var $last_access_date;
	var $last_modified_date;
	/** /
	var $link;
	var $location;
	var $profile_image;
	var $question_count;
	/**/
	var $reputation;
	/** /
	var $reputation_change_day;
	var $reputation_change_month;
	var $reputation_change_quarter;
	var $reputation_change_week;
	var $reputation_change_year;
	var $timed_penalty_date;
	var $up_vote_count;
	/**/
	var $user_id;
	/** /
	var $user_type;
	var $view_count;
	var $website_url;
	/**/

	function __construct($row)
	{
		$this->account_id = $row['userid'];
		$this->creation_time = $row['created'];
		$this->display_name = $row['handle'];
		$this->last_access_date = $row['loggedin'];
		$this->last_modified_date = $row['written'];
		$this->reputation = $row['points'];
		$this->user_id = $row['userid'];
		// $this->website_url = $row['website'];
	}

	static function get_query($ids, $id_type='user')
	{
		global $ID_TYPES;
		
		if(!in_array($ID_TYPES[$id_type], array("userid")))
			$id_type = 'user';

		$order = process_order();
	
		$sort = process_sort(array("reputation", "creation", "name", "modified"));

		/**/
		if(isset($_GET["fromdate"]))
			$fromdate = process_date('fromdate');

		if(isset($_GET["todate"]))
			$todate = process_date('todate');
		/**/

		if(isset($_GET["min"]))
		{
			$min = process_min_max($sort, 'min');
			if(in_array($sort, array("creation", "modified")))
			{
				$min = "FROM_UNIXTIME(".$min.")";
			}
		}

		if(isset($_GET["max"]))
		{
			$max = process_min_max($sort, 'max');
			if(in_array($sort, array("creation", "modified")))
			{
				$max = "FROM_UNIXTIME(".$max.")";
			}
		}

		if(isset($_GET["inname"]))
			$inname = process_inname();

		$var_to_col_mapping = array("ids" => $ID_TYPES[$id_type], "reputation" => "qa_userpoints.points", "creation" => "created", "name" => "handle", "modified" => "written");
	
		$query = "SELECT qa_users.*, qa_userpoints.points FROM qa_users, qa_userpoints";
		

		$use_and = true;
		$query .= " WHERE qa_users.userid = qa_userpoints.userid";
		if(isset($fromdate) || isset($todate) || isset($min) || isset($max) || isset($inname) || isset($ids))
		{	
			if(isset($fromdate))
			{
				if($use_and)
					$query .= " AND";
				$query .= " qa_users.".$var_to_col_mapping["creation"]." > ".$fromdate;
				$use_and = true;
			}

			if(isset($todate))
			{
				if($use_and)
					$query .= " AND";
				$query .= " qa_users.".$var_to_col_mapping["creation"]." < ".$todate;
				$use_and = true;
			}
			
			if(isset($min))
			{
				if($use_and)
					$query .= " AND";
				$query .= " qa_users.".$var_to_col_mapping[$sort]." > ";
				if($sort == "name")
					$query .= "'".$min."'";
				else
					$query .= $min;
				$use_and = true;
			}

			if(isset($max))
			{
				if($use_and)
					$query .= " AND";
				$query .= " qa_users.".$var_to_col_mapping[$sort]." < ";
				if($sort == "name")
					$query .= "'".$max."'";
				else
					$query .= $max;
				$use_and = true;
			}

			if(isset($inname))
			{
				if($use_and)
					$query .= " AND";
				$query .= " qa_users.".$var_to_col_mapping["name"]." LIKE '%".$inname."%'";
				$use_and = true;
			}

			if(isset($ids) && (0 < count($ids)))
			{
				if($use_and)
					$query .= " AND";
				$query .= " qa_users.".$var_to_col_mapping["ids"]." IN (";
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