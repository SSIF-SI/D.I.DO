$(document).ready(function(){
	
	$('a.edit-info').click(function (e) {
		e.preventDefault();
		MyModal.setTitle($(this).html());
		MyModal.editModal(this);
	});
	
	$('a.closeMd,a.closeIncompleteMd').click(function (e) {
		e.preventDefault();
		var more = "";
		
		if($(this).hasClass("closeIncompleteMd")){
			more = " con stato INCOMPLETO";
		}
		if(confirm("Sei sicuro di voler chiudere definitivamente il procedimento"+more+"?")){
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
		}
	});
	
	$('a.add-doc, a.edit-doc, a.upload-doc').click(function (e) {
		e.preventDefault();
		var href = $(this).attr("href");
		var params = paesrQueryString(href);
		MyModal.setTitle((params['id_doc'] != undefined ? "Aggiorna" : "Nuovo")+ " documento - "+jsUcfirst(params['name']));
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
	
	$('a.private-doc').click(function (e) {
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

	function paesrQueryString(querystring) {
	  // remove any preceding url and split
	  querystring = querystring.substring(querystring.indexOf('?')+1).split('&');
	  var params = {}, pair, d = decodeURIComponent;
	  // march and parse
	  for (var i = querystring.length - 1; i >= 0; i--) {
	    pair = querystring[i].split('=');
	    params[d(pair[0])] = d(pair[1] || '');
	  }

	  return params;
	};
	
	function jsUcfirst(string) 
	{
	    return string.charAt(0).toUpperCase() + string.slice(1);
	}
});