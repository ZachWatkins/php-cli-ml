<?php

use Laravel\Prompts\Prompt;
use function Laravel\Prompts\{text, select};

require 'vendor/autoload.php';

Prompt::fallbackWhen(true);

$name = text('What is your name?');

$activity = select(
    label: "Hello, $name! What would you like to do?",
    options: ['Create a new project', 'Go on an adventure', 'Hear interesting facts about Texas'],
    default: 'Create a new project',
    hint: 'You can exit at any time by pressing Ctrl+C'
);

echo $name . ' wants to ' . strtolower($activity) . '.' . PHP_EOL;
