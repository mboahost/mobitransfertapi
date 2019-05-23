<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
require_once('API.class.php');
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
//Votre requêtte doit contenir les champs prix,qte et phone
$nouvelle_comm = new MobitransfertAPI($service_token, $prix,$qte,$phone);
$command_checker = new MobitransfertCommons($service_token);
$json = json_decode($nouvelle_comm->send_command());
$json2 = null;
if($json->SuccessMessage){//success command saved
 $json2 = json_encode($command_checker->check_command($json->SuccessMessage->commandID)); 	
}else{
 $json2 = json_encode(["type"=>"error","errortype"=>$json->ErrorMessage]);
}
exit($json2);