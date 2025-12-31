<?php namespace Main\UI;

    enum MessageType : string {
        case Info = "info";
        case Error = "error";
        case Warn = "warn";
    }

    class Message {

        public static function renderInline(Message $message): string { 
            $type = strtoupper($message->type);
            return <<<HTML
                <div class="message-inline message-{$message->type}">
                    <span>{$type}</span><span>{$message->content}</span>
                </div>
                HTML;
        }
        
        public static function renderBox(Message $message): string { 
            $type = strtoupper($message->type);
            return <<<HTML
                <div class="message-box message-{$message->type}">
                    <div>{$type}</div>
                    <div>{$message->content}</div>
                </div>
                HTML;
        }

        public MessageType $type;
        public string $content;
        public function __construct(MessageType $type, string $content) {
            $this->type = $type;
            $this->content = $content;
        } 
    }

