<?php 
class FormHelper{
	private static $warnmessages = array();
	private static $warnBox = "";

	public static function createInputsFromDB($inputs, $mdData, $readonly = false){
		ob_start();
		foreach ($inputs as $input):
			$editable = (isset($input['editable']) && $input['editable']); 
			$key = (string)$input;
			$value = $mdData[$key];
			if(isset($input['values'])){
				$callBack = (string)$input['values'];
				$values = ListHelper::$callBack();
				$rValue = $value;
				$value = $values[$value];
			} 
			if(isset($input['type']) && $input['type'] == "data")
				$value = Utils::convertDateFormat($value, DB_DATE_FORMAT, "d/m/Y");
				
		?>
			<div class="col-lg-4">
        <?php if($readonly || !$editable):?>
		        <strong><?=ucfirst($key)?></strong><br/>
				<em><?=$value?></em>
		<?php else:
				if(is_null($input['values']))
					$input_html = HTMLHelper::input($input['type'], $key, ucfirst($key), $value);
				else{
					$input_html = HTMLHelper::select($key, ucFirst($key), $values, $rValue);
				}
				echo $input_html;
			endif; ?>
				<hr/>
			</div>
		<?php 
		endforeach;
		return ob_get_clean();
	}
	
	public static function createInputsFromXml($xmlInputs, $colDivision = 4, $_IMPORT = array()){
		$inputs = array();
		if(count($xmlInputs)){
			foreach ($xmlInputs as $input){
				$type = is_null($input['type']) ? 'text' : (string)$input['type'];
				$required = is_null($input['mandatory']) ? true : boolvar($input['mandatory']);
				$label = (string)$input;
				$field = /*isset($_POST[$field]) ?*/ self::fieldFromLabel($label) /*: (string)$input['key']*/;
				$value = isset($_POST[$field]) ? $_POST[$field] : (isset($_IMPORT[(string)$input['key']]) ? $_IMPORT[(string)$input['key']] : null);
				
				/* IN REALTA NN VA FATTO QUI... DA VEDERE... */
				if(!is_null($input['transform']) && empty($_POST)){
					$callback = (string)$input['transform'];
					$value = ImportHelper::$callback($value);
				}
				
				
				$warning = FormHelper::getWarnMessages($field);
				$class = isset($warning['class']) ? $warning['class'] : null;
				
				if(is_null($input['values']))
					$input_html = HTMLHelper::input($type, $field, $label, $value, $class, $required, !is_null($input['from']));
				else{
					$callback = (string)$input['values'];
					$options = ListHelper::$callback();
					$input_html = HTMLHelper::select($field, $label, $options, $value, $class, !is_null($input['from']));
					if(!is_null(($input['alt'])) && !isset($options[$value])) {
						$alt = (string)$input['alt'];
						$alt = explode("|", $alt);
						$alt = array_map(function($el) use ($_IMPORT) { 
							$el = trim($el);
							if(!empty($_IMPORT[$el])) return $_IMPORT[$el]; }
							, $alt);
						$join = isset($input['join']) ? (string)$input['join'] : " ";
						$value = join($join, $alt);
						$input_html = HTMLHelper::input($type, $field, $label, $value, $class, $required, !is_null($input['from']));
					}
					
				}
				
				if($type != 'hidden') $input_html = "<div class=\"col-lg-$colDivision\">$input_html</div>";
				array_push($inputs, $input_html);	
				
			}
		}
		
		return $inputs;
	}
	
	public static function check($REQUEST, $inputs){
		
		self::$warnmessages = array();
		self::$warnBox = "";
		
		foreach($inputs as $input){
			
			$label = (string)$input;
			$field = self::fieldFromLabel($label);
			$type = is_null($input['type']) ? 'text' : (string)$input['type'];
			$mandatory = is_null($input['mandatory']) ? true : $input['mandatory'];
			
			if($mandatory){
				if($type != 'hidden'){ // I tipi nascosti a regola sono sempre validi
					if(!isset($REQUEST[$field]) || trim($REQUEST[$field]) === ""){
						self::$warnmessages[$field] = array('error'=> "Il campo <em>$label</em> è obbligatorio", 'class' => "has-error");
					} else {
						self::checkField($type, $REQUEST, $field, $label);
					}
				}
			}
		}
		
		if(!self::isValid()){
			$errors = Utils::filterList(self::$warnmessages, 'error', true);
			$errors = Utils::getListfromField($errors,'error');
			self::$warnBox = HTMLHelper::saveErrorBox($errors);
		}
	}
	
	public static function isValid(){
		$errors = Utils::filterList(self::$warnmessages, 'error', true);
		return count($errors) == 0;
	}
	
	public static function getWarnMessages($item = null){
		return is_null($item) ? self::$warnmessages : 
			(isset(self::$warnmessages[$item]) ? self::$warnmessages[$item] : null);  
	}
	
	public static function getWarnBox(){
		return self::$warnBox;  
	}
	
	private static function fieldFromLabel($label){
		return strtolower(str_replace(" ", "_", $label));
	}
	
	private static function checkField($type, $var, $field, $label){
		$var[$field] = trim($var[$field]);
		
		switch($type){
			case 'text':
				$pattern = "/^[A-Za-z0-9_\s]{1,}$/";
				break;
			case 'date':
				$pattern = "#^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$#";
				break;
			case 'numeric':
				$pattern = "#^[0-9]{1,}[\.|,]{0,1}[0-9]{0,}$#";
				break;
			default:
				$pattern = "/^(.*?)$/";
		}
		if(!preg_match($pattern, $var[$field]))
			self::$warnmessages[$field] = array('error'=> "Il campo <em>$label</em> ha caratteri non validi al suo interno", 'class' => "has-warning");
		else 
			self::$warnmessages[$field] = array('error'=> false, 'class' => "has-success");
	}
}
?>