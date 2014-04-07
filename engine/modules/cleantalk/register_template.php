<?php
if (!defined('DATALIFEENGINE')) {
    die("Hacking attempt!");
}

require_once ENGINE_DIR . '/modules/cleantalk/ct_functions.php';
list($ct_config, $ct_config_serialized) = ct_get_config($db);

if ($ct_config['ct_enable_mod']) {
$tpl->copy_template .= '<br><a style="font-size: 12px; margin-left: 10px;" href="http://cleantalk.ru/dle" target="__blank">
	<b style="color: #009900;">Clean</b><b style="color: #777;">talk</b>
	</a><br><br>';
if (isset($ct_fill_field)) {
$tpl->copy_template .= '<script language="javascript" type="text/javascript">
$("#name").val("'.$name.'"); $("input[name$=\'email\']").val("'.$email.'");
</script>';
 }
// End: cleantalk.ru
 
// Begin: cleantalk.ru
$tpl->copy_template .= "
<script type=\"text/javascript\">
// <![CDATA[
document.getElementById(\"ct_checkjs\").value = 1;
// ]]>
</script>";
$_SESSION['ct_submit_register_time'] = time();
}
?>