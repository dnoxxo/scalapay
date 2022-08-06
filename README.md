# scalapay
Semplice classe php per gestire i pagamenti con scalapay


## come si usa ...


esempio creare un file chiamato scalapay.php e chiamarlo semplicemente tramite url con parametro dell'ordine 
per prendere info esempio 

<a href="scalapay.php?order=1234"> PAGA CON SCALAPAY </a>

<CODE>
<?php

require_once 'scalapay_class.inc.php';

$ORDINE_ID = @$_GET['order'];
$SCALAPAY_APIKEY = "APIKEY";
$TESTMODE = TRUE;
$SUCCESS_URL = '';
$FAIL_URL = '';

$SPESE_SPEDIZIONE = 0;

//CLIENTE
$TELEFONO = "";
$NOME = "";
$COGNOME = "";
$MAIL = "";


//SPEDIZIONE
$CAP = "";
$CITY =  "";
$VIA =  "";


$Scalapay = new Scalapay($SCALAPAY_APIKEY, $ORDINE_ID, $TESTMODE);
$Scalapay->SetFail($FAIL_URL);
$Scalapay->SetSuccess($SUCCESS_URL);


if(@$_GET['orderToken'] == "") {
$Scalapay->SetTotal($TOTALE_ORDINE);
$Scalapay->SetConsumer($TELEFONO, $NOME, $COGNOME, $MAIL);
$Scalapay->SetShipping($TELEFONO, 'IT', $NOME." ".$COGNOME, $CAP, $CITY, $VIA);

$prodotti = array();

foreach($prodotti as $prodotto) {
	$Scalapay->AddItem(($prodotto['prezzo']), ($prodotto['pezzi']),  stripslashes($prodotto['titolo']), "WEBSITE", stripslashes($prodotto['codice']));
}

$Scalapay->SetShippingVal($SPESE_SPEDIZIONE);
$RESPONSE = $Scalapay->CreateOrder();

$ORDER_TOKEN = $RESPONSE->token;
$ORDER_CHECKOUT = $RESPONSE->checkoutUrl;

//Azione qui per impostare il token sull'ordine

header('Location: '.$ORDER_CHECKOUT);
	

} else {


	$ORDER_TK = @$_GET['orderToken'];
	
	$CAPTURE =  $Scalapay->Capture(@$ORDER_TK);
	
	
	if($CAPTURE->status == 'APPROVED'){
		$CHECK = $Scalapay->CheckStatus(@$ORDER_TK);
		
		if($CHECK->status == 'charged'){
			
			//azione qui per impostare lo stato d'ordine in base al token 
			header('Location: '.$Scalapay->success);
			
		} 
		
	}
	
	
	
	if($CAPTURE->httpStatusCode != 200){
		
		
		header('Location: '.$Scalapay->fail);
		
		
	}





}
?>
</CODE>
