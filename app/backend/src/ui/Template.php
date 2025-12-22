<?php namespace Main\UI;
    use Text\Html;

    class Script { 
        public string $src;
        public ?string $type;
        public ?bool $defer;
        public ?bool $async;

        public function __construct(string $src, ?string $type, ?bool $defer, ?bool $async) {
            $this->src = $src;
            $this->type = $type;
            $this->defer = $defer;
            $this->async = $async;
        }

        public static function render(Style $style): string {
            $attributes = Html::renderAttributes([ "src" => $src, "type" => $type, "defer" => $defer, "async" => $async ]);
            return "<script {$attributes}></script>";
        }    
    }
    
    class Style {
        public string $href;
        public function __construct(string $href) {
            $this->href = $href;
        }
        public static function render(Style $style): string { 
            return "<link rel=\"stylesheet\" href=\"{$style->href}\">";
        }
    }


    class Template {
        public static $global = [ "styles" => [], "scripts" => [] ];

        public static function render(object $scheme): string {
            $title = $scheme->title ? "<title>{$scheme->title}</title>" : "";
            $scripts = array_merge($scheme->scripts ?? [], self::$global["scripts"]);
            $scripts = array_map(fn($x) => Script::render($x), $scripts);
            $scripts = join("", $scripts);
            
            $styles =  array_merge($scheme->styles ?? [], self::$global["styles"]);
            $styles = array_map(fn($x) => Style::render($x), $styles);
            $styles = join("", $styles);
            $body = $scheme->body ?? ""; // <- Todo purify

            return <<<TPL
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    {$title}
                    {$styles}
                    {$scripts}
                </head>
                <body>{$body}</body>
                </html>
            TPL;
        }
    }
