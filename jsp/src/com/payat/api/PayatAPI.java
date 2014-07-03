package com.payat.api;

import java.io.File;
import java.io.StringReader;
import java.net.URI;
import java.util.*;
import java.util.Map.Entry;

import javax.net.ssl.SSLContext;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.client.utils.URIUtils;
import org.apache.http.entity.mime.MultipartEntity;
import org.apache.http.entity.mime.content.*;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.util.EntityUtils;
import org.apache.http.conn.ClientConnectionManager;
import org.apache.http.conn.scheme.Scheme;
import org.apache.http.conn.scheme.SchemeRegistry;
import org.apache.http.conn.ssl.SSLSocketFactory;

import org.codehaus.jackson.JsonNode;
import org.codehaus.jackson.map.ObjectMapper;
import org.w3c.dom.Document;
import org.xml.sax.InputSource;

import java.security.cert.CertificateException;
import java.security.cert.X509Certificate;

import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;



public class PayatAPI {

	private String ClientID = "";
	private String ClientSecret = "";
	private String accessToken = "";
	
	private String scheme = "";
	private String host = "";
	private int port = 0;
	private String path = "";
	
	/**
	 * 클라이언트아이디와 시크릿으로 실서버에 접속하는 클래스를 생성
	 * @param ClientID - 클라이언트 아이디
	 * @param ClientSecret - 클라이언트 시크릿
	 */
	public PayatAPI(String ClientID, String ClientSecret){
		this.ClientID = ClientID;
		this.ClientSecret = ClientSecret;
		this.accessToken = "";
		this.scheme = "https";
		this.host = "www.kkokjee.com";
		this.port = 443;
	}


	/**
	 * 요청값이 없는 리스트 조회등에 주로 사용
	 * @param path - 요청경로(ex-/oauth/v1/authorization.json)
	 * @return Object - 요청경로에 따라 (json=JsonNode, xml=Document)로 리턴
	 * @throws Exception
	 */
	public Object api (String path) throws Exception {
		Object returnValue = null;
		String[] path_arr = path.split("\\.");
		if(path_arr[1].equals("xml")) returnValue = this.callDocument (path);
		else returnValue = this.callJsonNode (path);
		return returnValue;
	}
	/**
	 * 요청값을 함께 요청경로에 셋팅하여 사용
	 * @param path - 요청경로(ex-/oauth/v1/authorization.json)
	 * @param params - 요청값
	 * @return Object - 요청경로에 따라 (json=JsonNode, xml=Document)
	 * @throws Exception
	 */
	public Object api (String path,Map<String,Object> params) throws Exception {
		Object returnValue = null;
		String[] path_arr = path.split("\\.");
		if(path_arr[1].equals("xml")) returnValue = this.callDocument(path,params);
		else returnValue = this.callJsonNode(path,params);
		return returnValue;
	}

	//access token을 반환받아 셋팅
	private void getAccessToken() throws Exception{
		if(this.accessToken.length() <= 0){
			this.path = "/oauth/v1/authorization.json";
			
			Map<String,Object> params = new HashMap<String, Object>();
			params.put("client_id", ClientID);
			params.put("client_secret", ClientSecret);
			JsonNode returnValue = stringToJson(this.getEntity(params));

			if(returnValue.get("status").asText().trim().equals("ok")){
				this.accessToken = returnValue.get("data").get("access_token").asText();
			}else{
				throw new Exception(returnValue.get("message").asText());
			}
		}
	}
	

	//**** 리턴타입 : json ****
	private JsonNode callJsonNode(String path) throws Exception {
		if(this.accessToken.equals("") || this.accessToken == null){
			this.getAccessToken();
		}
		this.path = path;
		Map<String, Object> params = new HashMap<String, Object>();
		params.put("access_token", this.accessToken);
		JsonNode returnValue = stringToJson(this.getEntity(params));
		return returnValue;
	}
	private JsonNode callJsonNode(String path ,Map<String,Object> params) throws Exception {
		if(this.accessToken.equals("") || this.accessToken == null){
			this.getAccessToken();
		}
		this.path = path;
		params.put("access_token", this.accessToken);
		JsonNode returnValue = stringToJson(this.getEntity(params));
		return returnValue;
	}
	

