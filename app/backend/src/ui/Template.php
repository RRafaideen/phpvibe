<?php namespace Main\UI;

    class Template {
        public static function render(object $scheme): string {
            $title = $scheme->title ? "<title>{$scheme->title}</title>" : "";
            $body = $scheme->body ?? ""; 
    
            return <<<TPL
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    {$title}
                </head>
                <body>{$body}</body>
                </html>
            TPL;
        }
    }
