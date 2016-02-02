
	var sb = document.getElementById("sidebar");
	var t = document.createTextNode("Related Discussions");
	sb.appendChild(t);
	
	var list = document.createElement("ul");
	for( var i = 0; i < 10; i++ ){
		var ti = document.createTextNode("Sample ".concat(i+1));
		var lii = document.createElement("li");
		lii.appendChild(ti);
		list.appendChild(lii);
	}
	
	sb.appendChild(list);