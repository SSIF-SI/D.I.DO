$(document).ready(function(){
	$("a.mymodal.search").click(function (e){
		e.preventDefault();
		MyModal.setTitle("Aggiungi Filtro");
		MyModal.confirmModal(this, false, function(){
			$(".filter-box").append($("#filterResult").html());
			fillFilterBox("#filterResult input");
			MyModal.close();
		});
	});
	
	fillFilterBox();
	
	function fillFilterBox(html){
		var el = html == undefined ? ".filter-box input" : html;
		$(el).each(function(){
			var buttonClass = $(this).attr("name").indexOf("nome") != -1 ? "btn-success" : "btn-warning";
			var li = $("<li class='btn "+buttonClass+"'>"+$(this).val()+"&nbsp;</li>");
			var i = $("<i class='fa fa-times' data-rel='"+$(this).attr("id")+"'> </i>").click(function(e){
				e.preventDefault();
				var idToRemove = $(this).attr("data-rel");
				$("#"+idToRemove).remove();
				$(li).remove();
			});
			i.appendTo(li);
			li.appendTo("#filterList");
		})
	}
});