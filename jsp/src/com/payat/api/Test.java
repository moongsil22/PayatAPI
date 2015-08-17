package com.payat.api;

import java.io.File;
import java.util.HashMap;
import java.util.Map;

import org.codehaus.jackson.JsonNode;
import org.w3c.dom.Document;

public class Test {
 public static void main(String[] args) throws Exception {
		
  String client_id = "aegisep"; //클라이언트 아이디
  String secret = "7adb3bdb22db89f220549925e27e53853c5f20dc8976a06d4513016718b9da96"; //클라이언트 시크릿
		
  PayatAPI payat = new PayatAPI(client_id,secret); //생성자 호출 
  

  JsonNode real_json_list = (JsonNode) payat.api ("/openapi/v1/partner/store/list.json"); 
  System.out.println(real_json_list); 
  
  
  Document real_xml_list = (Document) payat.api ("/openapi/v1/partner/store/list.xml"); 
  System.out.println(real_xml_list); 


  Map<String,Object> param = new HashMap<String, Object>(); //파람선언
  param.put("store_screen_name", "111111"); //상점아이디
  param.put("item_name", "PayatReader"); //상품명
  param.put("item_price", "9000"); //상품가격
  File file = new File("img/test_img.gif"); //이미지 객체선언
  param.put("item_image", file); //상품이미지 
  

  JsonNode json_add_item = (JsonNode) payat.api("/openapi/v1/partner/item/add.json",param); //json형식으로 상품추가 api호출
  System.out.println(json_add_item); 



 }
}
