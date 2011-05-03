<?php

// Author: Cory Lown, cory_lown@ncsu.edu
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

function db_connect()
    {
    $con = @mysql_connect("@mysql.host@","@mysql.user@","@mysql.password@"); // MODIFY
    if (!$con)
        {
	echo mysql_error();
        $vsi_error = true;
        }
    //select database
    @mysql_select_db("@mysql.dbname@", $con); // MODIFY
    }
    
function get_result_object($query)
    {    
    $result = @mysql_query($query);
    if (!$result)
        {
        $vsi_error = true;
        }
    return $result;
    }

// this is the primary generic handler for all child classes
abstract class handler
    {
    protected $query;
    protected $response;
    protected $parameters = array();
    
    public function return_results_array()
        {
        $results_array = array();
        
        db_connect();
        $result = get_result_object($this->query);
        
        while($row = @mysql_fetch_array($result))
            {
            $temp_row_array = $this->get_results($row);
            $results_array[] = $temp_row_array;
            }
        $response['results'] = $results_array;
        $response['parameters'] = $this->parameters;
        return $response;
        }

    abstract public function get_results($row);

    }
    
?>