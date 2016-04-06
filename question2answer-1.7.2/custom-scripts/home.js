initialize();
var navLinks = null;
function keycheck(event)
{
    var key = event.keyCode;
    if (key == 13)
    {
        search();
    }
}
function search()
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
                return;
            }
            navLinks = new Array();
            for (var x = 0; x < pagedata.length; x++)
            {
                // array of links
                var links = new Array();

                // create link
                var l = document.createElement("a");
                // set link to top of page
                l.href = "#";
                // set displayed text of link
                l.innerHTML = " \\ " + pagedata[x].LName;
                // set onclick function
                l.setAttribute("onclick", "nav(\"" + pagedata[x].LName + "\", " + pagedata[x].LID + ", \"" + "Library" + "\", 1, " + x + ")" );
                // add new link to array
                links[links.length] = l;

                if (pagedata[x].hasOwnProperty("PName")) {
                    var p = document.createElement("a");
                    p.href = "#";
                    p.innerHTML = " \\ " + pagedata[x].PName;
                    p.setAttribute("onclick", "nav(\"" + pagedata[x].PName + "\", " + pagedata[x].PID + ", \"" + "Package" + "\", 2, " + x + ")");
                    links[links.length] = p;
                }
                if (pagedata[x].hasOwnProperty("TName")) {
                    var c = document.createElement("a");
                    c.href = "#";
                    c.innerHTML = " \\ " + pagedata[x].TName;
                    c.setAttribute("onclick", "nav(\"" + pagedata[x].TName + "\", " + pagedata[x].TID + ", \"" + "Class" + "\", 3, " + x + ")");
                    links[links.length] = c;
                }
                if (pagedata[x].hasOwnProperty("MName")) {
                    var m = document.createElement("a");
                    m.href = "#";
                    m.innerHTML = " \\ " + pagedata[x].MName + "(" + pagedata[x].Args + ")";
                    m.setAttribute("onclick", "nav(\"" + pagedata[x].MName + "\", " + pagedata[x].MID + ", \"" + "Method" + "\", 4, " + x +")");
                    // m.addEventListener("onclick", function(){
                    //     nav(pagedata[x].MName, pagedata[x].MID, "Method", links);
                    // });
                    links[links.length] = m;
                    console.log(m);
                }
                //console.log(links[0]);
                for (var j = 0; j < links.length; j++) {
                    pageBox.appendChild(links[j]);
                }
                var br = document.createElement("br");
                pageBox.appendChild(br);
                navLinks[x] = links;
            }
        });
}

function nav(name, id, type, numLinks, linksIndex)
{
    // Update navigation links. If name is in list, delete elements after name.
    // If not found, append name to list of links.
    var navigation = document.getElementById("navigation");
    var child;
    var numchild = navigation.childNodes.length;
    var found = false;
    var nodes = navigation.childNodes;
    var index;
    var links;
    if (navLinks == null)
    {
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
    }else
    {
        console.log(navLinks);
        //remove old links on navigation bar
        for (var x = 1; x < numchild; x++)
        {
            child = nodes[1];
            navigation.removeChild(child);
        }
        // add new links
        for (var x = 0; x < numLinks; x++)
        {
            navigation.appendChild(navLinks[linksIndex][x]);
            console.log(navLinks[linksIndex][x]);
        }
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
                console.log("a: " + a);
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
	navLinks = null;
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