<?php
header("access-control-allow-origin: *");
require_once "./api_classes/util.php";
header('Content-Type: application/json');//JSON-formatting

/**
* The class for a Tag.
* Data for this comes from: https://api.stackexchange.com/docs/types/tag
*/
class Tag
{
	var $tagid;
	var $name;
	var $count;
	var $activity;

	function __construct($row)
	{
		$this->tagid = $row['tagid'];
		$this->name = $row['name'];
		$this->count = $row['count'];
		$this->activity = $row['activity'];
	}

	static function get_query($ids, $id_type='tag')
	{
		global $ID_TYPES;
		
		if(!in_array($ID_TYPES[$id_type], array("tagid")))
			$id_type = 'tag';

		$order = process_order();
	
		$sort = process_sort(array("popular", "activity", "name"));

		/**/
		if(isset($_GET["fromdate"]))
			$fromdate = process_date('fromdate');

		if(isset($_GET["todate"]))
			$todate = process_date('todate');
		/**/

		if(isset($_GET["min"]))
		{
			$min = process_min_max($sort, 'min');
			if(in_array($sort, array("creation", "activity", "modified")))
			{
				$min = "FROM_UNIXTIME(".$min.")";
			}
		}

		if(isset($_GET["max"]))
		{
			$max = process_min_max($sort, 'max');
			if(in_array($sort, array("creation", "activity", "modified")))
			{
				$max = "FROM_UNIXTIME(".$max.")";
			}
		}

		if(isset($_GET["inname"]))
			$inname = process_inname();

		$var_to_col_mapping = array("ids" => "core.".$ID_TYPES[$id_type], "popular" => "core.count", "activity" => "core.activity", "name" => "core.name");
	
		$query = "SELECT core.tagid AS `tagid`, core.name as `name`, core.count AS `count`, core.activity AS `activity` FROM (SELECT T.wordid AS `tagid`, W.word as `name`, count(T.postid) AS `count`, MAX(P.created) AS `activity` FROM qa_tagwords T, qa_words W, qa_posts P WHERE T.wordid = W.wordid AND T.postid = P.postid GROUP BY T.wordid) AS core";
		

		if(isset($fromdate) || isset($todate) || isset($min) || isset($max) || isset($inname) || isset($ids))
		{
			$query .= " WHERE";
			$use_and = false;
			if(isset($fromdate))
			{
				if($use_and)
					$query .= " AND";
				$query .= " ".$var_to_col_mapping["activity"]." > ".$fromdate;
				$use_and = true;
			}

			if(isset($todate))
			{
				if($use_and)
					$query .= " AND";
				$query .= " ".$var_to_col_mapping["activity"]." < ".$todate;
				$use_and = true;
			}
			
			if(isset($min))
			{
				if($use_and)
					$query .= " AND";
				$query .= " ".$var_to_col_mapping[$sort]." > ";
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
				$query .= " ".$var_to_col_mapping[$sort]." < ";
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
				$query .= " ".$var_to_col_mapping["name"]." LIKE '%".$inname."%'";
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