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


// this function pads SUDOC call numbers for sorting
// based on similar function for sorting LC

function padding_SUDOC($unpadded_call_number)
    {

    $unpadded_call_number = trim($unpadded_call_number);
    
    $pattern = '/^([A-Z]{1,4})\s*(\d*)\s*(\D*)(\d*)(\D*)(\d*)(\D*)(\d*)(\D*)(\d*)(\D*)(\d*)/';

    if (preg_match($pattern, $unpadded_call_number, $regs))
        {
        $initial_letters = $regs[1];
        $numbers_01 = $regs[2];
        $chars_01 = $regs[3];
        $numbers_02 = $regs[4];
        $chars_02 = $regs[5];
        $numbers_03 = $regs[6];
        $chars_03 = $regs[7];
        $numbers_04 = $regs[8];
        $chars_04 = $regs[9];
        $numbers_05 = $regs[10];
        $chars_05 = $regs[11];
        $numbers_06 = $regs[12];
        
        if (strlen($initial_letters))
            {
            $initial_letters = $initial_letters . ' ';
            }
                
        if (strlen($numbers_01))
            {
            $numbers_01 = str_pad($numbers_01, 6, '0', STR_PAD_LEFT);
            }
            
        if (strlen($numbers_02))
            {
            $numbers_02 = str_pad($numbers_02, 6, '0', STR_PAD_LEFT);
            }
                    
        if (strlen($numbers_03))
            {
            $numbers_03 = str_pad($numbers_03, 6, '0', STR_PAD_LEFT);
            }
            
        if (strlen($numbers_04))
            {
            $numbers_04 = str_pad($numbers_04, 6, '0', STR_PAD_LEFT);
            }
            
        if (strlen($numbers_05))
            {
            $numbers_05 = str_pad($numbers_05, 6, '0', STR_PAD_LEFT);
            }

        if (strlen($numbers_06))
            {
            $numbers_06 = str_pad($numbers_06, 6, '0', STR_PAD_LEFT);
            }
                        
        $normalized =  $initial_letters . $numbers_01 . $chars_01 . $numbers_02 . $chars_02 . $numbers_03 . $chars_03 . $numbers_04 . $chars_04 . $numbers_05 . $chars_05 . $numbers_06;
        
        $padded_call_number = $normalized;
        }
     else
        {
        $padded_call_number = $unpadded_call_number;
        }
    return $padded_call_number;
    }
   
?>