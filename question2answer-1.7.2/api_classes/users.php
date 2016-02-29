<?php
header("access-control-allow-origin: *");
require_once "./api_classes/util.php";
require_once "./api_classes/questions.php";
require_once "./api_classes/answers.php";
require_once "./api_classes/comments.php";
require_once "./api_classes/posts.php";
header('Content-Type: application/json');//JSON-formatting

/**
* The class for a user.
* Data for this comes from: https://api.stackexchange.com/docs/types/user
*/
class User
{
	const ID = "users";
	
	var $user_id;
	
	static function get_valid_IDs($IDs)
	{
		return "SELECT DISTINCT U.`userid` FROM `qa_users` U WHERE U.`userid` IN (".$IDs.")";
	}

	function __construct($row)
	{
		$this->user_id = $row['user_id'];
	}
/*
	static function get_query($ids, $id_type='user')
	{
		global $ID_TYPES;
		
		if(!in_array($ID_TYPES[$id_type], array("userid")))
			$id_type = 'user';

		$order = process_order();
	
		$sort = process_sort(array("reputation", "creation", "name", "modified"));

		
		if(isset($_GET["fromdate"]))
			$fromdate = process_date('fromdate');

		if(isset($_GET["todate"]))
			$todate = process_date('todate');
		
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
}
	

?>