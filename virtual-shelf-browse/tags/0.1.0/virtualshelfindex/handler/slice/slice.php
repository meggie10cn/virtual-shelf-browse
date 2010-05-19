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

require_once('resources/handler.php');
require_once('resources/parameter_check_functions.php');

//this class handles all slice service requests
final class sliceHandler extends handler
    {
    
    public $startBatch;
    public $numBefore;
    public $numAfter;
    
    function __construct($startBatch, $numBefore, $numAfter)
        {
        $this->startBatch = $startBatch;
        $this->numBefore = $numBefore;
        $this->numAfter = $numAfter;
        $this->query = "SELECT group_id, catalog_key, MIN(call_number) as min_call_number FROM vs_index_os WHERE group_id BETWEEN " . $startBatch . " - " . $numBefore . " AND  " . $startBatch . " + " . $numAfter . " GROUP BY group_id;";
        $this->parameters['startBatch'] = $startBatch;
        $this->parameters['numBefore'] = $numBefore;
        $this->parameters['numAfter'] = $numAfter;
        }
        
    public function get_results($row)
        {
        $temp_row_array['batchId'] = $row[0];
        $temp_row_array['catKey'] = $row[1];
        $temp_row_array['minCallNum'] = htmlentities($row[2]);
        return $temp_row_array;
        }
    }

?>