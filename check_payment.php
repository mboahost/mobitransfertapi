<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
require_once dirname(__FILE__) .'/common.php';
extract($_POST,EXTR_OVERWRITE);
//Votre service token Mobitransfert vous l'obtenez en vous inscrivant ici https://mobitransfert.com/api/register , il n'est pas public et est obligatoire
$service_token = "";
$command_checker = new MobitransfertCommons($service_token);
$json = json_encode($command_checker->check_payment($commandID));//use $_POST['commandID']
exit($json);