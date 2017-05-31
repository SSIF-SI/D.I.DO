<?php 
class Common{
	const N_TOT = "nTot";
	
	public static function categorize($mdToCategorize){
		$new_md = [];
		foreach($mdToCategorize as $md){
			$category = dirname($md[Masterdocument::XML]);
			$subCategory = $md[Masterdocument::NOME];
			$XMLCategory = $md[Masterdocument::XML];
				
			$new_md	[$category] [$subCategory] [$XMLCategory] [$md[Masterdocument::ID_MD]]	= $md;
		}
	
		return $new_md;
	}
	
	
	public static function fieldFromLabel($label) {
		return strtolower ( str_replace ( array (
				" ",
				"'",
				"/"
		), "_", strtolower($label) ) );
	}
	public static function labelFromField($field, $upperFirst = true) {
		$field = strtolower ( str_replace ( array (
				"_",
				"'",
				"/"
		), " ", $field ) );
		return $upperFirst ? ucfirst($field) : $field;
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
	
	public static function createPostMetadata($postArray, $id_parent=null){
		$retArray = [ ];
		
		foreach ( $postArray as $key=>$post ) :
			if(preg_match("/(\d{2})\/(\d{2})\/(\d{4})$/", $post)){
				$post = Utils::convertDateFormat($post, "d/m/Y", DB_DATE_FORMAT);
			}
				
			$key = self::labelFromField($key, false);
			$retArray [$key] = $post;
		endforeach;
		
		if(!is_null($id_parent))
			$retArray = [$id_parent => $retArray];
		
		return $retArray;
	}	
	
	public static function getFileExtension($file){
		$path_parts = pathinfo($file);
		return $path_parts['extension'];
		
	}
	
	public static function renderValue($value, $input){
		if(isset($input[XMLParser::VALUES])){
			$callback = ( string ) $input[XMLParser::VALUES];
			$values = ListHelper::$callback();
			if(isset($input[XMLParser::SIGN_ROLE])){
				$values_alt = ListHelper::persone();
			}
			$value = isset($values[$value]) ? $values[$value] : $values_alt[$value];
		}
		if($input[XMLParser::TYPE] == "data")
			$value = Utils::convertDateFormat($value, DB_DATE_FORMAT, "d/m/Y");
		
		return $value;
	}
}
?>