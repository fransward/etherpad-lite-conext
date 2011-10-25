<?php
// check precondition
global $userdata, $padurl;
$proceed = isset($userdata) &&
		isset($padurl);		// Web_CGIUtil::get_self_url()

if (! $proceed ) exit();
?>
<?php
print "<div style=\"align:center\"><h1>{$padname}</h1></div>";
print "<iframe src=\"{$padurl}\" width=\"100%\" height=\"100%\"></iframe>";
?>
