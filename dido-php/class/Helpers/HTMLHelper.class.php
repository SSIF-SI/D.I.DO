<?php 
class HTMLHelper{
	static function select($name, $label, $options, $selected = null, $class = null, $isImported = false){
		ob_start();
?>
<div class="form-group <?=$class?>">
	<label class="control-label" for="<?=$name?>"><?=$label?>:</label>
	<select class="form-control" <?php if(!$isImported):?> id="<?=$name?>" name="<?=$name?>"<?php else:?> disabled="disabled"<?php endif;?>>
<?php if(!$isImported):?>
		<option value="">---Scegli---</option>
<?php endif;?>

<?php	foreach ($options as $value=>$optlabel): ?>
		<option value="<?=$value?>" <?=$value == $selected ? "selected" : ""?>><?=$optlabel?></option>
<?php 	endforeach;?>
	</select>
<?php if($isImported) echo self::lineInput("hidden", $name, $label, $selected, $class, false);?>
</div>
<?php
		return ob_get_clean();
	}
	
	static function input($type, $name, $label, $value=null, $class = null, $required = false, $isImported = false){
		ob_start();
		if($type == 'hidden' || $type == 'file'):
			if($type != 'hidden' && !is_null($label)):
?>
    <label class="control-label" for="<?=$name?>"><?=$label?>:</label>
<?php 		
			endif;
			echo self::lineInput($type, $name, $label, $value, $class, $required, $isImported);
		else :
			$innerInput = $type == 'textarea' ? self::textareaInput($name, $label, $value, $class, $required) : self::lineInput($type, $name, $label, $value, $class, $required, $isImported);
?>
	<div class="form-group <?=$class?>">
<?php 	if($type != 'hidden'):?>	
	    <label class="control-label" for="<?=$name?>"><?=$label?>:</label>
<?php 	endif; ?>
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
		return "<div class=\"alert alert-danger\"><p><span class=\"fa fa-warning\">&nbsp;</span><strong>Attenzione!</strong> Sono stati riscontrati i seguenti errori:</p><p>$errors</p></div>";
	}
	
	public static function saveSuccessBox(){
		return "<div class=\"alert alert-success\"><p><span class=\"glyphicon glyphicon-ok\">&nbsp;</span> Dati salvati con successo</p></div>";
	}
	
	public static function editTable($list, $buttons = array(), $substitutes = null, $key_replace = null, $hidden = null){
		ob_start();

		if(reset($list)){?>
		<div class="table-responsive">
			<table class="table table-condensed table-striped">
				<thead>
					<tr>
					<?php 	foreach(array_keys(reset($list)) as $key):
						if(isset($hidden) && in_array($key,$hidden)) continue;?>
						<th><?=isset($key_replace[$key]) ? $key_replace[$key] : ucfirst(str_replace("id_","",$key))?></tH>
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
							<a class="btn btn-<?=$button['type']?> <?=$button['class']?>" href="<?=$button['href']?>">
								<span class="fa fa-<?=$button['icon']?> fa-1x fa-fw"></span> <?=$label?>
							</a>
							<?php endforeach;?>
						</td>
					</tr>
					<?php endforeach;?>
					
				</tbody>
			</table>
		</div>
			<?php }else{?> <div class="alert alert-warning">Tabella vuota</div><?php }?>
<?php 	
		return ob_get_clean();
	}
	
	
	private static function lineInput($type, $name, $label, $value=null, $class = null, $required = false, $isImported = false){
		$more = $type == 'file' ? 'data-show-upload="false" data-allowed-file-extensions=\'["pdf", "p7m"]\'': null ;
		$more .= $isImported ? " readonly=\"readonly\"" : null ;
		$required = $required ? "required" : null;
		$class = $type == "hidden" ? null : "class=\"form-control $class\"";
		if(isImported && $type == "data"){
			$valueMod = Utils::convertDateFormat($value, "d/m/Y", DB_DATE_FORMAT);
			return "<input type=\"text\" $class value=\"$value\" $required $more/>\n" .
				   "<input type=\"hidden\" name=\"$name\" id=\"$name\" value=\"$valueMod\"/>";
		} else {
			return "<input type=\"$type\" $class name=\"$name\" id=\"$name\" value=\"$value\" $required $more/>";
		}
	}
	
	private static function textareaInput($name, $label, $value=null , $required = false, $isImported = false){
		$more = $isImported ? " readonly=\"readonly\"" : null ;
		$required = $required ? "required" : null;
		return "<textarea class=\"form-control\" rows=\"5\" name=\"$name\" id=\"$name\" $required $more>$value</textarea>";
	}
	
	static function createMetadata($list, $href, $pKeys, $substitutes_keys){
		$substitutes = array();
		$buttons = array();
		
		if(stripos($href, "?") === false) 
			$href .= "?";
		
		foreach($list as $k=>$item){
			foreach($substitutes_keys as $key=>$callback){
				$substitutes[$k][$key] = call_user_func($callback,$item[$key]);
			}
	
			$suffix = array();
			
			foreach($pKeys as $key)
				array_push($suffix, $key."=".$item[$key]);
			$suffix = "&".join("&",$suffix);
			
			$buttons[$k] = array(
					'Modifica'	=> array(
							'type' => 'primary',
							'href' => BUSINESS_HTTP_PATH.$href.$suffix,
							'icon' => 'pencil',
							'class'=> 'mymodal edit'),
					'Elimina'	=> array(
							'type' => 'danger',
							'href' => BUSINESS_HTTP_PATH.$href.$suffix."&delete",
							'icon' => 'trash',
							'class'=> 'mymodal delete')
			);
		}
	
		return array('substitutes' => $substitutes, 'buttons' => $buttons);
	}
	
	static function createDetailFromObj($obj, $xml, $type){
		$XMLParser = XMLParser::getInstance();
		$XMLParser->setXMLSource($xml,$type);
		$inputs = $XMLParser->getMasterDocumentInputs();
		
		return FormHelper::createInputsFromXml($inputs,4,$obj);
	}
}
?> 