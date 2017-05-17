$(document).ready(function(){
	
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
	
	$('a.import').click(function (e) {
		e.preventDefault();
		var formId = $(this).attr('href').replace("#","");
		var data = $('form#'+formId).serializeArray();
		MyModal.setTitle("Importazione ");
		MyModal.setContent("<h4>Attendere, importazione in corso... <i class=\"fa fa-refresh fa-spin fa-1x fa-fw\"></i></h4>");
		MyModal.modal();
		MyModal.submit({
			element: $(this),
			data: data, 
			innerdiv: '.modal-body'
		});
	});
	
	$('.select-one').click(function(e){
		var panel = $(this).attr('rel');
		var count = 0;
		$('.'+panel+' .select-one').each(function(){
			if($(this).prop('checked')){
				count++;
			}
		});
		$('.'+panel+' .select-all').prop('checked',false);
		toggleActionButtons(panel, count > 1);
	});
	
	
	
	$('.link-selected').click(function(e){
		e.preventDefault();
		var sectionId = $(this).attr('href');
		var selected = $(sectionId+' .select-one:checkbox:checked');
		
		var table = $("<table></table>");
		
		var thead = $(sectionId+" thead").clone();
		thead.find("th").last().html("");
		thead.find("th").first().html("");
		
		thead.appendTo(table);
		
		tr = [];
		selected.each(function(el){
			tr.push($("<tr>"+$(this).closest("tr").html()+"</tr>"));
		});
		
		var info = [];
		for(var i in tr){
			var form = $(tr[i]).find("td").last().find("form").first();
			
			var input = form.find("input");
			input.each(function(el){
				$(this).prop("name",$(this).prop("name")+"[]");
			});
			
			info.push(input);
			
			$(tr[i]).find("td").last().remove();
			$(tr[i]).find("td").first().html('');
			var radio = $('<input>',{type:"radio", id:"principale"+i, name:"principale", value: i});
			if(i==0) radio.attr("checked","checked");
			radio.appendTo($(tr[i]).find("td").first());
			$(tr[i]).appendTo(table);
		}
		
		var tinfo = "<p>Seleziona il Documento principale dal quale verranno salvate le informazioni. I restanti documenti saranno caricati come allegati.</p>";
		
		var MI_form = $("<form></form>");
		
		for (var i in info){
			info[i].each(function(el){
				$(this).appendTo(MI_form);
			});
		}
		
		$("<input type='hidden' name='multiImport' value='1'/>").appendTo(MI_form);
		
		MyModal.setTitle("Collegamento e importazione documenti");
		MyModal.setContent("<form method='POST'><table class='table table-condensed table-striped'>"+table.html()+"</table>"+tinfo+MI_form.html()+"</form>");
		MyModal.editModal(this, true);
		
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
			});
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
					$('#'+MyModal.MyModalId+' button[data-dismiss="modal"]').remove();
					location.reload(true);
				});
			} else {
				MultiImport.import(MultiImport.forms[MultiImport.imported]);
			}
		},
		import: function(item){
			MyModal.setTitle("Importazione ("+(MultiImport.imported+1)+" di "+MultiImport.forms.length+")");
			var data = item.serializeArray();
			MyModal.submit({
				element:MultiImport.context, 
				data: data, 
				innerdiv: '.modal-body',
				callback: MultiImport.more
			});
			// element,href, data, innerdiv, contentType, processData,callback,download)
		}
	}
	
});