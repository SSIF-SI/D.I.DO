var MyModal = {
	MyModalId: 'MyModal', 
	busy: false,
	init: function(){
		if( $('#'+MyModal.MyModalId).length == 0 ){
			$(  '<div class="modal fade" id="'+MyModal.MyModalId+'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">'+
					'<div class="modal-dialog">'+
						'<div class="modal-content">'+
							'<div class="modal-header">'+
								'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>'+
								'<h4 class="modal-title" id="myModalLabel"></h4>'+
							'</div>'+
							'<div class="modal-body"></div>'+
							'<div class="modal-result"></div>'+
							'<div class="modal-footer">'+
								'<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-backward fa-1x fa-fw"></span> Torna indietro</button>'+
							'</div>'+
			            '</div>'+
					'</div>'+
				'</div>').appendTo("#page-wrapper");
			$('#'+MyModal.MyModalId).on('hidden.bs.modal', function (e) {
				$('#'+MyModal.MyModalId).remove();
			});
		}
	},
	setTitle: function(title){
		MyModal.init();
		$('#'+MyModal.MyModalId+' .modal-title').html(title);
	},
	addButtons: function(buttons){
		for (i in buttons){
			var button = buttons[i];
			
			var htmlButton = $('<button type="'+(button.type == undefined ? 'button' : button.type)+'" class="btn '+(button.cssClass == undefined ? 'btn-default' : button.cssClass)+'" id="'+button.id+'" '+(button.moreData == undefined ? '' : button.moreData)+'><span class="fa '+(button.spanClass == undefined ? 'fa-arrow-circle-right' : button.spanClass)+' fa-1x fa-fw"></span> '+button.label+'</button>')
				.appendTo('.modal-footer');
			if (button.callback  && typeof(button.callback) === "function"){
				htmlButton.click( button.callback );
			}
		}
	},
	setContent: function(html){
		$('#'+MyModal.MyModalId+' .modal-body').html(html);
	},
	load: function(anchor, callbackSuccess, callbackFailure){
		MyModal.init();
		if(anchor.prop('href') != undefined){
			var href = anchor.prop('href');
	
			var span = anchor.children("span");
			var oldClass = span.prop('class');
			var newClass = "fa fa-refresh fa-spin fa-1x fa-fw";
	
			if(MyModal.busy == false){
				MyModal.busy = true;
				span.attr('class', newClass);

				$.ajax({
					url: href, 
					success: function( result ) {
						MyModal.busy = false;
						span.attr('class', oldClass);
						$('#'+MyModal.MyModalId+' .modal-body').html(result);
						MyModal.modal();
						if (callbackSuccess && typeof(callbackSuccess) === "function") callbackSuccess(result);
					},error: function( result ){
						MyModal.busy = false;
						span.attr('class', oldClass);
						MyModal.error("Errore imprevisto");
						if (callbackFailure && typeof(callbackFailure) === "function") callbackFailure(result);
					}
				});
			} 
		}
	},
	editModal: function(context){
		var href=$(context).prop("href");
		MyModal.addButtons(
			[
				{id:"salva", type: "submit", label: "Salva", cssClass: "btn-primary", spanClass: "fa-save", callback: function(){;
					MyModal.submit($(context),href,$('form').serializeArray());
					}
				}
			]
		);
		MyModal.load($(context));
	},
	deleteModal: function(context){
		var href=$(context).prop("href");
		MyModal.addButtons(
			[
				{id:"Elimina", type: "submit", label: "Elimina", cssClass: "btn-danger", spanClass: "fa-trash-o", callback: function(){;
					MyModal.submit($(context),href,null);
					}
				}
			]
		);
		MyModal.setContent("<label for=\"conferma\">Confermi:</label><p id=\"conferma\">Sei sicuro di voler eliminare l'elemento?</p>");
		MyModal.modal();
	},
	signModal: function(context){
		var href=$(context).prop("href");
		MyModal.addButtons(
			[
				{id:"Firma", type: "submit", label: "Firma", cssClass: "btn-primary", spanClass: "fa-pencil", callback: function(){;
				MyModal.submit($(context),href.replace('signature.php','signPdf.php'),$('form').serializeArray());
					}
				}
			]
		);
		MyModal.load($(context));
	},
	submit:function (element,href, data, innerdiv){
		var span = element.children("span");
		var oldClass = span.prop('class');
		var newClass = "fa fa-refresh fa-spin fa-1x fa-fw";
		if(MyModal.busy == false){
			
			MyModal.busy = true;
			span.attr('class', newClass);
			$('#'+MyModal.MyModalId+' button[data-dismiss="modal"]').prop('disabled', true);
			$.ajax({
				url: href,
				type: "POST", 
				dataType: "json",
				data: data,
				success: function( result ) {
					$('#'+MyModal.MyModalId+' button[data-dismiss="modal"]').prop('disabled', false);
					MyModal.busy = false;
					span.attr('class', oldClass);
					if(result.errors){
						MyModal.error(result.errors, innerdiv);
					} else {
						MyModal.success(innerdiv);
						$('#'+MyModal.MyModalId+' button[type="submit"]').remove();
						$('#'+MyModal.MyModalId+' button[data-dismiss="modal"]').click(function(){
							$("<h4>Attendere... <i class=\"fa fa-refresh fa-spin fa-1x fa-fw\"></i></h4>").appendTo(".modal-footer");
							$(this).remove();
							location.reload();
						});
					}
				},
				error: function( result ){
					$('#'+MyModal.MyModalId+' button[data-dismiss="modal"]').prop('disabled', false);
					MyModal.busy = false;
					span.attr('class', oldClass);
					MyModal.error("Errore imprevisto", innerdiv);
				}
			});
		} 
	},
	error: function (message, innerdiv){
		MyModal._resultMessage(message, true, innerdiv);
		MyModal.modal();
	},
	success: function(innerdiv){
		MyModal._resultMessage(null, false, innerdiv);
		MyModal.modal();
	},
	_resultMessage: function(message, error, innerdiv){
		$('#'+MyModal.MyModalId+(innerdiv == undefined ? ' .modal-result' : ' '+innerdiv))
			.html(error ? 
				"<div style='word-wrap: break-word;' class=\"alert alert-danger\"><p><span class=\"fa fa-warning\">&nbsp;</span> Attenzione, operazione non riuscita<br/><br/>"+message+"</p></div>" : 
				"<div class=\"alert alert-success\"><p><span class=\"glyphicon glyphicon-ok\">&nbsp;</span> Operazione andata a buon fine</p></div>");
	},
	modal: function(){
		if(!$('#'+MyModal.MyModalId).is(':visible')){
			$('#'+MyModal.MyModalId).modal({
				backdrop: 'static'
			});
		}
	},
	close: function(){
		$('#'+MyModal.MyModalId).modal('hide');
	}	
};