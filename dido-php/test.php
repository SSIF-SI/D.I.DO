<html>
<head>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.js"></script>
</head>
<body>
<form method="POST">
<button class="filterRemove" data-rel="k1">Rimuovi K1</button>
<input type="hidden" name="type[]" value="1" />
<input type="hidden" name="type[]" value="2" />
<input type="hidden" name="type[]" value="3" />
<input id="k1" type="hidden" name="keyword[numero mandato]" value="123" />
<input type="submit" value="Vai" />
</form>
<pre><?php print_r($_POST)?></pre>
<script>
	$("button.filterRemove").click(function(e){
		e.preventDefault();
		var idToRemove = $(this).attr("data-rel");
		$(this).remove();
		$("#"+idToRemove).remove();
	});
</script>
</body>
</html>
