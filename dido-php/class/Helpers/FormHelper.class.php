<?php

class FormHelper {

	private static $warnmessages = array ();

	private static $warnBox = "";

	public static function createInputs($inputs, $data, $innerValues = null, $readonly = false) {
		if($innerValues){
			$iValues = array();
			foreach($innerValues as $i_Values){
				$key = (string)$i_Values[XMLParser::IV_NAME];
				foreach($i_Values->value as $i_Value){
					$k =(string) $i_Value[XMLParser::IV_VALUE];
					$v =(string) $i_Value;
					$iValues[$key][$k] = $v;
				}
			}
			$innerValues = $iValues;
			
		}
		
		if (! $inputs)
			return;
		ob_start ();
		$col = 0;
		?>
<div class="row">
<?php
		foreach ( $inputs as $input ) :
			$col ++;
			if ($col > 3) {
				$col = 1;
				?>
</div>
<div class="row">
<?php
			}
			$editable = ! $readonly || ($readonly && isset ( $input [XMLParser::EDITABLE] ) && $input [XMLParser::EDITABLE]);
			$type = is_null ( $input [XMLParser::TYPE] ) ? 'text' : ( string ) $input [XMLParser::TYPE];
			
			$required = is_null ( $input [XMLParser::MANDATORY] ) ? true : ( bool ) ( string ) $input [XMLParser::MANDATORY];
			// $required = is_null($input['mandatory']) ? true : false;
			
			$key = Common::labelFromField ( ( string ) $input, false );
			
			$field = Common::fieldFromLabel ( $key );
			$label = Common::labelFromField ( $key );
			
			$value = $data [$key];
			
			if (isset ( $input [XMLParser::VALUES] )) {
				$rValue = $value;
				$callback = ( string ) $input [XMLParser::VALUES];
				$values = ListHelper::$callback ();
				if(isset($input [XMLParser::AUTOCOMPLETE])){
					$MDD = new MasterdocumentData(DBConnector::getInstance());
					//TODO: Rifattorizzare la getBy del Crud
					$MDDList = Utils::getListfromField($MDD->getBy(MasterdocumentData::KEY, Utils::apici(strtolower($label)), MasterdocumentData::VALUE),MasterdocumentData::VALUE, MasterdocumentData::VALUE);
					$values = $values + $MDDList;
					uasort($values, function($a, $b){
						$a = strtolower($a);
						$b = strtolower($b);
						return $a == $b ? 0 : ($a > $b ? 1 : -1);
					});
				}
				
				if (isset ( $input [XMLParser::SIGN_ROLE] )) {
					$values_alt = ListHelper::persone ();
				}
				
				$old = ! isset ( $values [$value] );
				
				$value = 
					isset($input [XMLParser::AUTOCOMPLETE]) ? 
					$rValue : 
					(isset ( $values [$value] ) ? $values [$value] : $values_alt [$value]);
			}
			
			if(isset($input[XMLParser::INNER_VALUES]) && !is_null($innerValues)){
				$rValue = $value;
				$innerValuesToSearch = (string)$input[XMLParser::INNER_VALUES];
				if(isset($innerValues[$innerValuesToSearch])){
					$values = $innerValues[$innerValuesToSearch];
					$old = !isset($values[$value]);
					$value = isset($values[$value]) ? $values[$value] : null;
				}
				
			}
			
			
			if ($type == "data")
				$value = Utils::convertDateFormat ( $value, DB_DATE_FORMAT, "d/m/Y" );
			
			?>
<div class="col-lg-4">
        <?php if($readonly || !$editable):?>
		        <strong><?=ucfirst($key)?> <?php HTMLHelper::checkRequired($required);?></strong><br />
		<em><?=empty($value) ? "&nbsp;" : nl2br($value)?></em>
		
             
			 
		<?php else :
				if ((! isset ( $input [XMLParser::VALUES] ) && ! isset ( $input [XMLParser::INNER_VALUES] )) || isset($input[XMLParser::AUTOCOMPLETE])){
					$class = isset($input[XMLParser::AUTOCOMPLETE]) ? "ui-autocomplete-input" : null;
					$acValues = isset($input[XMLParser::AUTOCOMPLETE]) ? $values : null;
					$input_html = HTMLHelper::input ( $type, $field, $label, $value, $class, $required, false, $acValues );
				}else {
					if ($old == false || is_null ( $data [$key] ))
						$input_html = HTMLHelper::select ( $field, $label, $values, $rValue, null, false, $required );
					else
						$input_html = HTMLHelper::fakeInput ( $field, $label, $value, $rValue, $required );
				}
				echo $input_html;
			endif;
			?>
				<hr />
	</div>
<?php
		endforeach
		;
		?>
</div>
<?php
		return ob_get_clean ();
	}

	public static function check($REQUEST, $inputs) {
		self::$warnmessages = array ();
		self::$warnBox = "";
		
		foreach ( $inputs as $input ) {
			
			$label = ( string ) $input;
			$field = self::fieldFromLabel ( $label );
			$type = is_null ( $input [XMLParser::TYPE] ) ? 'text' : ( string ) $input [XMLParser::TYPE];
			$mandatory = is_null ( $input [XMLParser::MANDATORY] ) ? true : $input [XMLParser::MANDATORY];
			
			if ($mandatory) {
				if ($type != 'hidden') { // I tipi nascosti a regola sono sempre
				                         // validi
					if (! isset ( $REQUEST [$field] ) || trim ( $REQUEST [$field] ) === "") {
						self::$warnmessages [$field] = array (
								'error' => "Il campo <em>$label</em> è obbligatorio",
								'class' => "has-error" 
						);
					} else {
						self::checkField ( $type, $REQUEST, $field, $label );
					}
				}
			}
		}
		
		if (! self::isValid ()) {
			$errors = Utils::filterList ( self::$warnmessages, 'error', true );
			$errors = Utils::getListfromField ( $errors, 'error' );
			self::$warnBox = HTMLHelper::saveErrorBox ( $errors );
		}
	}

	public static function isValid() {
		$errors = Utils::filterList ( self::$warnmessages, 'error', true );
		return count ( $errors ) == 0;
	}

	public static function getWarnMessages($item = null) {
		return is_null ( $item ) ? self::$warnmessages : (isset ( self::$warnmessages [$item] ) ? self::$warnmessages [$item] : null);
	}

	public static function getWarnBox() {
		return self::$warnBox;
	}

	private static function checkField($type, $var, $field, $label) {
		$var [$field] = trim ( $var [$field] );
		
		switch ($type) {
			case 'text' :
				$pattern = "/^[A-Za-z0-9_\s]{1,}$/";
				break;
			case 'date' :
				$pattern = "#^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$#";
				break;
			case 'numeric' :
				$pattern = "#^[0-9]{1,}[\.|,]{0,1}[0-9]{0,}$#";
				break;
			default :
				$pattern = "/^(.*?)$/";
		}
		if (! preg_match ( $pattern, $var [$field] ))
			self::$warnmessages [$field] = array (
					'error' => "Il campo <em>$label</em> ha caratteri non validi al suo interno",
					'class' => "has-warning" 
			);
		else
			self::$warnmessages [$field] = array (
					'error' => false,
					'class' => "has-success" 
			);
	}
}
?>