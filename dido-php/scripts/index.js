$(document).ready(function(){
	$('a.import').click(function (e) {
		e.preventDefault();
		var formId = $(this).attr('href').replace("#","");
		var data = $('form#'+formId).serializeArray();
		MyModal.setTitle("Importazinoe "+$('form#'+formId).attr('class'));
		MyModal.setContent("<h4>Attendere, importazione in corso... <i class=\"fa fa-refresh fa-spin fa-1x fa-fw\"></i></h4>");
		MyModal.modal();
		MyModal.submit($(this), null, data, '.modal-body');
	});
});