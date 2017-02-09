$(document).ready(function(){
	if(location.hash) {
		$('.nav-tabs a[href="' + location.hash + '"]').tab('show');
	}
	$(document.body).on("click", "a[data-toggle]", function(event) {
		location.hash = this.getAttribute("href");
	});
	
	$("a.mymodal.edit").click(function (e){
		e.preventDefault();
		MyModal.setTitle("Modifica Firmatario");
		MyModal.editModal(this);
	});
	
	$("a.mymodal.delete").click(function (e){
		e.preventDefault();
		MyModal.setTitle("Elimina Firmatario");
		MyModal.deleteModal(this);
	});
	
	$("a.mymodal.sign").click(function (e){
		e.preventDefault();
		MyModal.setTitle("Firma Documento");
		MyModal.signModal(this);
	});
	
});