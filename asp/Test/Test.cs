using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Net.Json;
using System.Xml;

namespace Test
{
    class Test
    {
        static void Main(string[] args)
        {
            string client_id = "aegisep"; //클라이언트 아이디
            string client_secret = "7adb3bdb22db89f220549925e27e53853c5f20dc8976a06d4513016718b9da96"; //클라이언트 시크릿

            PayatAPI payat = new PayatAPI(client_id, client_secret, true);//생성자 호출 (true이므로 개발자모드)


            //**** json ****
            JsonObjectCollection json_list = (JsonObjectCollection)payat.api("/openapi/v1/partner/store/list.json");
            Console.WriteLine(json_list);
            payat.setDeveloperMode(false); //실서버 모드로 전환

            Dictionary<string, object> param = new Dictionary<string, object>(); //param선언
            param.Add("screen_name", "111111"); //인자 추가
            JsonObjectCollection real_json_list = (JsonObjectCollection)payat.api("/openapi/v1/partner/store/info.json", param); //인자를 넣어 전송
            Console.WriteLine(real_json_list);


            //**** xml ****
            /*
            payat.setDeveloperMode(true); //개발 모드로 전환
            XmlDocument xml_list = (XmlDocument)payat.api("/openapi/v1/partner/store/list.xml");
            Console.WriteLine(xml_list);
            payat.setDeveloperMode(false); //실서버 모드로 전환
            XmlDocument real_xml_list = (XmlDocument)payat.api("/openapi/v1/partner/store/list.xml");
            Console.WriteLine(real_xml_list);
            */
        }
    }
}
