
<div class="row">
	<div class="col-lg-12">
		<h1 class="page-header">Dashboard</h1>
	</div>
</div>
<div class="row">
	                <?php TemplateHelper::createDashboardPanels($md);?>
	            </div>
<!--
				<div class="row">
                    <div class="col-lg-12">
                		<?=$result?>
                    </div>
                </div>
                <form role="form" method="POST" name="form2" enctype="multipart/form-data">
	                <div class="row">
	                	<?php foreach($inputs as $input): ?>
	                	<div class="col-lg-4">
	                     	<?=$input?>
	                  	</div>
	                    <?php endforeach; ?>
	                </div>
	                <div class="row">
	                	<div class="col-lg-12">
	                	<?php echo HTMLHelper::input("file", "fileToUpload", "File da caricare",$_FILES['fileToUpload']['name'],"file",true)?>
	                	</div>
	                </div>
	                <br/>
	                <button class="btn btn-primary" type="submit">Salva</button>
                </form>
                 -->
<div class="row">
		<?=$detail?>
	</div>
</div>