<?php
header('Content-Type: application/json');
//echo("goo");
//header("Access-Control-Allow-Origin: *");<<= à décommenter si vous appellez l'api depuis une application mobile
//ce fichier permet d'afficher le retour du traitement USSD de retrait
require_once dirname(__FILE__) .'/common.php';
if ($_SERVER['REQUEST_METHOD'] != 'POST') {//appel en POST
  exit( json_encode([
                "ErrorMessage" => "Requette incorrecte",
                "SuccessMessage" => "false"
            ]));
}

extract($_POST,EXTR_OVERWRITE);
$service_token = "";
//Votre service token Mobitransfert vous l'obtenez en vous inscrivant ici https://mobitransfert.com/register , il n'est pas public et est obligatoire
//Votre requêtte doit contenir les champs prix,qte et phone
$command_checker = new MobitransfertCommons($service_token);
$json = json_encode($command_checker->check_command($commandID));//use $_POST['commandID']
exit($json);
