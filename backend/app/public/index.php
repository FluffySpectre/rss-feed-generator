<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Controllers\MainController;

// Set headers for CORS and JSON responses
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle OPTIONS requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Create controller
$controller = new MainController();

// Parse the request
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = ltrim($path, '/');
$pathParts = explode('/', $path);

// Extract API endpoint
$endpoint = $pathParts[0] ?? '';

// Handle request based on endpoint
switch ($endpoint) {
    case 'api':
        $action = $pathParts[1] ?? '';
        handleApiRequest($controller, $action);
        break;
    
    case 'sites':
        // Serve RSS files directly
        $domain = $pathParts[1] ?? '';
        $file = $pathParts[2] ?? '';
        
        if ($domain && $file === 'rss.xml') {
            $rssPath = dirname(__DIR__) . '/public/sites/' . $domain . '/rss.xml';
            
            if (file_exists($rssPath)) {
                header('Content-Type: application/rss+xml');
                readfile($rssPath);
                exit;
            }
        }
        
        // 404 if RSS file not found
        http_response_code(404);
        echo json_encode(['error' => 'RSS feed not found']);
        break;
    
    default:
        // 404 for unknown endpoints
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}

/**
 * Handle API requests
 * 
 * @param MainController $controller The controller
 * @param string $action The action to perform
 * @return void
 */
function handleApiRequest(MainController $controller, string $action): void {
    switch ($action) {
        case 'register':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $url = $data['url'] ?? '';
            
            if (empty($url)) {
                http_response_code(400);
                echo json_encode(['error' => 'URL is required']);
                break;
            }
            
            $result = $controller->registerWebsite($url);
            echo json_encode($result);
            break;
        
        case 'websites':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;
            }
            
            $result = $controller->getAllWebsites();
            echo json_encode($result);
            break;
        
        case 'process':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $domain = $data['domain'] ?? '';
            $forceRegeneration = $data['forceRegeneration'] ?? false;
            
            if (empty($domain)) {
                http_response_code(400);
                echo json_encode(['error' => 'Domain is required']);
                break;
            }
            
            $result = $controller->processWebsite($domain, $forceRegeneration);
            echo json_encode($result);
            break;
        
        case 'process-all':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;
            }
            
            $result = $controller->processAllWebsites();
            echo json_encode($result);
            break;
        
        default:
            // 404 for unknown actions
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
            break;
    }
}
