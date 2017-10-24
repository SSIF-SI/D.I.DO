<?php
function getListfromField($list,$field = null, $key = null, $glue = " - "){
	if(is_null($field) && is_null($key)) return $list;
	$newList = array();

	if(is_array($list)) foreach($list as $k=>$item){
		$item = (array)$item;

		$i_key = is_null($key) ? $k : $item[$key];
			
		if(is_array($field)){
			$value = array();
			foreach($field as $f){
				if(!isset($item[$f])) return $newList;
				array_push($value, $item[$f]);
			}
			$value = join($glue, $value);
		} else {
			if(!is_null($field) && !isset($item[$field])) return $newList;
			$value = is_null($field) ? $item : $item[$field];
		}
			
		$newList[$i_key] = $value;
			
		
	}
	return $newList;
}

$result = array(
	0 => array(
			"label1" => "field1",
			"label2" => "field2",
			"label3" => "field3",
		),
	1 => array(
			"label1" => "field4",
			"label2" => "field5",
			"label3" => "field6",
		),
		
);

/*
	if(is_null($field) && is_null($key)) return $list;
		$newList = array();
		
		if(is_array($list)) foreach($list as $k=>$item){
			$item = (array)$item;

			$i_key = is_null($key) ? $k : $item[$k];
			
			if(is_array($field)){
				$value = array();
				foreach($field as $f){
					if(!isset($item[$f])) return $newList;
					array_push($value, $item[$f]);
				}
				$value = join($glue, $value);
			} else {
				if(!is_null($field) && !isset($item[$field])) return $newList;
				$value = is_null($field) ? $item : $item[$field];
			}
			
			$newList[$i_key] = $value;
			
			
		}
		return $newList;
 */
print_r(getListfromField($result,array("label2","label3"),"label1"));
//$list[Application_DocumentBrowser::LABEL_MD] = Common::categorize($list[Application_DocumentBrowser::LABEL_MD]);

//Utils::printr($list[Application_DocumentBrowser::LABEL_MD]);

//Utils::printr(ArrayHelper::countItems($list, "ordine cassa"));