$(document).ready(function(){
	
	$('a.edit-info').click(function (e) {
		e.preventDefault();
		MyModal.setTitle($(this).html());
		MyModal.editModal(this);
	});
	
	$('a.add-doc, a.upload-doc').click(function (e) {
		e.preventDefault();
		MyModal.setTitle("Carica documento");
		MyModal.editModal(this);
	});
	$('a.close-doc').click(function (e) {
		e.preventDefault();
		var href = $(this).attr("href");
		$.ajax({
			url: href, 
			type: "GET", 
			dataType: "json",
			success: function( result ) {
				location.reload();
			},
		error: function(){
			alert("Errore imprevisto");
		}
	});
	});
	$('a.delete-doc').click(function (e) {
		e.preventDefault();
		if(confirm("Sei Sicuro ?")){
			var href = $(this).attr("href");
			$.ajax({
				url: href, 
				type: "GET", 
				dataType: "json",
				success: function( result ) {
					if(result)
						location.reload();
					else 
						alert("Errore, documento non cancellato.");
				},
				error: function(){
					alert("Errore imprevisto");
				}

			});
		}
	});

});