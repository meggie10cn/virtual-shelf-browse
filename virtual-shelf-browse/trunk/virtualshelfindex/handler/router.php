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

// PHP 5.2.6

require_once('slice/slice.php');
require_once('start/start.php');
require_once('resources/parameter_check_functions.php');

define('DOCUMENTATIONFILE', '../documentation/html/documentation.html');

// at NCSU we use a mod rewrite rule to handle service requests
// uncomment this line if you plan to use a mod rewrite rule
// parse_str($_SERVER['QUERY_STRING'], $vsiParams['querystring']);

$completeRequest = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

// at NCSU we use a mod rewrite rule to handle service requests
// uncomment these lines if you plan to use a mod rewrite rule
// 'resource_request' passed by mod_rewrite
// $reqUri = $vsiParams['querystring']['resource_request'];
// 
// $reqUri = trim($reqUri, ' /');
// 
// if (strlen($reqUri) > 0) {
//     $vsiParams['uri_path'] = explode('/', $reqUri);
// } else {
//     $vsiParams['uri_path'] = array();
// }
    

// Set defaults
$service = 'start';
$callNum = false;
$catKey = false;
$batchId = 0;
$numBefore = 0;
$numAfter = 20;
$classType = 'LC';
$resultHandler = false;
$startPoint = false;
$vsi_error = false;

// at NCSU we use a mod rewrite rule to handle service requests
// uncomment this line if you plan to use a mod rewrite rule
// Get user input
// if (isset($vsiParams['uri_path'][1]))
//     {
//     $service = strtolower($vsiParams['uri_path'][1]);
//     }

// added as work around for lack of mod-rewrite rule
// comment out this code if you put a mod-rewrite rule in place
if (isset($_GET['service']))
    {
    $user_input_service = $_GET['service'];
    $service = strtolower($user_input_service);
    if ($service != 'start' and $service != 'slice')
        {
        $service = 'start';
        }
    }

    
if (isset($_GET['callNum']))
    {
    $user_input_callNum = $_GET['callNum'];
    $callNum = check_callNum($user_input_callNum);
    $startPoint = 'callNum';
    }

if (isset($_GET['catKey']))
    {
    $user_input_catKey = $_GET['catKey'];
    $catKey = check_15_digits($user_input_catKey);
    $startPoint = 'catKey';
    }
    
if (isset($_GET['batchId']))
    {
    $user_input_batchId = $_GET['batchId'];
    $batchId = check_15_digits($user_input_batchId);
    }
    
if (isset($_GET['numBefore']))
    {
    $user_input_numBefore = $_GET['numBefore'];
    $numBefore = check_numBefore($user_input_numBefore);
    }
    
if (isset($_GET['numAfter']))
    {
    $user_input_numAfter = $_GET['numAfter'];
    $numAfter = check_numAfter($user_input_numAfter);
    }
    
if (isset($_GET['classType']))
    {
    $user_input_classType = $_GET['classType'];
    $classType = check_classType($user_input_classType);
    }


// Direct requests based on service and parameters applied
try
    {
    switch($service)
        {
        case 'documentation.html':
            echo file_get_contents(DOCUMENTATIONFILE);
            return;
        case 'slice':
            $resultHandler = new sliceHandler($batchId,$numBefore,$numAfter);
            break;
        case 'start':
            switch($startPoint)
                {
                case 'catKey':
                    $resultHandler = new startHandlerCatKey($catKey,$classType);
                    break;
                case 'callNum':
                    $resultHandler = new startHandlerCallNum($callNum,$user_input_callNum,$classType);
                    break;
                case false:
                    $resultHandler = new startHandlerDefault($classType);
                    break;
                default:
                    $vsi_error = true;
                }
            break;
        default:
            $vsi_error = true;
        }
    }
catch (Exception $e)
    {
    $vsi_error = true;
    }


if ($resultHandler)
    {
    $results_Array = $resultHandler->return_results_array();
    $results_Array['requestUrl'] = $completeRequest;
    }
    

// Check for result and either output results, try something else, or generate an error
if (empty($results_Array['results']) and $resultHandler instanceof startHandlerCallNum and $startPoint == 'callNum')
    {
    $resultHandler = new startHandlerBefore($user_input_callNum,$classType);
    $results_Array = $resultHandler->return_results_array();
    $results_Array['requestUrl'] = $completeRequest;
//     un comment the following lines to specify json mime type
//     leaving this lines commented out helps with debugging (json displays)
//     header('Cache-Control: no-cache, must-revalidate');
//     header('Content-type: application/json');
    echo json_encode($results_Array);
    }
elseif (empty($results_Array['results']))
    {
    $vsi_error = true;
    }
else
    {
//     un comment the following lines to specify json mime type
//     leaving this lines commented out helps with debugging (json displays)
//     header('Cache-Control: no-cache, must-revalidate');
//     header('Content-type: application/json');
    echo json_encode($results_Array);
    }

// Check for error
if ($vsi_error === true){
    $results_Array['error'] = 'emptyResult';
    $results_Array['requestUrl'] = $completeRequest;
    $results_Array['results'] = array();
//     un comment the following lines to specify json mime type
//     leaving this lines commented out helps with debugging (json displays)
//     header('Cache-Control: no-cache, must-revalidate');
//     header('Content-type: application/json');
    echo json_encode($results_Array);
    return;
}


?>