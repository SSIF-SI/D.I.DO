
<div class="row">
	<div class="col-lg-12">
                		<?=$result?>
                    </div>
</div>
<form role="form" method="POST" name="form2"
	enctype="multipart/form-data">
	<div class="row">
	                	<?php foreach($inputs as $input) echo $input;?>
	                </div>
	<br />
	<button class="btn btn-primary" type="submit">Salva</button>
</form>