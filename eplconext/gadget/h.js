
var cozmanovaHelper = cozmanovaHelper || {};

cozmanovaHelper.isIE = /MSIE (\d+\.\d+);/.test(navigator.userAgent);

/**
 * Append a key=value to querystring of given url
 * @param url url to append argument to
 * @param key name of the parameter
 * @param value value of the parameter (will be encoded inside function)
 * @returns the resulting url string
 */
cozmanovaHelper.addToUrl = function(url, key, value) {
	var s='?'; if (url.indexOf("?") > -1) {
		s = '&';
	}

	return [ url, s, key, '=', encodeURIComponent(value) ].join("");
};


cozmanovaHelper.makeHttpObject = function() {
  try {return new XMLHttpRequest();}
  catch (error) {}
  try {return new ActiveXObject("Msxml2.XMLHTTP");}
  catch (error) {}
  try {return new ActiveXObject("Microsoft.XMLHTTP");}
  catch (error) {}

  throw new Error("Could not create HTTP request object.");
};



cozmanovaHelper.createElementWithAttributes = function(eltype, attrs) {
  var el = document.createElement(eltype);
  if (attrs) {
    for (i in attrs) {
      if (i=='class' && cozmanovaHelper.isIE) {
   		  el.className=attrs[i]
      } else {
    	  el.setAttribute(i, attrs[i]); 
      }
    }
  }
      
  return el;
};




cozmanovaHelper.dump = function (arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	
	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	
	if(typeof(arr) == 'object') { //Array/Hashes/Objects 
		for(var item in arr) {
			var value = arr[item];
			
			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
};



/** counter and trigger features for sync'ing code **/
cozmanovaHelper.globcounters = {};

cozmanovaHelper.regcounter = function(name, count, handler) {
    cozmanovaHelper.globcounters[name]={'count': count, 'handler': handler};
}
    
cozmanovaHelper.countdown = function(name) {
    var o=cozmanovaHelper.globcounters[name];
    if (o) {
        var i=o['count']; i--; o['count'] = i;
        var h=o['handler'];
        if (i == 0 && h) {
            h();
        }
    }
}
