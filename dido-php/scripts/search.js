$(document).ready(function(){
	$("a.mymodal.search").click(function (e){
		e.preventDefault();
		MyModal.setTitle("Aggiungi Filtro");
		MyModal.confirmModal(this, false, function(){
			if (typeof beforeFillFilterBox == 'function') { 
				beforeFillFilterBox();
				delete beforeFillFilterBox;
				beforeFillFilterBox = undefined;
			}
			$("#boxFilters").append($("#filterResult").html());
			fillFilterBox("#filterResult input");
			MyModal.close();
		});
	});
	
	fillFilterBox();
	
	function fillFilterBox(html){
		var el = html == undefined ? "#boxFilters input" : html;
		$(el).each(function(){
			var buttonClass = "btn-"+$(this).attr('class') != undefined ? "btn-"+$(this).attr('class') : $("#filterResult").attr("class");
			var buttonClass = "btn-"+$(this).attr('class');
			var label=typeof $(this).attr("label")!='undefined'?$(this).attr("label"):$(this).val();
			var li = $("<li class='btn "+buttonClass+"'>"+label+"&nbsp;</li>");
			var i = $("<i class='fa fa-times' data-rel='"+$(this).attr("id")+"'> </i>").click(function(e){
				e.preventDefault();
				var idToRemove = $(this).attr("data-rel");
				$("#"+idToRemove).remove();
				$(li).remove();
			});
			i.appendTo(li);
			li.appendTo("#filterList");
		});
	}
});