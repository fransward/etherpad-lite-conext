//
// Group Selector for gadgets
//


// flow:
// 1- Is Group selected?
// 2- Show all groups
//    - onClick: set SelectedGroup as user preference
//        update Prefs and refresh gadget
//

// open: how to refresh a gadget?

// namespace:
var groupSelector = groupSelector || {};

// var groupSelector.container = null;


groupSelector.onSelectGroup = function(groupid) {
  var prefs = new gadgets.Prefs();
  prefs.set('currentGroup', groupid);
  
  var v = new gadgets.views.View("home");
  gadgets.views.requestNavigateTo(v);
  return;
};


groupSelector.createGroupOption = function(groupid, grouptitle) {
  var f = function(){
	  // resolve groupid from li-element
	  var liid=this.id; 
	  var groupid=liid.substr(8);
	  groupSelector.onSelectGroup(groupid); 
  };
  
  var grpel = cozmanovaHelper.createElementWithAttributes('li', {
	'id' : 'grpelid_'+groupid,
	'style' : ';',
	});
  grpel.onclick = f;
  grpel.appendChild( document.createTextNode(grouptitle) );
  
  return grpel;
};


groupSelector.renderHeading = function(title) {
	var h = cozmanovaHelper.createElementWithAttributes('h1', {
		'style' : 'font-family : Arial; font-size : medium;'
	});
	h.appendChild( document.createTextNode(title) );
	return h;
};


// Retrieve the groups from either internal or external OpenSocial
// service provider
// if extreq is provided, it should contain the properties to build
// a request for an external OpenSocial-provider:
// extreq.url : url-template to call
// extreq.oauthService : name of the gadget's OAuth/Service-definition for the
//    external OpenSocial-provider
groupSelector.getUserGroups=function(container, extreq) {
  this.container=container;
  if (extreq) {
	var params={};
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.JSON;
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.OAUTH;
    params[gadgets.io.RequestParameters.OAUTH_SERVICE_NAME] = extreq.oauthService;
    params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.GET;
    
	url=extreq.url.replace(/{.*}/, user_id);
	
    gadgets.io.makeRequest(url, function(response) {
    	groupSelector.showGroups(response, container);
    }, params);
  } else {
    osapi.groups.get({userId:'@owner'}).execute(groupSelector.showGroups);
  }
}

// Returns an unnumbered list that shows all groups
// if extreq is set, makeRequest(extreq) is used to retrieve groups
//   instead of osapi.groups.geT(
// 
groupSelector.showGroups=function(response, container) {
	var t = cozmanovaHelper.createElementWithAttributes('div', {
		'class' : 'gsDiContainer'
	});
	t.appendChild( groupSelector.renderHeading('For which team do you want to start or edit an Etherpad document?'));

	var ulc=cozmanovaHelper.createElementWithAttributes('ul', {
	  'class' : 'gsUlContainer'
	});
	  
    result ='';
    for (var i=0; i < response.totalResults; i++) {
      var o = response.list[i];
      var groupid = o.id.groupId;
      var grouptitle = o.title;
      var elgroup = groupSelector.createGroupOption(groupid, grouptitle);
      ulc.appendChild(elgroup);
    }
    
    t.appendChild(ulc);
    
    if (this.container) {
    	this.container.appendChild(t);
    } else {
    	document.appendChild(t);
    }
    
    gadgets.window.adjustHeight();
};



groupSelector.clearGroup=function() {
	groupSelector.onSelectGroup('');
};
