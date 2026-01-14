<?php namespace Form;

    use Exception;
    use Event\EventEmitter;

    class FormBuilder {  
        public static function group(array $controls): AbstractControl {
            return new FormGroup($controls);
        }
        public static function array(array $controls): AbstractControl {
            return new FormArray($controls);
        }
        public static function control(mixed $value, array $validators = []): AbstractControl {
            return new FormControl($value, $validators);
        }
    }

    class FormError extends Exception { 
        public function __construct(string $message) { 
            $this->message = $message;
        }
    }

    interface ControlOptions { 
        public bool $dispatch { get; set; }
        public bool $validate { get; set; }
    }

    abstract class AbstractControl {        
        public static function options(?object $options = null): ControlOptions {
            if($options == null) $options = (object) [ ];
            if(!property_exists($options, "dispatch")) $options->dispatch = true;
            if(!property_exists($options, "validate")) $options->validate = true;
            return new class($options) implements ControlOptions {
                public bool $dispatch;
                public bool $validate;
                public function __construct($opts) {
                    $this->dispatch = is_bool($opts->dispatch) ? $opts->dispatch : true;
                    $this->validate = is_bool($opts->validate) ? $opts->validate : true;
                }
            };
        }

        private EventEmitter $events;
        protected array $validators;
        protected mixed $value;
        protected mixed $errors;

        abstract protected function validate(): void;
        abstract public function getValue(): mixed;
        abstract public function setValue(mixed $value, mixed $options): AbstractControl;
        abstract public function reset(mixed $options): void;

        public function __construct() {
            $this->events = new EventEmitter();
            $this->errors = null;
            $this->value = null;
        }

        protected function checkOptions(mixed $options) {
            if($options == null) return AbstractControl::options();
            if(is_array($options)) return AbstractControl::options((object) $options);
            if(is_object($options)) return AbstractControl::options($options);
            if(in_array("ControlOptions", class_implements($options))) return $options;
            throw new Exception("Unexpected control options");
        }

        protected function handleControlOptions(?ControlOptions $options = null): void {
            if($options == null) $options = AbstractControl::options();
            if($options->validate) $this->validate();
            if($options->dispatch) $this->events->emit("value-change", $this->value);
        }

        public function onValueChange(callable $callable): callable { 
            return $this->events->on("value-change", $callable);
        }

        public function setValidator(array $validators): AbstractControl { 
            $this->validators = $validators;
            $this->validate();
            return $this;
        }

        public function addValidator(callable $validator): AbstractControl {
            $this->validators[] = $validator;
            $this->validate();
            return $this;
        }

        public function removeValidator(int $index): AbstractControl {
            if($this->validators[$index] == null) return $this;
            array_splice($this->validators, 1, $index);
            $this->validate();
            return $this;
        }

        public function cleanValidator(): AbstractControl {
            $this->validators = [];
            $this->validate();
            return $this;
        }

        public function validators() {
            return $this->validators;
        }

        public function invalid(): bool {
            return $this->errors != null;
        }
        
        public function valid(): bool {
            return $this->errors == null;
        }

        public function getErrors(): ?array {
            return $this->errors;
        }
   
    }

    class FormControl extends AbstractControl {
        public function __construct(mixed $value, array $validators = []) {
            parent::__construct();
            $this->validators = $validators;
            $this->setValue($value, (object) ["validate" => false ]);
        }

        public function getValue(): mixed {
            return $this->value;
        }

        public function setValue(mixed $value, mixed $options = null): AbstractControl {
            $options = $this->checkOptions($options);
            $this->value = $value;
            $this->handleControlOptions($options);
            return $this;
        }

        public function reset(mixed $options = null): void {
            $options = $this->checkOptions($options);
            $this->value = null;
            $this->handleControlOptions($options);
        }

        protected function validate(): void {
            $this->errors = null; 
            foreach ($this->validators as $validator) {
                $message = $validator($this->value);
                if($message == null) continue;
                if($this->errors == null) $this->errors = []; 
                $this->errors[] = $message;
            }
        }
    }   
    
    class FormArray extends AbstractControl {
        private array $controls;

        public function __construct(array $controls = []) {
            parent::__construct();
            $this->controls = $controls;
            $this->setValue($this->getValue(), (object) [ "validate" => false ]);
        }

        public function setValue(mixed $value, mixed $options = null): AbstractControl {
            if(!is_array($value)) throw new FormError("Value should be an array");
            $options = $this->checkOptions($options);
            foreach ($this->controls as $index => $control) $control->setValue($value[$index], $options);
            $this->handleControlOptions($options);
            return $this;
        }

        public function getValue(): mixed {
            return array_map(fn($control) => $control->getValue());
        }

        public function getValueAt(int $index): mixed {
            if($this->controls[$index] == null) return null;
            return $this->controls[$index]->getValue();
        }

        public function reset(mixed $options = null): void {
            $options = $this->checkOptions($options);
            foreach ($this->controls as $control) $control->reset($options);
            $this->handleControlOptions($options);
        }

        protected function validate(): void { 
            $this->errors = null;
            foreach ($this->controls as $control) {
                if($control->valid()) continue;
                if($this->errors == null) $this->errors = [];
                $this->errors[] = $control->getErrors();
            }
        }

        public function getControls(): array {
            return $this->controls;
        }
        
        public function addControl(AbstractControl $control): AbstractControl {
            $this->controls[] = $control;
            $this->validate();
            return $this;
        }
        
        public function removeControl(int $index): AbstractControl { 
            if($this->controls[$index] != null) array_splice($this->controls, $index, 1);
            $this->validate();
            return $this;
        }
    }
    
    class FormGroup extends AbstractControl {
        private array $controls = [];

        public function __construct(array $controls = []) {
            parent::__construct();
            $this->controls = $controls;
            $this->setValue($this->getValue(), (object) ["validate" => false ]);
        }

        public function setValue(mixed $value, mixed $options = null): AbstractControl {

            if(is_array($value)) $value = (object) $value;
            if(!is_object($value)) throw new FormError("Value should be an object");
            $options = $this->checkOptions($options);
            foreach ($value as $field => $_value) {
                $control = $this->controls[$field];
                if($control == null) throw new FormError("Control doesn't exists");
                $control->setValue($_value, $options);
            }
            $this->handleControlOptions($options);
            return $this;
        }
        
        public function getValue(): mixed { 
            $array = [];
            foreach ($this->controls as $field => $control) 
                $array[$field] = $control->getValue();
            return (object) $array;
        }

        public function getValueOf(string $name): mixed {
            if($name == null || $this->controls[$name] == null) return null; 
            return $this->controls[$name]->getValue(); 
        }

        public function reset(mixed $options = null): void {
            $options = $this->checkOptions($options);
            foreach ($this->controls as $index => $control) $control->reset($options);
            $this->handleControlOptions($options);
        }
        
        protected function validate(): void { 
            $this->errors = null;
            foreach ($this->controls as $field => $control) {
                if($control->valid()) continue;
                if($this->errors == null) $this->errors = [];
                $this->errors[$field] = $control->getErrors();
            }

        }

        public function addControl(string $name, AbstractControl $control): AbstractControl {
            $this->controls[$name] = $control;
            $this->validate();
            return $this;
        }
        
        public function removeControl(string $name): AbstractControl { 
            if(array_key_exists($name, $this->controls)) unset($this->controls[$name]);
            $this->validate();
            return $this;
        }

        public function getControls(): object {
            return (object) $this->controls;
        }

        public function getControl(string $name): ?object {
            return $this->controls[$name];
        }
    }

