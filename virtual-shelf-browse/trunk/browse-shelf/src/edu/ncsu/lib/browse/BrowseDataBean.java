// Author:	Andreas Orphanides, andreas_orphanides@ncsu.edu
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

// BrowseDataBean.java
// This bean acts as a very thin model layer for transporting data between the
//		Browse.java controller and the JSP display files.

package edu.ncsu.lib.browse;

import java.util.HashMap;
import java.util.LinkedHashMap;

public class BrowseDataBean {
	private int batchId;
	private String matchType;
	private LinkedHashMap<Integer,String> catKeys;
	private HashMap<Integer,String> callNums;
	private String displayType;
	private String library;
	private String libraryShort;
	
	public BrowseDataBean(int batchId) {
		this.batchId = batchId;
	}
	
	public void setMatchType (String matchType) {
		this.matchType = matchType;
	}
	
	public void setCatKeys(LinkedHashMap<Integer,String> catKeys) {
		this.catKeys = catKeys;
	}
	
	public void setCallNums(HashMap<Integer,String> callNums) {
		this.callNums = callNums;
	}
	
	public void setDisplayType(String displayType) {
		this.displayType = displayType;
	}
	
	public void setLibrary(String library) {
		this.library = library;
	}
	
	public void setLibraryShort(String libraryShort) {
		this.libraryShort = libraryShort;
	}
	
	public int getBatchId() {
		return this.batchId;
	}
	
	public String getMatchType() {
		return this.matchType;
	}
	
	public LinkedHashMap<Integer,String> getCatKeys() {
		return this.catKeys;
	}
	
	public HashMap<Integer,String> getCallNums() {
		return this.callNums;
	}
	
	public String getDisplayType() {
		return this.displayType;
	}
	
	public String getLibrary() {
		return this.library;
	}
	
	public String getLibraryShort() {
		return this.libraryShort;
	}
}
