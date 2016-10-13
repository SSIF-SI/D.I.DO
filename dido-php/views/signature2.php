				<div class="row">
                    <div class="col-lg-12">
                    	<h1 class="page-header">Firmatari</h1>
                	</div>
                    <!-- /.col-lg-12 -->
                </div>
                <div class="row">
                    <div class="col-lg-12">
	                    <div class="table-responsive">
							<table class="table table-condensed table-striped">
								<thead>
									<tr>
									<?php foreach(array_keys(reset($signers)) as $key):?>
									<?php if(in_array($key,$hidden)) continue;?>
										<th><?=ucfirst(str_replace("id_","",$key))?></tH>
									<?php endforeach;?>
										<th></th>
									</tr>
								<tbody>
									<?php foreach($signers as $key=>$row):?>
									<tr id="row-<?=$row['variable'] ? 'variable' : 'fixed'?>-<?=$row['id_item']?>">
										<?php foreach($row as $field=>$value):?>
											<?php if(in_array($field,$hidden)) continue;?>
										<td><?=isset($substitutes[$key][$field]) ? $substitutes[$key][$field] : $value?></td>
										<?php endforeach;?>
										<th><a class="btn btn-primary" href="<?=BUSINESS_HTTP_PATH."editSigner.php?id_item={$row['id_item']}&variable=".(int)$row['variable']?>">Edit</a>
									</tr>
									<?php endforeach;?>
								</tbody>
							</table>
						</div>
                    </div>
                </div>
