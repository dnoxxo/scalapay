<?php
class Scalapay {
	
  protected  $token;
	const APIKEY_SANDBOX = 'qhtfs87hjnc12kkos';
	const DIVISA = 'EUR';
	const SCALATYPE = 'pay-in-3';
	
    const ORDER = "https://api.scalapay.com/v2/orders";
	const SANDBOX_ORDER =  "https://integration.api.scalapay.com/v2/orders";
	
	const SANDBOX_CAPTURE = 'https://integration.api.scalapay.com/v2/payments/capture';
	const CAPTURE = 'https://api.scalapay.com/v2/payments/capture';
	
	const SANDBOX_ORDERSTATUS = 'https://integration.api.scalapay.com/v2/payments/';
	const ORDERSTATUS = 'https://api.scalapay.com/v2/payments/';
	
	
	public $sandbox;
	private  $items;
	private  $shipping;
	private  $billing;
	private  $total;
	private  $consumer;
	private  $redirects;
	private  $shipping_price;
	public   $order_id;
	private  $type;
	private  $product;
	private  $frequency;
	private  $orderExpiryMilliseconds;
	private  $merchantReference;
	private  $current_url;
	public  $fail;
	public  $success;

	public function Capture($ORDER_TOKEN){
		$POST = json_encode(array('token' => $ORDER_TOKEN));
		$ENDPOINT =  $this->sandbox ? $this::SANDBOX_CAPTURE : $this::CAPTURE;
		return $this->ApiCAll($ENDPOINT, $POST);	
	}


	public function CheckStatus($ORDER_TOKEN){
		$ENDPOINT =  $this->sandbox ? $this::SANDBOX_ORDERSTATUS : $this::ORDERSTATUS;	
		return $this->ApiCAll($ENDPOINT.$ORDER_TOKEN, $POST, false);	
	}


	public function ApiCAll($ENDPOINT, $POSTFIELDS, $POST = true){	
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $ENDPOINT);
		
		if($POST) curl_setopt($curl, CURLOPT_POST, $POST);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	if($POST) curl_setopt($curl, CURLOPT_POSTFIELDS, $POSTFIELDS);
	
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = 'Authorization: Bearer '.trim($this->token);
		if($POST)	$headers[] = 'Content-Type: application/json';
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$resp = curl_exec($curl);
		curl_close($curl);
		return json_decode($resp);
	}

	public function CreateOrder(){
		$ENDPOINT =  $this->sandbox ? $this::SANDBOX_ORDER : $this::ORDER;
		$POST = "{".($this->GetPOST())."}";
		return $this->ApiCAll($ENDPOINT, $POST);	
	}
	
	

	public function GetPOST(){
		$POSTDATA[] = substr(json_encode($this->total), 1, -1);
		$POSTDATA[] = substr(json_encode($this->consumer), 1, -1);
		if($this->billing) $POSTDATA[] = substr(json_encode($this->billing), 1, -1);
		$POSTDATA[] = substr(json_encode($this->shipping), 1, -1);
		$POSTDATA[] = substr(json_encode(array('items' => $this->items)), 1, -1);
		$POSTDATA[] = substr(json_encode($this->redirects), 1, -1);
		if($this->shipping_price) $POSTDATA[] = substr(json_encode($this->shipping_price), 1, -1);
		$POSTDATA[] = substr(json_encode($this->type), 1, -1);
		$POSTDATA[] = substr(json_encode($this->product), 1, -1);
		$POSTDATA[] = substr(json_encode($this->frequency), 1, -1);
		$POSTDATA[] = substr(json_encode($this->orderExpiryMilliseconds), 1, -1);
		$POSTDATA[] = substr(json_encode($this->merchantReference), 1, -1);
		return  implode(',', $POSTDATA);
	}

	public function __construct($token, $order_id, $sandbox = true) {
        $this->token = $sandbox ? $this::APIKEY_SANDBOX : $token;
		
		$this->order_id = $order_id;
		$this->sandbox = $sandbox;
    $this->type = array('type' => "online");
		$this->product = array('product' =>  $this::SCALATYPE);
		$this->frequency =  array('frequency' =>  array('number' => 1, 'frequencyType' => "monthly"));
		$this->orderExpiryMilliseconds = array('orderExpiryMilliseconds' => 600000);
		$this->merchantReference = array('merchantReference' => $order_id);
		
		$this->current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		
		
    }


	public function SetShippingVal($TOTALE){
		$this->shipping_price = array('shippingAmount' =>  array('currency' => $this::DIVISA, 'amount' => $TOTALE));
	}

   public function SetSuccess($SUCCESS){
		$this->success = $SUCCESS;
	}

 
	public function SetFail($CANCEL){
		$this->fail = $CANCEL;
		$this->redirects = array('merchant' =>  array('redirectCancelUrl' => ($CANCEL), 'redirectConfirmUrl' => $this->current_url . ""));
	}

	public function SetConsumer($TEL, $NOME, $COGNOME, $EMAIL){
		$this->consumer = array('consumer' =>  array('phoneNumber' => $TEL, 'givenNames' => $NOME, 'surname' => $COGNOME, 'email' => $EMAIL));
	}  
	
	
	public function SetTotal($TOTALE){
		$this->total = array('totalAmount' =>  array('currency' => $this::DIVISA, 'amount' => $TOTALE));
	}

	public function SetBilling($TEL, $COUNTRY, $NOMINATIVO, $CAP, $PROVINCIA, $NOTE = ''){
		$this->billing = array('billing' => array('phoneNumber' => $TEL, 'countryCode' => $COUNTRY, 'name' => $NOMINATIVO, 'postcode' =>  $CAP, 'suburb' =>  $PROVINCIA, 'line1' => $NOTE ));	
	}
	
	public function SetShipping($TEL, $COUNTRY, $NOMINATIVO, $CAP, $PROVINCIA, $NOTE= ''){
		$this->shipping = array('shipping' => array('phoneNumber' => $TEL, 'countryCode' => $COUNTRY, 'name' => $NOMINATIVO, 'postcode' =>  $CAP, 'suburb' =>  $PROVINCIA, 'line1' => $NOTE ));	
	}
	
	
	public function AddItem($PRICE, $QTY, $NAME, $CATEGORY, $CODICE){
		$price =  array('currency' => $this::DIVISA, 'amount' => "$PRICE");
		$quantity = $QTY;
		$name =  $NAME;
		$category = $CATEGORY;
		$sku =  $CODICE;	
		$this->items[] = array('price' => $price, 'quantity' => $quantity, 'name' => $name, 'category' => $category, 'sku' => $sku);
	}
	
	
}

?>
