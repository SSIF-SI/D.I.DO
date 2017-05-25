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
	
});