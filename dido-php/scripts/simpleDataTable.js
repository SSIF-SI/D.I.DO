    $(document).ready(function() {
        $('#signature-table').DataTable({
            responsive: true,
            language: {
            	info:"Pagina _START_ di _END_",
            	lengthMenu: "Mostra _MENU_ righe",
            	search:"Cerca: ",
				paginate: {
					next: "Prossima",
					previous:"Precedente"
				 }
				}
        });
    });
