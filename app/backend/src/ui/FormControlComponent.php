<?php namespace Main\UI;

    use Text\Html;

    class FormControlComponent { 
        private array $attributes;
        private ?array $errors;
        private ?string $label;

        public function __construct(?string $label = null) {
            $this->label = $label;
            $this->attributes = [];
            $this->errors = null;
        }

        public function label(string $label): FormControlComponent { 
            $this->label = $label;
            return $this;
        }

        public function name(string $name): FormControlComponent { 
            $this->attributes["name"] = $name;
            return $this;
        }

        public function type(string $type): FormControlComponent { 
            $this->attributes["type"] = $type;
            return $this;
        }

        public function value(mixed $value): FormControlComponent { 
            $this->attributes["value"] = $value;
            return $this;
        }

        public function errors(?array $errors): FormControlComponent {
            if($errors == null) return $this;
            if($this->errors == null) $this->errors = [];
            array_push($this->errors, ...$errors);
            return $this;
        }

        public function reset(): FormControlComponent { 
            $this->errors = null;
            unset($this->attributes["value"]);
            return $this;
        }

        public function attribute(string $name, mixed $value): FormControlComponent {
            $this->attributes[$name] = "{$value}";
            return $this;
        }

        public function render(): string { 
            $label = $this->label == null ? "" : "<span  class=\"form-control-label\">{$this->label}</span>";
            $attributes = Html::renderAttributes($this->attributes);
            $input = "<input {$attributes} />";
            $messages = join("", array_map(fn($x) => "<span class=\"form-control-error\">{$x}</span>", $this->errors ?? []));
            $errors = $this->errors == null ? "" : "<div class=\"form-control-errors\">{$messages}</div>";
            return  <<<HTML
                    <div class="form-control">
                        <label>
                           {$label}
                           {$input}
                        </label>
                        {$errors}
                    </div> 
                HTML;
        }
    }
