$(document).ready(function(){
	$('a.import').click(function (e) {
		e.preventDefault();
		var formId = $(this).attr('href').replace("#","");
		var data = $('form#'+formId).serializeArray();
		MyModal.setTitle("Importazione ");
		MyModal.setContent("<h4>Attendere, importazione in corso... <i class=\"fa fa-refresh fa-spin fa-1x fa-fw\"></i></h4>");
		MyModal.modal();
		MyModal.submit($(this), null, data, '.modal-body');
	});
	
	$('a.edit-info').click(function (e) {
		e.preventDefault();
		MyModal.setTitle($(this).html());
		MyModal.editModal(this);
	});
	
	$('.select-one').click(function(e){
		var checked = false;
		var panel = $(this).attr('rel');
		$('.'+panel+' .select-one').each(function(){
			if($(this).prop('checked')){
				checked = true;
				return false;
			}
		});
		$('.'+panel+' .select-all').prop('checked',false);
		toggleActionButtons(panel, checked);
	});
	
	$('.select-all').click(function(e){
		var checked = $(this).prop('checked');
		var panel = $(this).attr('rel');
		var els = $('.'+panel+' .select-one');
		els.each(function(){
			$(this).prop('checked', checked);
		});
		toggleActionButtons(panel, checked);
	});
	
	function toggleActionButtons(panel, checked){
		$('.'+panel+' .action').each(function(){ 
			if(checked) $(this).removeClass('disabled');
			else $(this).addClass('disabled');
		});
	}
	
	$('.action .link-selected').click(function(e){
		e.preventDefault();
		
	});

	$('.action .import-selected').click(function(e){
		e.preventDefault();
		
	});
});