<?php namespace Random;
    $CHARS = [];
    function generateChars(): void {
        if(count($CHARS) > 0) return;
        $NUMBER = [];
        $LOWER = [];
        $UPPER = [];
        while(count($LOWER) < 26) $LOWER[] = chr(count($LOWER) + ord('a'));
        while(count($NUMBER) < 9) $NUMBER[] = chr(count($NUMBER) + ord('1'));
        $UPPER = array_map(fn($x): string => strtoupper($x), $LOWER);
        $CHARS = array_merge($NUMBER, $LOWER, $UPPER);
    }

    function gernerateRandomCode(int $size = 17): string { 
        generateChars();
        $output = "";
        while(strlen($output) < $size) $output = $output . $CHARS[rand(0, count($CHARS) -1)];
        return $output;
    }
    