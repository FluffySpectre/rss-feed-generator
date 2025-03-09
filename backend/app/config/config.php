<?php

// Load dotenv
require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

return [
    // Groq API configuration
    'groq_api_key' => $_ENV['GROQ_API_KEY'] ?? 'your-groq-api-key-here',
    'groq_model' => $_ENV['GROQ_MODEL'] ?? 'qwen-2.5-32b',
    
    // Application settings
    'app_url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
    'debug' => $_ENV['APP_DEBUG'] ?? true,
    
    // File paths
    'sites_directory' => dirname(__DIR__) . '/public/sites',
    
    // Cron settings
    'cron_enabled' => $_ENV['CRON_ENABLED'] ?? true,
    'cron_interval' => $_ENV['CRON_INTERVAL'] ?? 3600, // In seconds (default: 1 hour)
];
