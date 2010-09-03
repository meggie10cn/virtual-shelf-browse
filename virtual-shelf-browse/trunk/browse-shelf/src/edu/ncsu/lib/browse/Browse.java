// Authors:	Andreas Orphanides, andreas_orphanides@ncsu.edu
//			Emily Lynema, emily_lynema@ncsu.edu
//			Stephen Cole
// 
// Terms of Use: MIT License/X11 License
// 
// Copyright (C) 2010  NCSU Libraries, Raleigh, NC, http://www.lib.ncsu.edu/
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
//

// Browse.java
// This servlet acts as the controller for the virtual browse JSP application.
// Valid parameters are:
// batchId: Index identifier for desired item from virtual shelf browse service
// callNumber: Item call number
// classType: "LC" for library of congress, "SUDOC" for gov't doc, default "LC"
// before: Number of items to return before requested item, default "14"
// after: Number of items to return after requested item, default "15"
// displayType: "full" -- request a full HTML document; "popup" -- request HTML 
// 		suitable for a popup box "data: -- request HTML suitable for loading data 
//		for AJAX update; default "full"
//
// batchId or callNumber must be provided.
// The configuration parameter "vsiUrl" must be set in web.xml to point to an 
//		instance of the Virtual Shelf Index service.

package edu.ncsu.lib.browse;

import java.io.IOException;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.LinkedHashMap;
import java.util.Map;

import javax.servlet.ServletConfig;
import javax.servlet.ServletContext;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.RequestDispatcher;

import org.apache.commons.httpclient.HttpClient;
import org.apache.commons.httpclient.HttpStatus;
import org.apache.commons.httpclient.methods.GetMethod;
import org.apache.commons.lang.StringUtils;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import org.json.JSONTokener;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;



public class Browse extends HttpServlet {
	
	String address, service;
    ServletConfig config;
    ServletContext context;
    Logger log = LoggerFactory.getLogger(Browse.class);
    String vsiUrl = "";
    public BrowseDataBean browseData;
    
    // methods
    public void init() {
    
    	// get reference to servlet config & context
        config = getServletConfig();
        context = config.getServletContext();
        vsiUrl = config.getInitParameter("vsiUrl");
    }
    
    public void doGet(HttpServletRequest httpRequest, HttpServletResponse httpResponse) throws ServletException, IOException {
		if(!StringUtils.isNotBlank(httpRequest.getParameter("batchId")) && !StringUtils.isNotBlank(httpRequest.getParameter("callNumber"))){
			throw new ServletException("Invalid request: neither <batchId> or <callNumber> parameter were specified");
		}

		String matchType = "slice";
		String classType = "LC";
		String batchId = httpRequest.getParameter("batchId");
		String callNumber = httpRequest.getParameter("callNumber");

		// prevent html injection by stripping '<' and '>'
		if (callNumber != null) {
			callNumber = callNumber.replaceAll("<", "").replaceAll(">", "");
		}
		try {
			if(!StringUtils.isNotEmpty(batchId)){
				if(httpRequest.getParameter("classType")!=null){
					classType = httpRequest.getParameter("classType");
				}
				String[] batchIdArray = retrieveBatchId(callNumber, classType);
				batchId = batchIdArray[0];
				if(batchId==null){
					log.error("CatKey or callNumber was not found in virtual browse index");
					dispatchError(httpRequest, httpResponse);
					return;
				}
				matchType = batchIdArray[1];
				httpRequest.getSession().setAttribute("virtualBrowseCallNumber", callNumber);
			}
			int numberBefore = 14;
			if(httpRequest.getParameter("before")!=null){
				numberBefore = Integer.parseInt(httpRequest.getParameter("before"));
			}
			int numberAfter = 15;
			if(httpRequest.getParameter("after")!=null){
				numberAfter = Integer.parseInt(httpRequest.getParameter("after"));
			}
			JSONObject result = getData(vsiUrl + "router.php?service=slice&batchId=" + batchId + "&numBefore=" + numberBefore + "&numAfter=" + numberAfter);
			log.info(vsiUrl + "router.php?service=slice&batchId=" + batchId + "&numBefore=" + numberBefore + "&numAfter=" + numberAfter);			JSONArray results = result.getJSONArray("results");
			//log.info("Got " + results.length() + " results");
	
			ArrayList<String> requestKeys = new ArrayList<String>();
			int start = 0;
			int end = results.length();
			//adjust the start and end point to not include the current value if the numberBefore or the numberAfter is 0
			if(numberBefore==0){ start = 1;}
			if(numberAfter==0){ end = results.length()-1;}
			StringBuffer buf = new StringBuffer();
			// int firstBatchId = 0;
			LinkedHashMap<Integer, String> currentCatkeys = new LinkedHashMap<Integer, String>();
			HashMap<Integer, String> currentCallNums = new HashMap<Integer, String>();
			for(int i=start; i<end; i++){
				JSONObject item = results.getJSONObject(i);
				int currentBatchId = item.getInt("batchId");
				currentCallNums.put(new Integer(currentBatchId), item.getString("minCallNum"));
				String catkey = "NCSU" + item.getString("catKey");
				currentCatkeys.put(new Integer(currentBatchId), catkey);
				requestKeys.add(catkey);
				buf.append(catkey + ", ");
			}		
			
			long startTime = System.currentTimeMillis();
			log.info("Identified neighboring catkeys: " + buf.toString() + "finished in " + calculateTime(startTime));
			
			browseData = new BrowseDataBean(Integer.parseInt(batchId));
			//Map<String, Object> model = new HashMap<String, Object>();
			//model.put("returnedBatchId", Integer.parseInt(batchId));
			
			browseData.setMatchType(matchType);
			browseData.setCatKeys(currentCatkeys);
			browseData.setCallNums(currentCallNums);
			//model.put("matchType", matchType);
			//model.put("currentCatkeys", currentCatkeys);
			//model.put("currentCallNums", currentCallNums);
	
			String jsp;
			String displayType = httpRequest.getParameter("displayType");
			if(displayType!=null && displayType.equals("data")){
				jsp = "browseData";
			}else if(displayType!=null && displayType.equals("popup")){
				jsp = "browsePopup";
			}else{ //if(displayType.equals("full")){
				if(callNumber==null){
					if (httpRequest.getSession().getAttribute("virtualBrowseCallNumber") != null) {
						callNumber = httpRequest.getSession().getAttribute("virtualBrowseCallNumber").toString();
					}
					if(callNumber==null){
						callNumber = results.getJSONObject(numberBefore).getString("minCallNum");
					}
				}
	
				jsp = "browse";
			}
			
			browseData.setDisplayType(displayType);
			browseData.setLibrary("Your Library");
			browseData.setLibraryShort("Library");
			httpRequest.setAttribute("browseData",browseData);
			//model.put("displayType", displayType);
			//model.put("library", "Your library");
			//model.put("libraryShort", "Library");
			//log.info("TRLN processed request for " + buf.toString() + " in " + calculateTime(fullTime));
			jsp = "jsp/" + jsp + ".jsp";
			RequestDispatcher dispatcher = httpRequest.getRequestDispatcher(jsp);
			try {
				dispatcher.forward(httpRequest, httpResponse);
			}
			catch (Exception e) {
				dispatchError(httpRequest, httpResponse);
			}
		}
		catch(JSONException e) {
			log.error("Just threw a JSON exception.");
			e.printStackTrace();
			dispatchError(httpRequest, httpResponse);
		}
	}

