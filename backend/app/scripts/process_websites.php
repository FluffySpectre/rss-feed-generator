<?php

// Set script execution time limit (0 = no limit)
set_time_limit(0);

// Include autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Controllers\MainController;

// Create controller
$controller = new MainController();

// Process command line arguments
$options = getopt('d:', ['domain:']);
$domain = $options['d'] ?? $options['domain'] ?? null;

// Initialize logger
$logFile = dirname(__DIR__) . '/logs/cron_' . date('Y-m-d') . '.log';
$logDir = dirname($logFile);

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(
        $logFile, 
        "[{$timestamp}] {$message}" . PHP_EOL, 
        FILE_APPEND
    );
}

// Log start of execution
logMessage("Starting website processing job");

try {
    if ($domain) {
        // Process specific website
        logMessage("Processing specific website: {$domain}");
        $result = $controller->processWebsite($domain);
        
        if ($result['success']) {
            logMessage("Successfully processed website: {$domain}");
            if (isset($result['data']['articlesCount'])) {
                logMessage("Found {$result['data']['articlesCount']} articles");
            }
        } else {
            logMessage("Failed to process website: {$domain}. Error: {$result['message']}");
        }
    } else {
        // Process all websites
        logMessage("Processing all websites");
        $result = $controller->processAllWebsites();
        
        if ($result['success']) {
            logMessage("Successfully processed all websites");
            
            // Log individual website results
            foreach ($result['data'] as $domain => $websiteResult) {
                $status = $websiteResult['success'] ? 'Success' : 'Failed';
                $message = $websiteResult['message'] ?? '';
                $articlesCount = $websiteResult['data']['articlesCount'] ?? 0;
                
                logMessage("{$status}: {$domain} - {$message} - Articles: {$articlesCount}");
            }
        } else {
            logMessage("Failed to process all websites. Error: {$result['message']}");
        }
    }
} catch (Exception $e) {
    logMessage("Exception occurred: " . $e->getMessage());
}

// Log end of execution
logMessage("Finished website processing job");

// Exit with success code
exit(0);
