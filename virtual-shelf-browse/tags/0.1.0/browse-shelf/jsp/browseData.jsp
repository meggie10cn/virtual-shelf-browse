<!-- 
Authors:	Andreas Orphanides, andreas_orphanides@ncsu.edu
			Emily Lynerma, emily_lynema@ncsu.edu
			Troy Hurteau, troy_hurteau@ncsu.edu

Terms of Use: MIT License/X11 License

Copyright (C) 2010  NCSU Libraries, Raleigh, NC, http://www.lib.ncsu.edu/

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

browseData.jsp
Data-only content for ajax loading into browse.jsp or browsePopup.jsp

-->

<%@ page contentType="text/html;charset=UTF-8" language="java" %> 
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions"%>

<jsp:useBean id="browseData" scope="request" type="edu.ncsu.lib.browse.BrowseDataBean" />

<div class="browseDataContent" id="browseDataContent">
	<ul id="mycarousel" class="mycarousel jcarousel-skin-tango">
		<c:forEach items="${browseData.catKeys}" var="batchIdItem" varStatus="iter">
			<c:set var="catkey" value="${browseData.catKeys[batchIdItem.key]}" />

			<c:if test='${matchType eq "closestMatch" && batchIdItem.key eq browseData.batchId && param.browse ne "true"}'>
				<li class="browseItem coverBrowse ${batchIdItem.key}">
					<div class="recordImageBlock noResults selected">
						<div class="coverBlock noResultsBlock">
							<div class="noResultsCover">
								<div class="noResultsContents">
									<table>
										<tr>
											<td align="center" valign="middle">
												<div class="noResultsMsg">
													The call number ${sessionScope.virtualBrowseCallNumber} was not found. Here are the nearest call numbers.<br/>
												</div>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
						<div class="locationBlock">
							&nbsp;
						</div>
					</div>
				</li>
			</c:if> 
		
			<li class="browseItem coverBrowse ${batchIdItem.key}">
				<div class="recordImageBlock ${batchIdItem.key} <c:if test='${browseData.matchType eq "startsWith" && batchIdItem.key eq returnedBatchId}'>selected</c:if>" id="cover${batchIdItem.key}" batchId="${batchIdItem.key}">
					<div class="coverBlock">
						<a href="#yourCatalogLinkHere" style="text-decoration: none;">
							<div class="coverImage fakeImage" isbn="ISBN goes here for cover loading" oclc="OCLC goes here for cover loading" upc="UPC goes here for cover loading">
								<div class="coverContents cover${batchIdItem.key%5 + 1}">		
									<div class='coverShadowRight'><div class='coverShadowBottom'><div class='coverShadowTop'><div class='coverShadowLeft'><div class='coverShadowCorner'>						
									<table class="coverTable">
										<tr>
											<td align="center" valign="middle">
												<div class="title">
													To Serve Man
												</div>
												<div class="tilde">
													~
												</div>
												<div class="author">
													Serling, Rod
												</div>
											</td>
										</tr>
									</table>
									</div></div></div></div></div>
								</div>
							</div>
						</a>
					</div>				
					<div class="locationBlock">
						<table>
							<tr>
								<td>
									Availability
								</td>
							</tr>
						</table>
					</div>
					<div class="mouseoverBlock">
						<div class="title">
							To Serve Man
							<span class="pubYear">(1962)</span>
						</div>
						<div class="author">
							Serling, Rod
						</div>
						<div class="callNum">
							<c:choose>
								<c:when test='${fn:contains(browseData.callNums[batchIdItem.key]," v.")}'>
									${fn:substringBefore(browseData.callNums[batchIdItem.key], " v.")}
								</c:when>
								<c:otherwise>
									<c:choose>
										<c:when test='${fn:contains(browseData.callNums[batchIdItem.key]," V.")}'>
											${fn:substringBefore(browseData.callNums[batchIdItem.key], " V.")}
										</c:when>
										<c:otherwise>
											${browseData.callNums[batchIdItem.key]}
										</c:otherwise>
									</c:choose>
								</c:otherwise>
							</c:choose>
						</div>
						<c:if test='${param.debug eq "true" }'>
							<div class="batchID">
								Batch ID:
								${batchIdItem.key}
							</div>
						</c:if>
					</div>
				</div>
			</li>		
		</c:forEach>
	</ul>
	
	
	<ul class="debug" matchedBatchId="${returnedBatchId}" matchType="${matchType}" <c:if test='${param.debug ne "true" }'>style="display: none;"</c:if>>
		<li>
			Searched call number: ${param.callNumber}<br/>
			Matched batch ID: ${browseData.batchId}<br />
			Match type: ${browseData.matchType}<br />
			Browse: <c:choose><c:when test='${param.browse eq "true" }'>True</c:when><c:otherwise>False</c:otherwise></c:choose>
			
		</li>
	</ul>
	
	<ul id="listview" class="listview">
		<c:forEach items="${browseData.catKeys}" var="batchIdItem" varStatus="iter">
			<c:set var="catkey" value="${browseData.catKeys[batchIdItem.key]}" />			
			<li class="browseItem listBrowse <c:if test="${iter.index lt 12 || iter.index gt 16 }">hidden</c:if> <c:if test='${matchType eq "startsWith" && batchIdItem.key eq returnedBatchId}'>selected</c:if> ${batchIdItem.key}" id="list${batchIdItem.key}" batchId="${batchIdItem.key}">
				<c:if test='${matchType eq "closestMatch" && batchIdItem.key eq returnedBatchId && param.browse ne "true"}'>
					<hr />
					<div class="recordListBlock noResults">
						<div class="title noResultsMsg">
							The call number <em>${sessionScope.virtualBrowseCallNumber}</em> was not found. Here are the nearest call numbers.<br/>
						</div>
					</div>
				</c:if>
			
				<hr />
				<div class="recordListBlock" batchId="${batchIdItem.key}">				
					<div class="title">
						<a href="#link_to_catalog">To Serve Man</a>
					</div>
					<table class="metadata">
						<c:if test='${param.debug eq "true" }'>
							<tr>
								<th class="label">
									Batch ID:
								</th>
								<td class="data">
									${batchIdItem.key}
								</td>
							</tr>
						</c:if>
						<tr>
							<th class="label author">
								Author:
							</th>
							<td class="data author">
								Serling, Rod
							</td>
						</tr>
						<tr>
							<th class="label published">
								Published:
							</th>
							<td class="data published">
								1962
							</td>
						</tr>
						<tr>
							<th class="label callnum">
								Call number:
							</th>
							<td class="data callnum">
								${browseData.callNums[batchIdItem.key]}
							</td>
						</tr>
						<tr>
							<th class="label locations">
								Locations:
							</th>
							<td class="data locations">
								<table>
									<tr>
										<td>
											Availability goes here.
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</li>
		</c:forEach>
	</ul>
</div>