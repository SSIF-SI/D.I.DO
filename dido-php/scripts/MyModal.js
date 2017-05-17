var MyModal = {
		MyModalId: 'MyModal',
		oldClass: null,
		span: null,
		newClass: "fa fa-refresh fa-spin fa-1x fa-fw",
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
						'<div class="modal-footer">'+
						'<div style="visibility:hidden" class="progress progress-striped active"><div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div></div>' +
						'<div class="modal-result"></div>'+
						'<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-backward fa-1x fa-fw"></span> Torna indietro</button>'+
						'</div>'+
						'</div>'+
						'</div>'+
				'</div>').appendTo("#page-wrapper");
				$('#'+MyModal.MyModalId).on('hidden.bs.modal', function (e) {
					$('#'+MyModal.MyModalId).remove();
					MyModal.busy = false;
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
			MyModal.init();
			$('#'+MyModal.MyModalId+' .modal-body').html(html);
		},
		load: function(anchor, callbackSuccess, callbackFailure){
			MyModal.init();
			if(anchor.prop('href') != undefined){
				var href = anchor.prop('href');

				MyModal.span = anchor.children("span");
				MyModal.oldClass = MyModal.span.prop('class');
				
				if(MyModal.busy == false){
					MyModal.busy = true;
					MyModal.span.attr('class', MyModal.newClass);

					$.ajax({
						url: href, 
						success: function( result ) {
							MyModal.busy = false;
							$('#'+MyModal.MyModalId+' .modal-body').html(result);
							MyModal.modal();
							if (callbackSuccess && typeof(callbackSuccess) === "function") callbackSuccess(result);
						},error: function( result ){
							MyModal.busy = false;
							MyModal.error("Errore imprevisto");
							if (callbackFailure && typeof(callbackFailure) === "function") callbackFailure(result);
						}
					});
				} 
			}
		},
		editModal: function(context, contentSet){
			var href=$(context).prop("href");
			MyModal.addButtons(
					[
					 {id:"salva", type: "submit", label: "Salva", cssClass: "btn-primary", spanClass: "fa-save", callback: function(){;
					 MyModal.submit({
						  element:$(context),
						  href:href,
						  data:$('#'+MyModal.MyModalId+' form').serializeArray()
					 });
					 }
					 }
					 ]
			);
			if(contentSet == undefined || !contentSet) 
				MyModal.load($(context));
			else 
				MyModal.modal();
		},
		deleteModal: function(context){
			var href=$(context).prop("href");
			MyModal.addButtons(
					[
					 {id:"Elimina", type: "submit", label: "Elimina", cssClass: "btn-danger", spanClass: "fa-trash-o", callback: function(){;
					 MyModal.submit({
						 element: $(context),
						 href: href});
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
					 {id:"Firma", type: "submit", label: "Firma", cssClass: "btn-primary", spanClass: "fa-pencil", callback: function(){	
						 
						 var formData = new FormData($('#firmatario')[0]);
						 $('#pdfDaFirmare').fileinput('upload');
						 $('#keystore').fileinput('upload');
						 formData.append('pdfDaFirmare', $('#pdfDaFirmare')[0].files[0]); 
						 formData.append('keystore', $('#keystore')[0].files[0]);
						 
						 MyModal.submit({
							 element: $(context),
							 href: href.replace('signature.php','signPdf.php'),
							 data: formData,
							 innerdiv: ' .modal-result', 
							 download: true
						 });
						
					 }
					 }
					 ]
			);
			MyModal.load($(context));
		},
		submit:function (params){
			// params:
			// (element,href, data, innerdiv, contentType, processData,callback,download)
			if(MyModal.busy == false){
				MyModal.busy = true;

				$('.modal-result').html("");
				$('.modal .progress').css("visibility", 'visible');
				if(params.callback == undefined)
					MyModal.setProgress(1);
				
				if(MyModal.checkRequired(params.data, params.innerdiv)){
					/*
					MyModal.span = element.children("span");
					MyModal.oldClass = MyModal.span.prop('class');
					
					MyModal.span.attr('class', MyModal.newClass);
					*/
					$('#'+MyModal.MyModalId+' button[data-dismiss="modal"]').prop('disabled', true);
					if(params.download){
						$('.modal-body #firmatario').submit();
						$('#download_iframe').on('load', function(){
					        // controlli
					    });
					}
					else{
						MyModal.ajax(params);
					}
				} else {
					MyModal.busy = false;
				}
			}
		}, 
		ajax: function(params){
			$.ajax({
				xhr: function() {
					var xhr = new window.XMLHttpRequest();

					xhr.addEventListener("progress", function(evt) {
						if (evt.lengthComputable && params.callback == undefined || !params.callback) {
							var percentComplete = evt.loaded / evt.total * 100;
							MyModal.setProgress(percentComplete);
						}
					}, false);
					return xhr;
				},
				url: params.href,
				type: "POST", 
				dataType: "json",
				contentType: params.contentType,
				cache: false,
				processData: params.processData,
				data: params.data,
				success: function( result ) {
					MyModal.busy = false;
					if(params.callback != undefined && params.callback){
						return params.callback(result);
					} else {
						MyModal.unlockButtons();
						if(result.errors){
							MyModal.error(result.errors, params.innerdiv);
							$('.modal .progress').css("visibility", 'hidden');
						} else {
							MyModal.success(params.innerdiv);
							$('#'+MyModal.MyModalId+' button[type="submit"]').remove();
							$('#'+MyModal.MyModalId+' button[data-dismiss="modal"]').click(function(){
								
								$("<h4>Attendere... <i class=\"fa fa-refresh fa-spin fa-1x fa-fw\"></i></h4>").appendTo(".modal-footer");
								$('#'+MyModal.MyModalId+' button[data-dismiss="modal"]').remove();
								location.reload(true);
							});
						}
					}
				},
				error: function( result ){
					MyModal.busy = false;
					if(params.callback != undefined && params.callback){
						return params.callback(false);
					} else {
						$('.modal .progress').css("visibility", 'hidden');
						$('#'+MyModal.MyModalId+' button[data-dismiss="modal"]').prop('disabled', false);
						if(MyModal.span != null )
							MyModal.span.attr('class', MyModal.oldClass);
						MyModal.error("Errore imprevisto", params.innerdiv);
					}
				}
			});
		},
		unlockButtons: function(){
			$('#'+MyModal.MyModalId+' button[data-dismiss="modal"]').prop('disabled', false);
			if(MyModal.span != null )
				MyModal.span.attr('class', MyModal.oldClass);
		},
		setProgress: function(percentage){
			percentage = percentage > 100 ? 100 : percentage;
			$('.modal .progress-bar').css("width", percentage+'%');
		},
		checkRequired: function (data, innerdiv){
			if(data == undefined || data == null)
				return true;
			var requiredFields = [];
			for(var i = 0; i< data.length ; i++){
				if(data[i].name.indexOf("[]") !== -1)
					continue;
				var el = $('#'+data[i].name);
				var required = el.attr('required');
				if(required != undefined && data[i].value.trim().length == 0){
					requiredFields.push(data[i].name);
					el.addClass("alert-danger");
				} else {
					el.removeClass("alert-danger");
				}
			}
			if( requiredFields.length > 0){
				MyModal.error("Compilare i seguenti campi obbligatori: "+requiredFields.join(), innerdiv);
				return false;
			} else {
				return true;
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
					"<div style='word-wrap: break-word; text-align:left !important' class=\"alert alert-danger \"><p><span class=\"fa fa-warning\">&nbsp;</span> Attenzione, operazione non riuscita<br/><br/>"+message+"</p></div>" : 
			"<div class=\"alert alert-success\"><p><span class=\"glyphicon glyphicon-ok\">&nbsp;</span> Operazione andata a buon fine</p></div>");
		},
		modal: function(){
			MyModal.init();
			if(MyModal.span != null )
				MyModal.span.attr('class', MyModal.oldClass);
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