<?php
header('Content-Type: application/json');
//echo("goo");
//header("Access-Control-Allow-Origin: *");<<= à décommenter si vous appellez l'api depuis une application mobile
//ce fichier permet d'afficher le retour du traitement USSD de retrait
require_once dirname(__FILE__) .'/common.php';
if ($_SERVER['REQUEST_METHOD'] != 'POST') {//appel en POST
  return json_encode([
                "type" => "error",
                "errortype" => "Requette incorrecte"
            ]);
}

extract($_POST,EXTR_OVERWRITE);
$service_token = "";
//Votre service token Mobitransfert vous l'obtenez en vous inscrivant ici https://mobitransfert.com/api/register , il n'est pas public et est obligatoire
//Votre requêtte doit contenir le champs commandID
$command_checker = new MobitransfertCommons($service_token);
$json = $command_checker->check_command($commandID); 
exit($json);
