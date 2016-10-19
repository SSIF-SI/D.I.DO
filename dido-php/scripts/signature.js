$("a.mymodal").click(function (e){
	e.preventDefault();
	var href = $(this).attr('href');

	$.get( href, function( result ) {
		$("#myModal .modal-content").html(result)
		$("#myModal").modal();
		// Recupero il contenuto della finestra modale tramite GET
		// e lo metto nel div.modal-content, quindi mostro la finestra
	});

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
//you can do anything with data, or pass more data to this function. i set this data to modal header for example

