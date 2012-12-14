using System;
using System.Xml;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Net.Json;
using System.Collections.Specialized;
using System.Collections;
using System.Net;
using System.IO;

public class UploadFile
{
    public UploadFile()
    {
        ContentType = "application/octet-stream";
    }
    public string Name { get; set; }
    public string Filename { get; set; }
    public string ContentType { get; set; }
    public Stream Stream { get; set; }
}

public partial class PayatAPI
{
	
	
	
    
    private string client_id;
    private string client_secret;

    private bool dev_mode;
    private string dev_uri = "http://dev.kkokjee.com";
    private string real_uri = "https://www.kkokjee.com";
    private string mode;

    private string access_token = "";

    /// <summary>
    /// 클라이언트아이디와 시크릿으로 실서버에 접속하는 클래스를 생성
    /// </summary>
    /// <param name="client_id">클라이언트 아이디</param>
    /// <param name="client_secret">클라이언트 시크릿</param>
    public PayatAPI(string client_id, string client_secret)
    {
        this.client_id = client_id;
        this.client_secret = client_secret;
        setDeveloperMode(false);
    }

    /// <summary>
    /// 클라이언트아이디와 시크릿으로 실서버 또는 개발서버에 접속하는 클래스를 생성
    /// </summary>
    /// <param name="client_id">클라이언트 아이디</param>
    /// <param name="client_secret">클라이언트 시크릿</param>
    /// <param name="developer_mode">개발서버 접속여부 (true=개발서버, false=실서버)</param>
    public PayatAPI(string client_id, string client_secret, bool developer_mode)
    {
        this.client_id = client_id;
        this.client_secret = client_secret;
        setDeveloperMode(developer_mode);
    }

    /// <summary>
    /// 개발서버와 실서버접속 변경
    /// </summary>
    /// <param name="DeveloperMode">개발서버 접속여부 (true=개발서버, false=실서버)</param>
    public void setDeveloperMode(bool DeveloperMode)
    {
        if (DeveloperMode)
        {
            this.access_token = "";
            this.mode = this.dev_uri;
        }
        else
        {
            this.access_token = "";
            this.mode = this.real_uri;
        }
    }

    /// <summary>
    /// 요청값이 없는 리스트 조회등에 주로 사용
    /// </summary>
    /// <param name="path">요청경로(ex-/oauth/v1/authorization.json)</param>
    /// <returns>요청경로에 따라 (json=JsonObjectCollection, xml=XmlDocument)로 리턴</returns>
    public object api(string path)
    {
        object returnValue = null;
        if (this.access_token.Equals("") || this.access_token.Length <= 0) this.setAccessToken();

        string[] path_arr = path.Split('.');
        if (path_arr[1].Equals("json"))
        {
            returnValue = this.callJson(path);
        }
        else
        {
            returnValue = this.callXml(path);
        }

        return returnValue;
    }
    /// <summary>
    /// 요청값을 함께 요청경로에 셋팅하여 사용
    /// </summary>
    /// <param name="path">요청경로(ex-/oauth/v1/authorization.json)</param>
    /// <param name="param">요청값</param>
    /// <returns>요청경로에 따라 (json=JsonObjectCollection, xml=XmlDocument)</returns>
    public object api(string path, Dictionary<string, object> param)
    {
        object returnValue = null;
        if (this.access_token.Equals("") || this.access_token.Length <= 0) this.setAccessToken();

        string[] path_arr = path.Split('.');
        if (path_arr[1].Equals("json"))
        {
            returnValue = this.callJson(path, param);
        }
        else
        {
            returnValue = this.callXml(path, param);
        }
        return returnValue;
    }


    //json을 리턴
    private JsonObjectCollection callJson(string path)
    {
        Dictionary<string, object> dic = new Dictionary<string, object>();
        dic.Add("access_token", this.access_token);
        return strTojsonlist(this.Post(mode + path, dic));
    }
    //json을 리턴 (param을 포함)
    private JsonObjectCollection callJson(string path, Dictionary<string, object> param)
    {
        Dictionary<string, object> dic = new Dictionary<string, object>();
        dic.Add("access_token", this.access_token);
        foreach (KeyValuePair<string, object> val in param)
        {
            dic.Add(val.Key, val.Value);
        }
        return strTojsonlist(this.Post(mode + path, dic));
    }
    //json으로 파싱
    private JsonObjectCollection strTojsonlist(string response_str)
    {
        JsonTextParser parser = new JsonTextParser();
        JsonObject obj = parser.Parse(response_str);
        JsonObjectCollection json_list = (JsonObjectCollection)obj;
        return json_list;
    }

