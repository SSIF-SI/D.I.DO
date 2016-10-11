<div class="row">
	<div class="col-lg-12">
		<h1 class="page-header">Firmatari</h1>
	</div>
	<!-- /.col-lg-12 -->
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">Firmatari</div>
			<!-- /.panel-heading -->
			<div class="panel-body">
				<div id="signature_dataTable"
					class="dataTables_wrapper form-inline dt-bootstrap no-footer">
					<button class="btn btn-primary" type="create" tabindex="0" aria-controls="example" href="#"><span>Nuovo</span></button>
					<button class="btn btn-primary" type="modify" tabindex="0" aria-controls="example" href="#"><span>Modifica</span></button>
					<button class="btn btn-primary" type="delete" tabindex="0" aria-controls="example" href="#"><span>Cancella</span></button>
					<div class="row">
						<div class="col-sm-12">
							<table
								class="table table-striped table-bordered table-hover dataTable no-footer dtr-inline"
								id="signature-dataTable" style="width: 100%;">
								<thead>
									<tr>
              				<?php foreach ($arrayk as $column):?>
							<th class="sorting" tabindex="0"
											aria-controls="signature_dataTable" rowspan="1" colspan="1"
											aria-label="<?= ucfirst(trim($column))?>: activate to sort column ascending"
											style="width: 325px;"><?= ucfirst(trim($column))?></th>							
							<?php endforeach; ?>
							</tr>
								</thead>
								<tbody>
                           <?php foreach ($allsignatures as $row):?>
                           <?php $oddeven=$oddeven=="odd"?"even":"odd"?>
                            <tr class="gradeA <?=$oddeven?>" role="row"
										style="word-wrap: break-word;">
										<td class="sorting_1"><?=$row["sigla"]?></td>
										<td class="center"><?= $row["descrizione"]?></td>
										<td class="center"><?= $row["id_persona"]?></td>
										<td
											style="word-wrap: break-word; min-width: 160px; max-width: 160px;"><?= $row["pkey"]?></td>
										<td class="center"><?= $row["id_delegato"]?></td>
										<td
											style="word-wrap: break-word; min-width: 160px; max-width: 160px;"><?= $row["pkey_delegato"]?></td>
									</tr>
							<?php endforeach; ?>
                                  
                        </tbody>
							</table>
						</div>
						<!-- /.table-responsive -->
					</div>
					<!-- /.panel-body -->
				</div>
				<!-- /.panel -->
			</div>
		</div>
	</div>
</div>