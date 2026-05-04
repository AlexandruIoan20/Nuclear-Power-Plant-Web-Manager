<?php

enum ReactorType: string {
    case PWR = 'PWR';
    case BWR = 'BWR';
    case PHWR = 'PHWR';
    case FBR = 'FBR';

    public function label(): string {
        return match($this) {
            self::PWR => 'Pressurized Water Reactor',
            self::BWR => 'Boiling Water Reactor',
            self::PHWR => 'Pressurized Heavy Water Reactor',
            self::FBR => 'Fast Breeder Reactor',
        };
    }
}