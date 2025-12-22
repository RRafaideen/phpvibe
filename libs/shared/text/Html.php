<?php namespace Text;

    class Html { 
        public static function renderAttributes(array $attributes): string {
            $output = [];
            foreach ($attributes as $name => $value) {
                $type = gettype($value);
                if(in_array($type, ["array", "object", "resource", "resource" , "NULL", "unknown type"])) continue;
                elseif($value == false) continue;
                if($value === true) $value = "true";
                $output[] = "{$name}=\"{$value}\"";
            }
            return join(" ", $output);
        }
    }