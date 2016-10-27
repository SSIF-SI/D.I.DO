$(document).ready(function(){
	$('.mymodal-edit').click(function(e){
		e.preventDefault();
		var anchor = $(this);
		MyModal.setTitle("MyModal Test");
		MyModal.addButtons(
			[
				{id:"test-1", label: "test"},
				{id:"test-2", label: "test2", cssClass: "btn-danger", spanClass: "fa-trash-o", callback: function(){alert($(this).prop('id'))}},
				{id:"test-3", type: "submit", label: "test3", cssClass: "btn-primary", spanClass: "fa-save", callback: function(){alert($(this).prop('id'));MyModal.submit($(this),{test: true})}}
			]
		);
		MyModal.load(anchor);
		
	});
})