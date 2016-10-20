$(document).ready(function(){
	var loading = false;
	
	$("a.mymodal").click(function (e){
		e.preventDefault();
		var href = $(this).attr('href');
		
		var span = $(this).children("span");
		var oldClass = span.attr('class');
		var newClass = "fa fa-refresh fa-spin fa-1x fa-fw";
		
		if(!loading){
			
			loading = true;
			span.attr('class', newClass);
			
			// fa fa-cog fa-spin fa-3x fa-fw
			$.get( href, function( result ) {
				span.attr('class', oldClass);
				
				// Recupero il contenuto della finestra modale tramite GET
				// e lo metto nel div.modal-content, quindi mostro la finestra
				$("#myModal .modal-content").html(result);
				$("#myModal").modal();
				
				loading = false;
				
				// Intercetto la sottomissione della finestra modale e continuo a fare tutto
				// via AJAX passando in POST i parametri
				$("form").submit(function(e){
					e.preventDefault();
					//console.log("QUI");
					$.ajax({
						type: "POST", 
						data: { id_persona: $('#id_persona').val(), pkey: $('#pkey').val() }, 
						success: function(result){ 
							alert(result);
						}
					});
				});
			});
		}
	});
});