$("a.mymodal").click(function (e){
	e.preventDefault();
	var href = $(this).attr('href');
	$.get( href, function( result ) {
		   $("#myModal .modal-content").html(result)
		   $("#myModal").modal();
	});
   //you can do anything with data, or pass more data to this function. i set this data to modal header for example
});