<?php namespace Feature\Auth\Models;    
    
    class Address {
        public int $aid;
        public ?string $street;
        public ?string $postcode;
        public ?string $city;
    }
