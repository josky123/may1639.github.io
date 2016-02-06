initialize();
function searchTest()
{
    var searchText = document.getElementById("searchBox").value;
    var pageBox = document.getElementById("pageBox");
    var pageHeading = document.getElementById("pageHeading");
    var searchTypeList = document.getElementById("searchType");
    var searchType = searchTypeList.options[searchTypeList.selectedIndex].text;
    var source = document.getElementById("source");
    source.style.display = 'none'
    pageHeading.innerHTML = "Search Results";
    pageBox.innerHTML = "";


    var request = $.post("search.php", 
            {
                "name": String(searchText),
                "searchType": String(searchType)
            },
            function(data, status, xhr) {
                pagedata = data;
                //console.log(pagedata);
            },
            "json"
        );

        request.done(function() 
        {
            // add pages
            if (pagedata.length < 1) {
                pageBox.innerHTML = "No results found for \"" + searchText + "\" in " + searchType + ".";
            }
            for (var x = 0; x < pagedata.length; x++)
            {
                var a = document.createElement("a");
                a.href = "#";
                var text = "";
                if (searchType == "Method") {
                    text = pagedata[x].LName + " \\ " + pagedata[x].PName + " \\ " + pagedata[x].TName + " \\ " + pagedata[x].MName + "(" + pagedata[x].Args + ")";
                    a.setAttribute("onclick", "nav(\"" + pagedata[x].MName + "\", " + pagedata[x].MID + ", \"" + searchType + "\")" );
                } 
                else if (searchType == "Class") {
                    text = pagedata[x].LName + " \\ " + pagedata[x].PName + " \\ " + pagedata[x].TName;
                    a.setAttribute("onclick", "nav(\"" + pagedata[x].TName + "\", " + pagedata[x].TID + ", \"" + searchType + "\")" );
                }
                else if (searchType == "Package") {
                    text = pagedata[x].LName + " \\ " + pagedata[x].PName;
                    a.setAttribute("onclick", "nav(\"" + pagedata[x].PName + "\", " + pagedata[x].PID + ", \"" + searchType + "\")" );
                }
                else if (searchType == "Library") {
                    text = pagedata[x].LName;
                    a.setAttribute("onclick", "nav(\"" + pagedata[x].LName + "\", " + pagedata[x].LID + ", \"" + searchType + "\")" );
                }
                a.innerHTML = text;
                pageBox.appendChild(a);
                var br = document.createElement("br");
                pageBox.appendChild(br);
            }
        });
}

function nav(name, id, type)
{
    // Update navigation links. If name is in list, delete elements after name.
    // If not found, append name to list of links.
    var navigation = document.getElementById("navigation");
    var child;
    var numchild = navigation.childNodes.length;
    var found = false;
    var nodes = navigation.childNodes;
    var index;
    for (var x = 0; x < numchild; x++)
    {
        child = nodes[x];
        if (found) {
            navigation.removeChild(nodes[index]);
        }
        else if (child.name == name) {
            found = true;
            index = x + 1;
        }
    }
    if (!found) {
        var a = document.createElement("a");
        a.name = name;
        a.href = "#";
        a.setAttribute("onclick", "nav(\"" + name + "\", " + id + ", " + "\"" + type + "\")" );
        a.innerHTML = " \\ " + name;
        navigation.appendChild(a);
    }

    // Update pages
    var pages = document.getElementById("pages");
    var pageHeading = document.getElementById("pageHeading");
    var pagetype;
    var pageBox = document.getElementById("pageBox");
    pageBox.innerHTML = "";

    if (type == "Library") {
        pageHeading.style.display = 'block';
        pageHeading.innerHTML = "Packages";
        pagetype = "Package";
    }
    else if (type == "Package") {
        pageHeading.style.display = 'block';
        pageHeading.innerHTML = "Classes";
        pagetype = "Class";
    }
    else if (type == "Class") {
        pageHeading.style.display = 'block';
        pageHeading.innerHTML = "Methods";
        pagetype = "Method";
    }
    else {
        pageHeading.style.display = 'none';
        pageHeading.innerHTML = "";
        pagetype = "none";
    }

    var pagedata;
    if (type != "Method")
    {
        var request = $.post("navigation.php", 
            {
                "name": String(name),
                "id": parseInt(id),
                "type": String(type),
                "pagetype": String(pagetype)
            },
            function(data, status, xhr){
                pagedata = data;
                // if (type == "class") {
                //     console.log(pagedata);
                // }
            },
            "json"
        );

        request.done(function() 
        {
            // add pages
            for (var x = 0; x < pagedata.length; x++)
            {
                var a = document.createElement("a");
                a.href = "#";
                var text = pagedata[x].Name;;
                if (pagetype == "Method") {
                    var args = pagedata[x].Arguments;
                    args = args.replace(",", ", ");
                    text = pagedata[x].Name + "(" + args + ")";
                }
				a.setAttribute("onclick", "nav(\"" + text + "\", " + pagedata[x].ID + ", \"" + pagetype + "\")" );
                a.innerHTML = text;
                pageBox.appendChild(a);
                var br = document.createElement("br");
                pageBox.appendChild(br);
            }
        });
    }

    // Update source code div
    var source = document.getElementById("source");
    if (type == "Class" || type == "Method")
    {
        var request = $.post("source.php", 
            {
                "name": String(name),
                "id": parseInt(id),
                "type": String(type),
            },
            function(data, status, xhr){
                pagedata = data;
            },
            "json"
        );
        request.done(function() 
        {
            // add source
            var sourceBox = document.getElementById("sourceBox");
            sourceBox.innerHTML = "<pre>" + pagedata.source + "</pre>";
            
        });
        source.style.display = 'block';
    }
    else {
        source.style.display = 'none';
    }
	
	genRelatedDiscussions();
}

function initialize()
{
    // set navigation to home
    var navigation = document.getElementById("navigation");
    navigation.innerHTML = "<a onclick=\"initialize()\" href=\"#\">Home</a>";

    // Set pages label to Libraries
    var pages = document.getElementById("pages");
    var pageHeading = document.getElementById("pageHeading");
    pageHeading.innerHTML = "Libraries";
    pageHeading.style.display = 'block';
    var pageBox = document.getElementById("pageBox");
    pageBox.innerHTML = "";

    // get libraries from database
    var libs;
    var request = $.post("navigation.php", 
        {
            type: "home"
        },
        function(data, status, xhr){
            libs = data;
        },
        "json"
    );
    request.done(function() {
        // add libraries to pagebox
        for (var x = 0; x < libs.length; x++)
        {
            var a = document.createElement("a");
            a.setAttribute("onclick", "nav(\"" + libs[x].Name + "\", " + libs[x].ID + ", " + "\"Library\")" );
            a.href = "#";
            a.innerHTML = libs[x].Name;
            pageBox.appendChild(a);
            var br = document.createElement("br");
            pageBox.appendChild(br);
        }
    });

    // hide source code div
    var source = document.getElementById("source");
    source.style.display = 'none';
		
	var sidebar = document.getElementById("sidebar");
	//sidebar.style.visibility = "visible";
    sidebar.style.display = 'block';
	var list = document.getElementById("rel_dis");
	list.innerHTML = "";
	var msg1 = document.createTextNode("Related StackOverlow Posts Will Be Displayed Here.");
	
	var li1 = document.createElement("li");
	li1.appendChild(msg1);
	list.appendChild(li1);
	list.style.listStyleType = "none";
	list.style.padding = "0px";
}