    //xml을 리턴
    private XmlDocument callXml(string path)
    {
        Dictionary<string, object> dic = new Dictionary<string, object>();
        dic.Add("access_token", this.access_token);
        return strToxmllist(this.Post(mode + path, dic));
    }
    //xml을 리턴 (param을 포함)
    private XmlDocument callXml(string path, Dictionary<string, object> param)
    {
        Dictionary<string, object> dic = new Dictionary<string, object>();
        dic.Add("access_token", this.access_token);
        foreach (KeyValuePair<string, object> val in param)
        {
            dic.Add(val.Key, val.Value);
        }
        return strToxmllist(this.Post(mode + path, dic));
    }
    //xml로 파싱
    private XmlDocument strToxmllist(string response_str)
    {
        XmlDocument xml = new XmlDocument();
        xml.LoadXml(response_str);
        return xml;
    }

    //accesstoken을 가져와서 셋팅
    private void setAccessToken()
    {
        Dictionary<string, object> dic = new Dictionary<string, object>();
        dic.Add("client_id", this.client_id);
        dic.Add("client_secret", this.client_secret);

        string response_str = this.Post(mode + "/oauth/v1/authorization.json", dic);


        JsonTextParser parser = new JsonTextParser();
        JsonObject obj = parser.Parse(response_str);
        JsonObjectCollection json_list = (JsonObjectCollection)obj;

            JsonObject data = json_list["data"];
            JsonObjectCollection json_list_data = (JsonObjectCollection)data;

            this.access_token = (String)json_list_data["access_token"].GetValue();

    }


    //post connetction
    private string Post(string uri, Dictionary<string, object> param = null)
    {
        byte[] streamBytes = null;

        NameValueCollection values = new NameValueCollection();
		ArrayList files = new ArrayList();
				
        foreach (KeyValuePair<string, object> kvp in param)
        {
            if (kvp.Value is FileStream)
            {
	             UploadFile file = new UploadFile();
	             file.Name = kvp.Key;
	             file.Filename = kvp.Value.Name;
	             file.ContentType = "application/octet-stream";
	             file.Stream = kvp.Value;
	             files.Add(file);
	             
            }
            else
            {
                string str = (String)kvp.Value;
                values.Add(kvp.Key, str);
            }
        }
		
		var request = WebRequest.Create(uri);
        request.Method = "POST";
        var boundary = "---------------------------" + DateTime.Now.Ticks.ToString("x", NumberFormatInfo.InvariantInfo);
        request.ContentType = "multipart/form-data; boundary=" + boundary;
        boundary = "--" + boundary;
        
         
        using (var requestStream = request.GetRequestStream())
        {

            foreach (string name in values.Keys)
            {
                var buffer = Encoding.ASCII.GetBytes(boundary + Environment.NewLine);
                requestStream.Write(buffer, 0, buffer.Length);
                buffer = Encoding.ASCII.GetBytes(string.Format("Content-Disposition: form-data; name=\"{0}\"{1}{1}", name, Environment.NewLine));
                requestStream.Write(buffer, 0, buffer.Length);
                buffer = Encoding.UTF8.GetBytes(values[name] + Environment.NewLine);
                requestStream.Write(buffer, 0, buffer.Length);
            }


            foreach (var file in files)
            {
                var buffer = Encoding.ASCII.GetBytes(boundary + Environment.NewLine);
                requestStream.Write(buffer, 0, buffer.Length);
                buffer = Encoding.UTF8.GetBytes(string.Format("Content-Disposition: form-data; name=\"{0}\"; filename=\"{1}\"{2}", file.Name, file.Filename, Environment.NewLine));
                requestStream.Write(buffer, 0, buffer.Length);
                buffer = Encoding.ASCII.GetBytes(string.Format("Content-Type: {0}{1}{1}", file.ContentType, Environment.NewLine));
                requestStream.Write(buffer, 0, buffer.Length);
                file.Stream.CopyTo(requestStream);
                buffer = Encoding.ASCII.GetBytes(Environment.NewLine);
                requestStream.Write(buffer, 0, buffer.Length);
            }

            var boundaryBuffer = Encoding.ASCII.GetBytes(boundary + "--");
            requestStream.Write(boundaryBuffer, 0, boundaryBuffer.Length);
        }

		using (var response = request.GetResponse())
		using (var responseStream = response.GetResponseStream())
		using (var stream = new MemoryStream())
        {
            responseStream.CopyTo(stream);
            streamBytes = stream.ToArray();
            string response_str = System.Text.Encoding.GetEncoding("utf-8").GetString(streamBytes);
	        return response_str;
        }
        
    
    }
}  
