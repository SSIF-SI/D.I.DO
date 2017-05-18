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
	
	public static function getNewPathFromXml($xml) {
		return dirname ( $xml ) . DIRECTORY_SEPARATOR . date ( "Y" ) . DIRECTORY_SEPARATOR . date ( "m" ) . DIRECTORY_SEPARATOR;
	}
	
	public static function getFolderNameFromMasterdocument($md) {
		return self::fieldFromLabel ( $md[Masterdocument::NOME] . " " . $md[Masterdocument::ID_MD]);
	}
	
	public static function getFilenameFromDocument($document) {
		return self::fieldFromLabel ( $document [Document::NOME] . " " . $document [Document::ID_DOC] . "." . $document [Document::EXTENSION] );
	}
	
	
	public static function redirect($url = HTTP_ROOT){
		header("Location: " . $url);
		die();
	}
}
?>