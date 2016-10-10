<?php 
class HTMLHelper{
	static function select($name, $label, $options, $selected = null, $class = null){
		ob_start();
?>
<div class="form-group">
	<label for="<?=$name?>"><?=$label?>:</label>
	<select class="form-control <?=$class?>" id="<?=name?>">
<?php	foreach ($options as $value=>$optlabel): ?>
		<option value="<?=value?>" <?=$value == $selected ? "selected" : ""?>><?=$optlabel?></option>
<?php 	endforeach;?>
	</select>
</div>
<?php
		return ob_get_clean();
	}
	
	static function input($name, $type, $label, $class = null, $value=null){
		ob_start();
		$innerInputMethod = $type."Input";
		$innerInput = self::$innerInputMethod($name, $label, $value, $class);
?>
	<div class="form-group">
	    <label for="<?=$name?>"><?=$label?>:</label>
		<?=$innerInput;?> 
	</div>		
<?php
		return ob_get_clean();
	}
	
	private static function textInput($name, $label, $value=null, $class){
		return "<input type=\"text\" class=\"form-control $class\" name=\"$name\" id=\"$name\" value=\"$value\" />";
	}
	
	private static function passwordInput($name, $label, $value=null, $class){
		return "<input type=\"password\" class=\"form-control $class\" name=\"$name\" id=\"$name\" value=\"$value\" />";
	}
	
	private static function textareaInput($name, $label, $value=null, $class){
		return "<textarea class=\"form-control $class\" rows=\"5\" name=\"$name\" id=\"$name\">$value</textarea>";
	}
	
	static function multipleInput($type, $name, $label, $options, $selected = null, $class = null, $inline = false){
		ob_start();		
?>
	<label><?=$label?></label>
<?php 
		foreach ($options as $option):
			$input = "<input type=\"$type\" class=\"$class\" id=\"{$option['id']}\" name=\"$name\" value=\"{$option['value']}\" ".(in_array($option['value'], $selected) ? "checked" : "").">{$option['label']}";
			if($inline):
?>
	<label class="<?=$type?>-inline"><?=$input?></label>
<?php	 	else :?>	
	<div class="<?=$type?>">
		<label><?=$input?></label>
	</div>
<?php
			endif;
		endforeach;
?>
</div>
<?php
		return ob_get_clean();
	}

}
?> 