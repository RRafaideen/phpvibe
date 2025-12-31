<?php namespace Main\UI;

    use Text\Html;

    class FormControl { 
        public string $label;
        public array $attributes;
        public array $messages;

        public function __construct(string $label, ?string $name = null, string $type = "text",  array $attributes = []) {
            $attributes["name"] = $name;
            $attributes["type"] = $type;
            $this->label = $label;
            $this->attributes = $attributes;
            $this->messages = [];
        }

        public function type() {
            return $this->attributes["type"];
        }

        public function name() {
            return $this->attributes["name"];
        }

        public static function render(FormControl $control) {
            $attributes = Html::renderAttributes($control->attributes);
            $messages = array_map(fn($x) => self::renderMessage($x), $control->messages);
            $messages = join("", $messages);
            return  <<<HTML
                    <div class="form-control">
                        <label class="form-control-label">
                           <span>{$control->label}</span>
                           <input {$attributes} />
                        </label>
                        <div class="form-control-messages">{$messages}</div>
                    </div>
                HTML;

        }

        private static function renderMessage(Message $message) { 
            return "<div class=\"form-control-message message-{$message->type}\">{$message->content}</div>";
        }

    }