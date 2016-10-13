<?php 
class HTMLHelper{
	static function select($name, $label, $options, $selected = null, $class = null){
		ob_start();
?>
<div class="form-group <?=$class?>">
	<label class="control-label" for="<?=$name?>"><?=$label?>:</label>
	<select class="form-control" id="<?=name?>" name="<?=$name?>">
<?php	foreach ($options as $value=>$optlabel): ?>
		<option value="<?=$value?>" <?=$value == $selected ? "selected" : ""?>><?=$optlabel?></option>
<?php 	endforeach;?>
	</select>
</div>
<?php
		return ob_get_clean();
	}
	
	static function input($type, $name, $label, $value=null, $class = null, $required = false){
		ob_start();
		if($type == 'hidden'):
			echo self::lineInput($type, $name, $label, $value, $required);
		else :
		$innerInput = $type == 'textarea' ? self::textareaInput($name, $label, $value, $required) : self::lineInput($type, $name, $label, $value, $required);
?>
	<div class="form-group <?=$class?>">
	    <label class="control-label" for="<?=$name?>"><?=$label?>:</label>
		<?=$innerInput;?> 
	</div>		
<?php
		endif;
		return ob_get_clean();
	}
	
	static function multipleInput($type, $name, $label, $options, $selected = array(), $class = null, $inline = false){
		ob_start();		
?>
<div class="form-group <?=$class?>">
	<label class="control-label"><?=$label?></label>
<?php 
		foreach ($options as $option):
			$input = "<input type=\"$type\" id=\"{$option['id']}\" name=\"$name\" value=\"{$option['value']}\" ".(in_array($option['value'], $selected) ? "checked" : "").">{$option['label']}";
			if($inline):
?>
	<label class="control-label" class="<?=$type?>-inline"><?=$input?></label>
<?php	 	else :?>	
	<div class="<?=$type?>">
		<label class="control-label"><?=$input?></label>
	</div>
<?php
			endif;
		endforeach;
?>
</div>
<?php
		return ob_get_clean();
	}

	public static function ulist($list, $class = null){
		if(!is_array($list) || count($list) == 0) return false;
		ob_start();
?>
<ul class="<?=$class?>">
	<?php foreach ($list as $item):?>
	<li><?=$item?></li>
	<?php endforeach;?>
</ul>
<?php 
		return ob_get_clean();
	}
	
	public static function saveErrorBox($errors){
		$errors = self::ulist($errors);
		return "<div class=\"alert alert-danger\"><p><strong>Attenzione!</strong> Sono stati riscontrati i seguenti errori:</p><p>$errors</p></div>";
	}
	
	public static function saveSuccessBox(){
		return "<div class=\"alert alert-success\"><p><span class=\"glyphicon glyphicon-ok\">&nbsp;</span> Dati salvati con successo</p></div>";
	}
	
	public static function editTable($list, $buttons = array(), $substitutes = null, $hidden = null){
		ob_start();
?>
		<div class="table-responsive">
			<table class="table table-condensed table-striped">
				<thead>
					<tr>
					<?php foreach(array_keys(reset($list)) as $key):?>
					<?php if(isset($hidden) && in_array($key,$hidden)) continue;?>
						<th><?=ucfirst(str_replace("id_","",$key))?></tH>
					<?php endforeach;?>
						<th></th>
					</tr>
				<tbody>
					<?php foreach($list as $key=>$row):?>
					<tr id="row-<?=$row['variable'] ? 'variable' : 'fixed'?>-<?=$row['id_persona']?>">
						<?php foreach($row as $field=>$value):?>
							<?php if(isset($hidden) && in_array($field,$hidden)) continue;?>
						<td><?=isset($substitutes[$key][$field]) ? $substitutes[$key][$field] : $value?></td>
						<?php endforeach;?>
						<td class="text-right">
							<?php if(isset($buttons[$key])) foreach($buttons[$key] as $label=>$button):?>
							<a class="btn btn-<?=$button['type']?>" href="<?=$button['href']?>">
								<span class="glyphicon glyphicon-<?=$button['icon']?>"></span> <?=$label?>
							</a>
							<?php endforeach;?>
						</td>
					</tr>
					<?php endforeach;?>
				</tbody>
			</table>
		</div>
		
<?php 	
		return ob_get_clean();
	}
	
	
	private static function lineInput($type, $name, $label, $value=null, $required = false){
		$required = $required ? "required" : null;
		return "<input type=\"$type\" class=\"form-control\" name=\"$name\" id=\"$name\" value=\"$value\" $required/>";
	}
	
	private static function textareaInput($name, $label, $value=null , $required = false){
		$required = $required ? "required" : null;
		return "<textarea class=\"form-control\" rows=\"5\" name=\"$name\" id=\"$name\" $required>$value</textarea>";
	}
	
	
}
?> 