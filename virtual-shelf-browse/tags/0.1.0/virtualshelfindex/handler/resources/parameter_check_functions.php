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


// these functions check and modifie the user input
// may need to be updated/modified for use at other institutions

function check_callNum($input)
    {
    $trimmed = trim($input);
    $trimmed = strtoupper($trimmed);
    $trimmed = str_replace( ' ', '', $trimmed);
    return $trimmed;
    }
    
    
function check_15_digits($input) //for catKey and batchID
    {
    $trimmed = trim($input);
    if (preg_match('/^\d{1,15}$/', $trimmed))
        {
        return $trimmed;
        }
    else
        {
        $trimmed = 0;
        return $trimmed;
        }
    }


function check_numBefore($input)
    {
    $trimmed = trim($input);
    if (preg_match('/^\d{1,4}$/', $trimmed))
        {
        return $trimmed;
        }
    else
        {
        $trimmed = 0;
        return $trimmed;
        }
    }
    
function check_numAfter($input)
    {
    $trimmed = trim($input);
    if (preg_match('/^\d{1,4}$/', $trimmed))
        {
        return $trimmed;
        }
    else
        {
        $trimmed = 20;
        return $trimmed;
        }
    }

// modify for allowable classification type parameters
function check_classType($input)
    {
    $trimmed = trim($input);
    $trimmed = strtoupper($trimmed);
    if ($trimmed == 'LC' or $trimmed == 'SUDOC')
        {
        return $trimmed;
        }
    else
        {
        $trimmed = 'LC';
        return $trimmed;
        }
    }
?>