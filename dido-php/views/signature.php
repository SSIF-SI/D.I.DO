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
				<div id="dataTables-example_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
					<div class="row">
						<div class="col-sm-6">
							<div class="dataTables_length" id="dataTables-example_length">
								<label>Mostra <select name="dataTables-example_length"
									class="form-control input-sm">
										<option value="10">10</option>
										<option value="25">25</option>
										<option value="50">50</option>
										<option value="100">100</option>
								</select> risultati
								</label>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<table
								class="table table-striped table-bordered table-hover dataTable no-footer dtr-inline"
								id="dataTables-example" style="width: 100%;">
								<thead>
									<tr>
              				<?php foreach ($arrayk as $column):?>
							<th class="sorting" tabindex="0" rowspan="1" colspan="1"
											style="width: 271px;"><?= ucfirst(trim($column))?></th>							
							<?php endforeach; ?>
							</tr>
								</thead>
								<tbody>
                           <?php foreach ($allsignatures as $row):?>
                            <tr style="word-wrap: break-word;">
										<td class=""><?=$row["sigla"]?></td>
										<td class="sorting_1"><?= $row["descrizione"]?></td>
										<td class="center"><?= $row["id_persona"]?></td>
										<td style="word-wrap: break-word;min-width: 160px;max-width: 160px;"><?= $row["pkey"]?></td>
										<td class="center"><?= $row["id_delegato"]?></td>
										<td style="word-wrap: break-word; min-width: 160px;max-width: 160px;"><?= $row["pkey_delegato"]?></td>
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




