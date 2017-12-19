$(document).ready(function(){
	$("a.mymodal.search").click(function (e){
		e.preventDefault();
		MyModal.setTitle("Aggiungi Filtro");
		MyModal.confirmModal(this, false, function(){
			if (typeof beforeFillFilterBox == 'function') { 
				beforeFillFilterBox();
				var $id=$("#filterResult input").attr("id");
				var $exist=false;
				$("#boxFilters input").each(function() {
					if($(this).attr("id")==$id){
						alert("Filtro Esistente!");
						$exist=true;
					}
				});
				if($exist){
					$("#filterResult input").remove();
					$exist=false;
					return;
				}
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
			if($(this).attr('class')!='typekey'){
				var buttonClass = "btn-"+$(this).attr('class');
				var label=$(this).attr('data-label');
				var li = $("<li id=btn-"+$(this).attr("id")+" class='btn "+buttonClass+"'>"+label+"&nbsp;</li>");
				var i = $("<i class='fa fa-times' data-rel='"+$(this).attr("id")+"'> </i>").click(function(e){
					e.preventDefault();
					var idToRemove = document.getElementById( $(this).attr("data-rel") );
					idToRemove.parentNode.removeChild( idToRemove );
//					var idTransformRemove = document.getElementById( $(this).attr("data-rel").replace(/filter-/,"transform-") );
//					if(idTransformRemove!=null)
//						idTransformRemove.parentNode.removeChild( idTransformRemove );
					$(li).remove();
				});
				i.appendTo(li);
				li.appendTo("#filterList");
			} 

		});

	}
});