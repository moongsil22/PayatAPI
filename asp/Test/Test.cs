using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Net.Json;
using System.Xml;
using System.IO;

namespace Test
{
    class Test
    {
        static void Main(string[] args)
        {
            string client_id = ""; //클라이언트 아이디
            string client_secret = ""; //클라이언트 시크릿

            PayatAPI payat = new PayatAPI(client_id, client_secret, true);//생성자 호출 (true이므로 개발자모드)


            //**** json ****
            JsonObjectCollection json_list = (JsonObjectCollection)payat.api("/openapi/v1/partner/store/list.json");
            Console.WriteLine(json_list);

            Dictionary<String, Object> param_file = new Dictionary<String, Object>(); //파람선언
            param_file.Add("store_screen_name", ""); //상점아이디
            param_file.Add("item_name", ""); //상품명
            param_file.Add("item_price", ""); //상품가격
            FileStream file = new FileStream("경로", FileMode.Open); //이미지 객체선언
            param_file.Add("item_image", file); //상품이미지 
            JsonObjectCollection json_add_item = (JsonObjectCollection)payat.api("/openapi/v1/partner/item/add.json", param_file); //json형식으로 상품추가 api호출
            Console.WriteLine(json_add_item);


            payat.setDeveloperMode(false); //실서버 모드로 전환

            Dictionary<string, object> param = new Dictionary<string, object>(); //param선언
            param.Add("screen_name", ""); //상점아이디
            JsonObjectCollection real_json_list = (JsonObjectCollection)payat.api("/openapi/v1/partner/store/info.json", param); //인자를 넣어 전송
            Console.WriteLine(real_json_list);


            //**** xml ****
            payat.setDeveloperMode(true); //개발 모드로 전환
            XmlDocument xml_list = (XmlDocument)payat.api("/openapi/v1/partner/store/list.xml");
            Console.WriteLine(xml_list);
            payat.setDeveloperMode(false); //실서버 모드로 전환
            XmlDocument real_xml_list = (XmlDocument)payat.api("/openapi/v1/partner/store/list.xml");
            Console.WriteLine(real_xml_list);
        }
    }  
}
