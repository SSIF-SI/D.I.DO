<?php 
class Common{
	const N_TOT = "nTot";
	
	static function countSingleMultiArrayItems($array, $label){
		if(key_exists($label, $array) && is_array($array[$label])) 
			return count($array[$label]);
		foreach($array as $key=>$values){
			if(is_array($array[$key]))
				return self::countSingleMultiArrayItems($array[$key], $label);
		}
		return 0;
	}
	
	static function countMultipleMultiArrayItems($array, $labels){
		if(!is_array($labels)) return 0;
		
		$sum = 0;
		foreach($labels as $label){
			$sum += self::countSingleMultiArrayItems($array, $label);
		}
		return $sum;
	}
	
	public static function fieldFromLabel($label) {
		return strtolower ( str_replace ( array (
				" ",
				"'",
				"/"
		), "_", strtolower($label) ) );
	}
	public static function labelFromField($field) {
		return ucfirst(strtolower ( str_replace ( array (
				"_",
				"'",
				"/"
		), " ", $field ) ) );
	}
	
	public static function redirectTo($url = HTTP_ROOT){
		header("Location: " . $url);
		die();
	}
}
?>