<?php
// check precondition
global $userdata, $padlist, $mainurl;
$proceed = isset($userdata) &&
		isset($padlist) &&
		isset($mainurl);		// Web_CGIUtil::get_self_url()

if (! $proceed ) exit();
?>

  <ul>
  <?php
  foreach ($padlist as $pad) {
  	$padlink = Web_CGIUtil::appendArg($mainurl, 'activepad', $pad);
  	$padname = MyEtherpadLiteClient::splitGrouppadName($pad);
  	$a = "<a href=\"{$padlink}\">{$padname[1]}</a>&nbsp;" . 
  		"<a target=\"_blank\" href=\"{$padlink}\"><img src=\"$appcontext/images/link.png\"/></a>";   
  	?>
    <li><?php echo $a;?></li>
  <?php } // foreach ($padlist as $pad) ?>
  </ul>
