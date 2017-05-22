$(document).ready(function(){
	$(document.body).on("click", "a[data-toggle]", function(event) {
		
		var tabs = this.getAttribute("href").split("/");
		
		var targethref = tabs[(tabs.length-1)];
		
		location.href = this.getAttribute("href");
		
		$(targethref+' .nav-tabs a').each(function(el){
			if($(this).parent('li').hasClass('active'))
				location.href = this.getAttribute("href");
		});
		
	});
	if(location.hash) {
		var tabs = location.hash.split("/");
		var tabString = "";
		for (var i in tabs){
			
			tabString += i > 0 ? "/"+tabs[i] : tabs[i];
			$('.nav-tabs a[href="' + tabString + '"]').tab('show');
		}
	} else {
		$('.nav-tabs a').first().click();
	}
	
});