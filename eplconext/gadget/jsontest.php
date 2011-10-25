<?php 
header("Expires: Mon, 26 Jul 1990 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Test some JS/JSON stuff</title>
<script language="JavaScript">
  var jsondata = '[{"name":"Pad Name One","url":"https:\/\/etherpad.conext.surfnetlabs.nl\/p\/pad_one","created":1316169334,"owner":"urn:student:mdobrinic","group_id":null},{"name":"Pad Name Two","url":"https:\/\/etherpad.conext.surfnetlabs.nl\/p\/pad_two","created":1316169334,"owner":"urn:student:mdobrinic","group_id":null},{"name":"Pad Name Three","url":"https:\/\/etherpad.conext.surfnetlabs.nl\/p\/pad_three","created":1316169334,"owner":"urn:student:mdobrinic","group_id":null}]';

  function formatPad(pad) {
    var s = '<li>' + pad.name + '</li>';
    return s;
  }
  
  function perform() {
  	var myJSON = JSON.parse(jsondata);
//  	alert(myJSON);
	var s;
	if (! (myJSON instanceof Array)) {
		s = 'Invalid input from service.';
	} else {
		s = '<ul>';
		for(var i = 0; i < myJSON.length; i++) {
			s = s + formatPad(myJSON[i]);
		}
		s = s + '</ul>';
	}
		
  	var o = document.getElementById('placeholder');
  	o.innerHTML = s;
  }
</script>

</head>
<body>
<a href="#" onClick="perform(); return false;">Perform processing.</a>
<div id="placeholder"/>
</body>
</html>
