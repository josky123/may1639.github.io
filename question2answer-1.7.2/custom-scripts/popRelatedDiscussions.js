
function populateRelatedDiscussions(data){

	// The maximum number of posts to read through.
	var maxPostCount = 200;
	
	// The maximum and minimum view counts for related posts.
	var maxViews = 0;
	var minViews = 0;
	
	// The maximum and minimum scores for related posts.
	var maxScore = 0;
	var minScore = 0;
	
	// The maximum and minimum number of related tags possessed by related posts.
	var maxTagCount = 0;
	var minTagCount = 0;
	
	// The number of related tags possessed by each post.
	var tagCount = [];
	
	var relatedTags = ["java", "parameter", "jml", "specification", "spec", "preconditions", "post-conditions", "design-by-contract", "assert", "assertions", "assertion", "apache-commons"];
	
	$.each( data.items, function( i, item ){
		
		// End the function (and therefore the loop) if we have max posts
		if( i == maxPostCount ){
			return false;
		}
		
		// Get maximum and minimum Views.
		if( i == 0 ){
			maxViews = item.view_count;
			minViews = item.view_count;
		}
		else if( item.view_count > maxViews ){
			maxViews = item.view_count;
		}
		else if( item.view_count < minViews ){
			minViews = item.view_count;
		}
		
		// Get the maximum and minimum Scores
		if( i == 0 ){
			maxScore = item.score;
			minScore = item.score;
		}
		else if( item.score > maxScore ){
			maxScore = item.score;
		}
		else if( item.score < minScore ){
			minScore = item.score;
		}
		
		// Get the tag count
		var numTags = 0;
		$.each( item.tags, function( j, tag ){
			
			for( k = 0; k < relatedTags.length; k++ ){
				if( tag == relatedTags[k] ){
					numTags++;
				}
				else if( relatedTags[k] == "apache-commons" ){
					if( tag.indexOf(relatedTags[k]) > -1 ){
						numTags++;
					}
				}
			}
		});
		
		tagCount[tagCount.length] = numTags;
		
		if( i == 0 ){
			maxTagCount = numTags;
			minTagCount = numTags;
		}
		else if( numTags > maxTagCount ){
			maxTagCount = numTags;
		}
		else if( numTags < minTagCount ){
			minTagCount = numTags;
		}
		
	});
	
	var rankedWeight = [];
	
	$.each( data.items, function( i, item ){
		
		// End the function (and therefore the loop) if we have max posts
		if( i == maxPostCount ){
			return false;
		}
		
		var rScore = ( item.score - minScore ) / ( maxScore - minScore );
		var rViews = ( item.view_count - minViews ) / ( maxViews - minViews );
		var rTagCount = ( tagCount[i] - minTagCount ) / ( maxTagCount - minTagCount );
		rankedWeight[i] = rScore + rViews + rTagCount;
	});
	
	//console.log(rankedWeight);
	//return rankedWeight;
	return sortedIndices( rankedWeight );
}

function sortedIndices( arr ){
	
	var indices = [];
	
	for( i = 0; i < arr.length; i++ ){
		indices[i] = i;
	}
	
	for( i = 0; i < arr.length; i++ ){
		for( j = i+1; j < arr.length; j++ ){
			
			if( arr[j] > arr[i] ){
				
				var tmp = arr[i];
				arr[i] = arr[j];
				arr[j] = tmp;
				
				tmp = indices[i]
				indices[i] = indices[j];
				indices[j] = tmp;
			}
		}
	}

	return indices;
}

function genRelatedDiscussions(){
    var nav = document.getElementById("navigation");
	//console.log(nav.childNodes);
    var navlen = nav.childNodes.length;
	//console.log(navlen);
	//console.log(nav.childNodes[navlen-1].name);
	
	var forumName;
	var parentName;
	
	if( navlen < 4 ){
		return;
	}
	
	forumName = nav.childNodes[navlen-1].name;
	parentName = nav.childNodes[navlen-2].name;
	
	if( navlen == 4 ){
		parentName = nav.childNodes[navlen-1].name;
	}
	
	// Get rid of any parenthesis on the forum name (i.e. add( object a ) )
	var j = 0;
	while( j < forumName.length ){
		
		if( forumName.charAt(j) == '(' ){
			break;	
		}
		j++;
	}
	forumName = forumName.substring(0, j);
	
	// Get rid of any parenthesis or generic notation on class names (i.e. ArrayList<E>)
	j = 0;
	while( j < parentName.length ){
		
		if( parentName.charAt(j) == '(' || parentName.charAt(j) == '<' ){
			break;	
		}
		j++;
	}
	parentName = parentName.substring(0, j);
	
	// Create the StackExchange API call string
	var callString1 = 'http://api.stackexchange.com/2.2/search/advanced?pagesize=100&order=asc&sort=relevance&accepted=True&tagged=';
	var callStringDelim = ';';
	var siteString = '&site=stackoverflow';
	
	var callString = callString1.concat(forumName).concat(callStringDelim).concat(parentName).concat(siteString);

	// Call the StackExchange API
	var stackStats = jQuery.getJSON( callString ).done(function( data ){	
		
			//console.log("This is not working...");
		
			// Rank the data
			var relevantIndices = populateRelatedDiscussions(data);
		
			// Populate the discussion box
			var list = document.getElementById("rel_dis");
			
			list.innerHTML = "";

			var printedRes = 0;
		 
			//console.log(data);
		 
			for( i = 0; i < relevantIndices.length; i++ ){
				
				index = relevantIndices[i];
				var li = document.createElement("li");
				var a = document.createElement("a");
				
				var decoded = $('<textarea/>').html( data.items[index].title).text();
				var word = document.createTextNode(decoded);
				a.appendChild(word);
				a.href = data.items[index].link;
				a.target = "_blank";
				li.appendChild(a);
				li.style.padding = "5px";
				list.appendChild(li);
				
				printedRes++;
				
				if(printedRes == 10 ){
					break;
				}
		
			}
	}); 
}