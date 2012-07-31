<?php
////	INIT
define("GLOBAL_EXPRESS",1);
require_once "../includes/global.inc.php";
if(@$_GET["captcha"]==$_SESSION["captcha"])		echo "true";
else											echo "false";
?>