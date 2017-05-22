<?php
class ArrayHelper{
	public static function countItems($array, $label){
		$array = self::findarray($array,$label);
		if(!$array) return 0;
		$sum=0;
		$sum += self::countArray($array);
		return $sum;
	}

	private static function findarray($array,$label){
		$retArr = false;
		if(array_key_exists($label, $array))
			return $array[$label];
		foreach($array as $key=>$values){
			if(is_array($values)){
				$retArr = self::findarray($values, $label);
				if($retArr)
					return $retArr;
			}
		}
		return false;
	}

	private static function countArray($array){
		if(count(array_filter(array_keys($array), 'is_string')) == 0) 
			return count($array);
		$sum = 0;
		foreach($array as $key=>$values){
			$sum += self::countArray($values);
		}
		return $sum;
	}
}
?>