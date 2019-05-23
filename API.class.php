<?php
class MobitransfertAPI
{
    public $_vendeur;//Votre service token Mobitransfert vous l'obtenez en vous inscrivant ici https://mobitransfert.com/register
    public $_prix;//prix du produit
    public $_qte;//Quantité achetée
    public $_codeMobilePaye;//Numéro mobile du client
    public $bad_vendeurMessage = "Numéro de token invalide";
    public $bad_prixMessage = "Le prix ne doit pas contenir de virgule et doit être entier et superieur a 0";
    public $bad_qteMessage = "La quantité doit être entier et superieur a 0";
    public $bad_numeroMessage = "Numéro de téléphone incorrect";
    public $bad_codeMobilePayeMessage = "Le code Mobile Paye ne doit pas contenir de virgule";
    
    public function __construct($_vendeur, $_prix, $_qte, $_codeMobilePaye)
    {
        $this->_vendeur        = $_vendeur;
        $this->_qte            = $_qte;
        $this->_prix           = $_prix;
        $this->_codeMobilePaye = $_codeMobilePaye;
    }
    
    public function is_Good_prix()
    {
        return (ctype_digit($this->_prix) && $this->_prix > 0);
    }
    
    public function is_Good_qte()
    {
        return ctype_digit($this->_qte) && $this->_qte > 0;
    }
    
    public function is_Good_produit()
    {
        return !strpos($this->_produit, ",", 0);
    }
    
    public function is_Good_ShopName()
    {
        return strlen($this->_vendeur) == 32;//code sha1 valide
    }
    
    public function is_Good_codeMobilePaye()
    {
        return $this->is_Good_PhoneNumber();//numéro valide
    }
    
    public function is_Good_PhoneNumber()
    {
        $argument2 = $this->is_aCamerPhone($this->_codeMobilePaye);
        return $argument2;
    }
    /*
	check un numéro Mtn ou Orange ou Nexttel
	paramètres:
	$number: Numéro du client
	*/
    public function is_aCamerPhone($number)
    {
        $splited = str_split($number);
        if (count($splited) !== 9 || $splited[0] === "2" || $splited[0] !== "6") {
            return false;
        }
        switch (true) {
            case ($splited[1] == "7" || $splited[1] == "8" || ($splited[1] == "5" && intval($splited[2], 10) < 5)):
                $retval = "mtn";
                break;
            case $splited[1] === "6":
                $retval = "nexttel";
                break;
            case ($splited[1] == "9" || ($splited[1] == "5" && intval($splited[2], 10) >=5)):
                $retval = "orange";
                break;
        }
        return $retval;
    }
    /*
	envoit de la commande
	retourne le json suivant
	{"ErrorMessage":"Error message from server or empty or false","SuccessMessage":"false if ErrorMessage" or =>{"type":"payment","commandID":"the command ID","userID":null,"paid":"no","number":"client number sent in the request","amount":"total amount of the transaction","created":"date created timestamp","executedAt":"null or empty","message":"null or empty","network":"network we are requesting on","ErrorCode":"null or empty"}}
	*/
    
    public function send_command()
    {
        $error = "";
        if (!$this->is_Good_qte())
            $error .= $this->bad_qteMessage . "<br>";
        if (!$this->is_Good_PhoneNumber())
            $error .= $this->bad_numeroMessage . "<br>";
        if (!$this->is_Good_ShopName())
            $error .= $this->bad_vendeurMessage . "<br>";
        if (!$this->is_Good_prix())
            $error .= $this->$bad_prixMessage . "<br>";
        if ($error != "")
            return json_encode(array(
                "ErrorMessage" => $error,
                "SuccessMessage" => "false"
            ));
        $url    = 'https://api.mobitransfert.com/?action=makepayment';
        $fields = array(
            'ServiceToken' => urlencode($this->_vendeur),
            'number' => $this->_codeMobilePaye,
            'network' => $this->is_Good_PhoneNumber(),
            'amount' => $this->_prix * $this->_qte
        );
        
        //url-ify the data for the POST
        $fields_string = "";
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        
        //open connection
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        
        //execute post
        $result = json_decode(curl_exec($ch));
        if ($result->type && $result->type == "error") {
            $result2 = json_encode(array(
                "ErrorMessage" => $result->errortype,
                "SuccessMessage" => "false"
            ));
        } else if(!$result->type) {
            $result2 = json_encode(array(
                "ErrorMessage" => "Erreur du serveur veuillez éssayer à nouveau",
                "SuccessMessage" => "false"
            ));
        } else {
            $result2 = json_encode(array(
                "ErrorMessage" => "false",
                "SuccessMessage" => $result->data
            ));
        }
        $status = curl_getinfo($ch);
        //close connection
        curl_close($ch);
        return $result2;
        
    }
    
}
