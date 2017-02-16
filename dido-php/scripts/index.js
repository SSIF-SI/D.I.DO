$(document).ready(function(){
	$('a.import').click(function (e) {
		e.preventDefault();
		var formId = $(this).attr('href').replace("#","");
		var data = $('form#'+formId).serializeArray();
		MyModal.setTitle("Importazione ");
		MyModal.setContent("<h4>Attendere, importazione in corso... <i class=\"fa fa-refresh fa-spin fa-1x fa-fw\"></i></h4>");
		MyModal.modal();
		MyModal.submit($(this), null, data, '.modal-body');
	});
	
	$('a.edit-info').click(function (e) {
		e.preventDefault();
		MyModal.setTitle($(this).html());
		MyModal.editModal(this);
	});
	
	$('.select-one').click(function(e){
		var checked = false;
		var panel = $(this).attr('rel');
		$('.'+panel+' .select-one').each(function(){
			if($(this).prop('checked')){
				checked = true;
				return false;
			}
		});
		$('.'+panel+' .select-all').prop('checked',false);
		toggleActionButtons(panel, checked);
	});
	
	$('.select-all').click(function(e){
		var checked = $(this).prop('checked');
		var panel = $(this).attr('rel');
		var els = $('.'+panel+' .select-one');
		els.each(function(){
			$(this).prop('checked', checked);
		});
		toggleActionButtons(panel, checked);
	});
	
	function toggleActionButtons(panel, checked){
		$('.'+panel+' .action').each(function(){ 
			if(checked) $(this).removeClass('disabled');
			else $(this).addClass('disabled');
		});
	}
	
	$('.link-selected').click(function(e){
		e.preventDefault();
		
	});

	$('.import-selected').click(function(e){
		e.preventDefault();
		var selected = $('.select-one:checkbox:checked');
		selected.each(function(index){
			var id_formToImport = $(selected[index]).prop('id').replace(/^form-/,"importform-");
			var form = $('#'+id_formToImport);
			
		})
	});
	
	$('.import-selected').click(function(e){
		e.preventDefault();
		var selected = $('.select-one:checkbox:checked');
		MultiImport.start($(this), selected);
	});
	
	var MultiImport = {
		context: null,
		errors: [],
		forms: [],
		imported: 0,
		start: function (context, selected){
			MultiImport.context = context;
			selected.each(function(index){
				var id_formToImport = $(selected[index]).prop('id').replace(/^form-/,"importform-");
				var form = $('#'+id_formToImport);
				MultiImport.forms.push(form);
			})
			MyModal.setContent("<h4>Attendere, importazione in corso... <i class=\"fa fa-refresh fa-spin fa-1x fa-fw\"></i></h4>");
			MyModal.setProgress(1);
			MyModal.modal();
			MultiImport.import(MultiImport.forms[MultiImport.imported]);
		},
		more: function(result){
			if(result == false || result.errors != false)
			MultiImport.errors.push("Errore durante l'importazione del documento "+(MultiImport.imported+1)+"; "+(result.errors != undefined ? result.errors : null));
			MultiImport.imported++;
			MyModal.setProgress(MultiImport.imported/MultiImport.forms.length*100);
			if(MultiImport.imported == MultiImport.forms.length) {
				MyModal.setTitle("Importazione terminata");
				MyModal.setContent("");
				MyModal.unlockButtons();
				
				if(MultiImport.errors.length == 0)
					MyModal.success();
				else
					MyModal.error(MultiImport.errors.join("<hr/>\n"));
				
				$('#'+MyModal.MyModalId+' button[data-dismiss="modal"]').click(function(){
					$("<h4>Attendere... <i class=\"fa fa-refresh fa-spin fa-1x fa-fw\"></i></h4>").appendTo(".modal-footer");
					$(this).remove();
					location.reload();
				});
			} else {
				MultiImport.import(MultiImport.forms[MultiImport.imported]);
			}
		},
		import(item){
			MyModal.setTitle("Importazione ("+(MultiImport.imported+1)+" di "+MultiImport.forms.length+")");
			var data = item.serializeArray();
			MyModal.submit(MultiImport.context, null, data, '.modal-body',undefined, undefined, MultiImport.more);
		}
	}
});