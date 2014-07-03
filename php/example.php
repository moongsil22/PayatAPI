<?
  require_once("payatapi.php");
  
  
  
  $payat = new PayAtAPI("aegisep","7adb3bdb22db89f220549925e27e53853c5f20dc8976a06d4513016718b9da96"); //client_id,client_secret
  
  try {
	  
	  //JSON형식으로 상품 이미지와 함께 등록 및 업로드!
	  $params = array(
	  	"store_screen_name"=>"111111",
	  	"employee_screen_name"=>"111111",
	  	"item_name"=>"테스트",
	  	"item_code"=>"A000",
		"item_price"=>"3000",
		"item_image" => "@test.jpg"	  	
	  );
	  $json = $payat->api(
	  	"/openapi/v1/partner/item/add.json",
	  	$params
	  );
	  print_r($json);
	  
	  //XML형식으로 상품 목록 받아오기

	  $params = array(
	  	"store_screen_name"=>"111111"
	  );
	  $xml = $payat->api(
	  	"/openapi/v1/partner/item/list.xml",
	  	$params
	  );
	  print_r($xml);
		
	  //실서버연결시 생성자 마지막은 false로..	  
	  $payat = new PayAtAPI("","",false); //client_id,client_secret,is_dev

  }catch(Exception $e) {
  	print_r($e);
  }

  
?>