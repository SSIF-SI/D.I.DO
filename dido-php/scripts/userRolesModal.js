$(document).ready(function(){
	$("a.mymodal.edit").click(function (e){
		e.preventDefault();
		MyModal.setTitle("Modifica Ruolo Utente");
		MyModal.editModal(this);
	});
	
	$("a.mymodal.delete").click(function (e){
		e.preventDefault();
		MyModal.setTitle("Elimina Ruolo Utente");
		MyModal.deleteModal(this);
	});
	
});