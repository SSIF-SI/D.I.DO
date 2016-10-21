$(document).ready(function(){
	var loading = false;
	
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
					$("#myModal").modal();
					
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
								success: function(result){ 
									loading = false;
									modal_span.attr('class', modal_class);
									
									if(result.errors){
										alert("Attenzione, salvataggio non riuscito.\n"+result.errors)
									} else {
										alert("Dati salvati con successo!");
										location.reload();
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
			loading = true;
			span.attr('class', newClass);
			$.ajax({
				url: href, 
				success: function(result){ 
					loading = false;
					span.attr('class', oldClass);
					if(result.errors){
						alert("Attenzione, eliminazione non riuscita.\n"+result.errors)
					} else {
						alert("Dati eliminati con successo!");
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
	});
});