	//**** 리턴타입 : xml ****
	private Document callDocument(String path) throws Exception {
		if(this.accessToken.equals("") || this.accessToken == null){
			this.getAccessToken();
		}
		this.path = path;
		Map<String, Object> params = new HashMap<String, Object>();
		params.put("access_token", this.accessToken);
		Document returnValue = stringToXml(this.getEntity(params));
		return returnValue;
	}
	private Document callDocument(String path,Map<String,Object> params) throws Exception {
		if(this.accessToken.equals("") || this.accessToken == null){
			this.getAccessToken();
		}
		this.path = path;
		params.put("access_token", this.accessToken);
		Document returnValue = stringToXml(this.getEntity(params));
		return returnValue;
	}


	//**** parser & connector ****
	private Document stringToXml (HttpEntity entity) throws Exception{


		String xmlString = new String(EntityUtils.toByteArray(entity),"UTF-8");

        DocumentBuilderFactory t_dbf = DocumentBuilderFactory.newInstance();
        DocumentBuilder t_db = t_dbf.newDocumentBuilder();
        InputSource t_is = new InputSource();
        t_is.setCharacterStream(new StringReader(xmlString));
        Document document = t_db.parse(t_is);

		return document;
	}
	private JsonNode stringToJson (HttpEntity entity) throws Exception{
		ObjectMapper mapper = new ObjectMapper();
		JsonNode json = mapper.readValue(EntityUtils.toString(entity),JsonNode.class);
		return json;
	}
	private HttpEntity getEntity (Map<String,Object> Params) throws Exception {
	    URI uri = URIUtils.createURI(this.scheme,this.host,this.port,this.path,null,null );
	    HttpClient httpclient = new DefaultHttpClient();
		HttpPost httppost = new HttpPost(uri.toString());


	    MultipartEntity mpEntity = new MultipartEntity();
		for (Entry<String,Object> e : Params.entrySet()){

			if(e.getValue() instanceof File){
			    ContentBody cbFile = new FileBody((File) e.getValue(), "image/jpeg");
			    mpEntity.addPart("item_image", cbFile);
			}else{
				mpEntity.addPart(e.getKey(),new StringBody(e.getValue().toString()));	
			}
		}
	    httppost.setEntity(mpEntity);
	    httpclient = this.wrapClient(httpclient);


		HttpResponse response = httpclient.execute(httppost);
		HttpEntity entity = response.getEntity();
		
		
		return entity;
	}
	
	@SuppressWarnings("deprecation")
	public HttpClient wrapClient(HttpClient base) {
	     try {
	         SSLContext ctx = SSLContext.getInstance("TLS");
	         X509TrustManager tm = new X509TrustManager() {
	             public void checkClientTrusted(X509Certificate[] xcs, String string) throws CertificateException { }
	  
	             public void checkServerTrusted(X509Certificate[] xcs, String string) throws CertificateException { }
	  
	             public X509Certificate[] getAcceptedIssuers() {
	                 return null;
	             }
	         };
	         ctx.init(null, new TrustManager[]{tm}, null);
	         SSLSocketFactory ssf = new SSLSocketFactory(ctx);
	         ssf.setHostnameVerifier(SSLSocketFactory.ALLOW_ALL_HOSTNAME_VERIFIER);
	         ClientConnectionManager ccm = base.getConnectionManager();
	         SchemeRegistry sr = ccm.getSchemeRegistry();
	         sr.register(new Scheme("https", ssf, 443));
	         return new DefaultHttpClient(ccm, base.getParams());
	     } catch (Exception ex) {
	         return null;
	     }
	 }
	
}
