<div class="row">
	<div class="col-lg-12">
		<h1 class="page-header">Firmatari</h1>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading">Firmatari</div>
				<!-- /.panel-heading -->
				<div class="panel-body">
					<div class="table-responsive">
						<table width="100%" class="col-lg-12 table table-striped table-bordered table-hover dataTable no-footer dtr-inline" 
						id="signature-table" data-id-field="id_persona" role="grid">
							<thead>
								<tr role="row">
							 <?php foreach ($arrayk as $column):?>
								<th class="<?php $column=="sigla"?"sorting_asc":"sorting"?>" tabindex="0" aria-controls="dataTables-example" rowspan="1" colspan="1" aria-sort="ascending" aria-label="<?= ucfirst(trim($column))?>: activate to sort column ascending"tabindex="0" id="<?=trim($column)?>"><?= ucfirst(trim($column))?></th>
							<?php endforeach; ?>
							</tr>
							</thead>
							<tbody>
						 	<?php foreach ($allsignatures as $row):?>
						 	<?php $oddeven=$oddeven=="odd"?"even":"odd"?>
							<tr class="gradeA <?=$oddeven?>" role="row">
									<td class="sorting_1"><?=$row["sigla"]?></td>
									<td><?=$row["descrizione"]?></td>
									<td><?=$row["id_persona"]?></td>
									<td><?=$row["pkey"]?></td>
									<td><?=$row["id_delegato"]?></td>
									<td><?=$row["pkey_delegato"]?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>