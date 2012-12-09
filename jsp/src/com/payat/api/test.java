package com.payat.api;

import java.io.File;
import java.util.HashMap;
import java.util.Map;

import org.codehaus.jackson.JsonNode;
import org.w3c.dom.Document;

public class test {
 public static void main(String[] args) throws Exception {
		
  String client_id = "aegisep"; //클라이언트 아이디
  String secret = "7adb3bdb22db89f220549925e27e53853c5f20dc8976a06d4513016718b9da96"; //클라이언트 시크릿
		
  PayatAPI payat = new PayatAPI(client_id,secret,true); //생성자 호출 (true이므로 개발자모드)
  
  
  //**** json ****
  JsonNode json_list = (JsonNode) payat.api ("/openapi/v1/partner/store/list.json"); 
  System.out.println(json_list); 
  payat.setDeveloperMode (false); //실서버 모드로 전환
  JsonNode real_json_list = (JsonNode) payat.api ("/openapi/v1/partner/store/list.json"); 
  System.out.println(real_json_list); 
  
  
  //**** xml ****
  payat.setDeveloperMode (true); //개발 모드로 전환
  Document xml_list = (Document) payat.api ("/openapi/v1/partner/store/list.xml"); 
  System.out.println(xml_list); 
  payat.setDeveloperMode (false); //실서버 모드로 전환
  Document real_xml_list = (Document) payat.api ("/openapi/v1/partner/store/list.xml"); 
  System.out.println(real_xml_list); 


  payat.setDeveloperMode (true); //개발 모드로 전환
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
