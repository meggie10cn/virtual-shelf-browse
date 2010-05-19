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


// this function adapted from older perl script created at NCSU
function padding_LC($unpadded_call_number)
    {
    $unpadded_call_number = strtoupper($unpadded_call_number);
    $unpadded_call_number = trim($unpadded_call_number);

    // regex adapted from http://code.google.com/p/library-callnumber-lc/wiki/Home
    // used under BSD License: http://www.opensource.org/licenses/bsd-license.php
    $pattern = "/^([A-Z]{1,3})\s*(?:(\d+)(?:\s*?\.\s*?(\d+))?)?\s*(?:\.?\s*([A-Z])\s*(\d+|\Z)?)?\s*(?:\.?\s*([A-Z])\s*(\d+|\Z)?)?\s*(?:\.?\s*([A-Z])\s*(\d+|\Z)?)?(\s+.+?)?\s*$/";
    // end regex
    
    if (preg_match($pattern, $unpadded_call_number, $regs))
        {
        $initial_letters = $regs[1];
        $class_number = $regs[2];
        $decimal_number = $regs[3];
        $cutter_1_letter = $regs[4];
        $cutter_1_number = $regs[5];
        $cutter_2_letter = $regs[6];
        $cutter_2_number = $regs[7];
        $cutter_3_letter = $regs[8];
        $cutter_3_number = $regs[9];
        $the_trimmings = $regs[10];
                
        if (strlen($initial_letters))
            {
            $initial_letters = str_pad($initial_letters, 3, ' ', STR_PAD_RIGHT);
            }
                
        if (strlen($class_number))
            {
            $class_number = str_pad($class_number, 6, '0', STR_PAD_LEFT);
            }
            
        if (strlen($cutter_1_letter))
            {
            $cutter_1_letter = ' .' . $cutter_1_letter;
            }
                    
        if (strlen($cutter_2_letter))
            {
            $cutter_2_letter = '  ' . $cutter_2_letter;
            }
            
        if (strlen($the_trimmings))
            {
            $the_trimmings = '  ' . trim($the_trimmings);
            }
        
  
        $normalized = $initial_letters . $class_number;
        
        if (strlen($decimal_number))
            {
            $normalized = $normalized . '.';
            }
          
        $normalized = $normalized . $decimal_number . $cutter_1_letter . $cutter_1_number . $cutter_2_letter . $cutter_2_number . $the_trimmings;
        
        $padded_call_number = $normalized;
        
        }
     else
        {
        $padded_call_number = $unpadded_call_number;
        }
    return $padded_call_number;
    }
    
?>