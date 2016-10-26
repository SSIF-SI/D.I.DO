$(document).ready(function(){
	var loading = false;
	
	if(location.hash) {
		$('.nav-tabs a[href="' + location.hash + '"]').tab('show');
    }
	
	$(document.body).on("click", "a[data-toggle]", function(event) {
        location.hash = this.getAttribute("href");
    });
    
    
	$("a.mymodal.edit").click(function (e){
		e.preventDefault();
		var href = $(this).attr('href');
		
		var span = $(this).children("span");
		var oldClass = span.attr('class');
		var newClass = "fa fa-refresh fa-spin fa-1x fa-fw";
		
		if(!loading){
			
			loading = true;
			span.attr('class', newClass);
			
			// fa fa-cog fa-spin fa-3x fa-fw
			$.ajax({
				url: href, 
				success: function( result ) {
					loading = false;
					span.attr('class', oldClass);
					
					// Recupero il contenuto della finestra modale tramite GET
					// e lo metto nel div.modal-content, quindi mostro la finestra
					$("#myModal .modal-content").html(result);
					$("#myModal").modal({
						backdrop: 'static'
					});
					
					var modal_span = $('#myModal button[type="submit"]').children("span");
					var modal_class = modal_span.attr('class');
					
					// Intercetto la sottomissione della finestra modale e continuo a fare tutto
					// via AJAX passando in POST i parametri
					$("form").submit(function(e){
						if(!loading){
							e.preventDefault();
							loading = true;
							modal_span.attr('class', newClass);
							
							
							$.ajax({
								url: href, 
								type: "POST", 
								data: $(this).serializeArray(), 
								dataType: "json",
								success: function(result){ 
									loading = false;
									modal_span.attr('class', modal_class);
									
									if(result.errors){
										$('#myModal .modal-result').html("<div class=\"alert alert-danger\"><p><span class=\"fa fa-warning\">&nbsp;</span> Attenzione, operazione non riuscita<br/><br/>"+result.errors+"</p></div>");
									} else {
										$('#myModal .modal-result').html("<div class=\"alert alert-success\"><p><span class=\"glyphicon glyphicon-ok\">&nbsp;</span> Dati salvati con successo</p></div>");
										$('#myModal button[type="submit"]').remove();
										$('#myModal button[data-dismiss="modal"]').click(function(){
											$("#myModal").modal('hide');
											location.reload();
										});
									}
								},
								error: function(){
									loading = false;
									modal_span.attr('class', modal_class);
									alert("Errore imprevisto");
								}
							});
						}
					});
				},
				error: function(){
					loading = false;
					span.attr('class', oldClass);
					alert("Errore imprevisto");
				}
			});
		}
	});
	$("a.mymodal.delete").click(function (e){
		e.preventDefault();
		var href = $(this).attr('href');
		
		var span = $(this).children("span");
		var oldClass = span.attr('class');
		var newClass = "fa fa-refresh fa-spin fa-1x fa-fw";
		
		if(!loading){
			if(confirm("Sei sicuro ?")){
				loading = true;
				span.attr('class', newClass);
				$.ajax({
					url: href, 
					dataType: "json",
					success: function(result){ 
						loading = false;
						span.attr('class', oldClass);
						if(result.errors){
							alert("<div class=\"alert alert-danger\"><p><span class=\"glyphicon glyphicon-remove\">&nbsp;</span> Attenzione, eliminazione non riuscita<br/><br/>"+result.errors+"</p></div>");
						} else {
							location.reload();
						}
					},
					error: function(){
						loading = false;
						span.attr('class', oldClass);
						alert("Errore imprevisto");
					}
				});
			}
		}
	});
	
	$('#myModal').on('hidden.bs.modal', function (e) {
		$("#myModal .modal-content").html("");
	})

});