	private String[] retrieveBatchId(String callNumber, String classType) throws IOException, JSONException {
		String url = vsiUrl + "router.php?service=start&classType="+classType+"&callNum=" + URLEncoder.encode(callNumber, "UTF-8");
		JSONObject result = getData(url);
		
		JSONArray array = result.getJSONArray("results");
		
		if(array==null || array.length()==0){
			return null;
		}else{
			String[] output = new String[2];
			output[0] = array.getJSONObject(0).getString("batchId");
			output[1] = result.getJSONObject("parameters").getString("matchType");
			return output;
		}
	}
		
	private JSONObject getData(String url) throws IOException, JSONException{
		log.info("Polling url: " + url);
    	HttpClient client = new HttpClient();
    	GetMethod method = new GetMethod(url);
    	int statusCode = client.executeMethod(method);
    	if (statusCode != HttpStatus.SC_OK) {
	    	log.error("Call to vsi start failed with result: "+ method.getStatusLine());
	    	throw new IOException("Error communicating with VSI service");
	    }
    	String response = method.getResponseBodyAsString();
		return new JSONObject(new JSONTokener(response));
	}
	
	private void validate(HttpServletRequest r) throws ServletException {
		if(!StringUtils.isNotBlank(r.getParameter("before"))){
			throw new ServletException("Invalid request: <before> parameter not specified");
		}
		if(!StringUtils.isNotBlank(r.getParameter("after"))){
			throw new ServletException("Invalid request: <after> parameter not specified");
		}
		if(!StringUtils.isNotBlank(r.getParameter("displayType"))){
			throw new ServletException("Invalid request: <displayType> parameter not specified");
		}

	}
	
	private String calculateTime(long startTime) {
		long time = System.currentTimeMillis() - startTime;
		long minutes = time / 60000;
		long seconds = (time % 60000)/1000;
		//long millis = (time % 60000);
		return minutes + " minutes " + seconds + " seconds" ;
	}

    private void dispatchError(HttpServletRequest request, HttpServletResponse response) {
        RequestDispatcher errDispatcher = request.getRequestDispatcher("jsp/error.jsp");
        try {
           errDispatcher.forward(request, response);
        } catch (Exception e) {
            System.out.println("Error forwarding to error.jsp");
            e.printStackTrace();
        }
    }

}

