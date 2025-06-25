<?php
require_once '../vendor/autoload.php';

use YourNamespace\Bot\Bot;

// Initialize the Bot
$bot = new Bot();

// Handle incoming requests
$update = $bot->getUpdate();
$bot->handleUpdate($update);
?>