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
require_once('resources/sudoc_padding_functions.php');
require_once('resources/call_number_padding_functions.php');
require_once('resources/parameter_check_functions.php');

class startHandler extends handler
    {
    
    public function get_results($row)
        {
        $temp_row_array['batchId'] = $row[0];
        return $temp_row_array;
        }
    }

// handles start requests by catalog key
final class startHandlerCatKey extends startHandler
    {
    
    public $catKey;

    function __construct($catKey)
        {
        $this->catKey = $catKey;
        $this->query = "SELECT group_id FROM sorted_call_no_index WHERE catalog_key = '" . $catKey ."' LIMIT 1";
        $this->parameters['catKey'] = $catKey;
        }
    }

//handles start requests by call numbers
final class startHandlerCallNum extends startHandler
    {
    
    public $callNum;
    public $user_input_callNum;
    public $classType;
    
    function __construct($callNum,$user_input_callNum,$classType)
        {        
        $this->callNum = mysql_escape_string($callNum);
        $this->classType = $classType;
        $this->query = "SELECT group_id FROM sorted_call_no_index WHERE call_number_search LIKE '" . $callNum ."%' AND classification = '" . $classType . "' ORDER BY call_number_padded LIMIT 1";
        $this->parameters['callNum'] = $callNum;
        $this->parameters['classType'] = $classType;
        $this->parameters['matchType'] = 'startsWith';
        }
    }

//handles start requests by classification scheme (also default case)    
final class startHandlerDefault extends startHandler
    {
    
    public $classType;

    function __construct($classType)
        {
        $this->classType = $classType;
        $this->query = "SELECT group_id FROM sorted_call_no_index WHERE classification = '" . $classType ."' ORDER BY call_number_padded LIMIT 1";
        $this->parameters['classType'] = $classType;
        }
    }

// handles case where call number specified in request doesn't exist.
// this is a fallback attempt to place the call number in correct sequence.
// highly dependent on user's input looking something like a call number
final class startHandlerBefore extends startHandler
    {
    
    public $callNum;
    public $classType;
    protected $padded_call_number;

    function __construct($callNum, $classType)
        {
        
        switch ($classType)
            {
            case 'LC':
                $padded_call_number = padding_LC($callNum);
                break;
            case 'SUDOC':
                $padded_call_number = padding_SUDOC($callNum);
                break;
            default:
                $padded_call_number = padding_LC($callNum);
            }
        
        $this->callNum = mysql_escape_string($padded_call_number); //NOT CONNECTED TO DB HERE.
        $this->classType = $classType;
        $this->query = "SELECT group_id FROM sorted_call_no_index WHERE call_number_padded > '" . $padded_call_number ."' AND classification = '" . $classType . "' AND call_number_padded != '' LIMIT 1;";
        $this->parameters['callNum'] = $padded_call_number;
        $this->parameters['classType'] = $classType;
        $this->parameters['matchType'] = 'closestMatch';
        }
    }
    
?>