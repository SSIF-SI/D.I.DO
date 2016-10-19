$("a.mymodal").click(function (e){
	e.preventDefault();
	var href = $(this).attr('href');
	
	$.get( href, function( result ) {
		// Recupero il contenuto della finestra modale tramite GET
		// e lo metto nel div.modal-content, quindi mostro la finestra
		$("#myModal .modal-content").html(result);
		$("#myModal").modal();
		
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
});