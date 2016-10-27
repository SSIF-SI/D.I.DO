$(document).ready(function(){

	if(location.hash) {
		$('.nav-tabs a[href="' + location.hash + '"]').tab('show');
	}

	$(document.body).on("click", "a[data-toggle]", function(event) {
		location.hash = this.getAttribute("href");
	});
	
	$("a.mymodal.edit").click(function (e){
		var href=$(this).prop("href");
		e.preventDefault();
		MyModal.setTitle("Modifica Firmatario");

		MyModal.addButtons(
			[
				{id:"salva", type: "submit", label: "Salva", cssClass: "btn-primary", spanClass: "fa-save", callback: function(){;
					MyModal.submit($(this),href,$('form').serializeArray());
					}
				}
			]
		);
		MyModal.load($(this));
	});
	
	$("a.mymodal.delete").click(function (e){
		var href=$(this).prop("href");
		e.preventDefault();
		MyModal.setTitle("Elimina Firmatario");

		MyModal.addButtons(
			[
				{id:"Elimina", type: "submit", label: "Elimina", cssClass: "btn-danger", spanClass: "fa-trash-o", callback: function(){;
					MyModal.submit($(this),href,null);
					}
				}
			]
		);
		MyModal.setContent("<label for=\"conferma\">Confermi:</label><p id=\"conferma\">Sei sicuro di voler eliminare l'elemento?</p>");
		MyModal.modal();
	});
});