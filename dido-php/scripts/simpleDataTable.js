    $(document).ready(function() {
        $('.search-table').DataTable({
            "responsive" : true,
            "language" : {
            	"info" :"Pagina _START_ di _END_",
            	"lengthMenu" : "Mostra _MENU_ righe",
            	"search" :"Cerca: ",
				"paginate" : {
					"next" : "Prossima",
					"previous" :"Precedente"
				 }
				},
        	"aoColumnDefs" : [
                         { "bSearchable" : false, "bVisible" : false, "aTargets" : ['hidden-column'] },
                         { "bSearchable" : false, "aTargets" : ['nosearch-column'] }
                     ]
        });
    });