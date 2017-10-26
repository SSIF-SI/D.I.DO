$(document).ready(function(){
	$("a.mymodal.search").click(function (e){
		e.preventDefault();
		MyModal.setTitle("Aggiungi Filtro");
		MyModal.confirmModal(this);
	});
	
});