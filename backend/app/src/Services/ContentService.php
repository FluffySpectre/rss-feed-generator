<?php
namespace App\Services;

use App\Models\Website;

class ContentService {
    /**
     * Download the content of a website
     * 
     * @param Website $website The website to download
     * @return string|null The website content or null on failure
     */
    public function downloadContent(Website $website): ?string {
        $url = $website->getUrl();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || $content === false) {
            return null;
        }
        
        return $content;
    }
    
    /**
     * Get a sample of the content for analysis
     * 
     * @param string $content The full website content
     * @param int $maxLength Maximum length of the sample
     * @return string The content sample
     */
    public function getContentSample(string $content, int $maxLength = 15000): string {
        if (strlen($content) <= $maxLength) {
            return $content;
        }
        
        // Try to get a meaningful portion of the page by finding the main content area
        $mainContentPatterns = [
            '/<main\b[^>]*>(.*?)<\/main>/is',
            '/<article\b[^>]*>(.*?)<\/article>/is',
            '/<div[^>]*class="[^"]*content[^"]*"[^>]*>(.*?)<\/div>/is',
            '/<div[^>]*id="[^"]*content[^"]*"[^>]*>(.*?)<\/div>/is',
            '/<div[^>]*class="[^"]*main[^"]*"[^>]*>(.*?)<\/div>/is',
            '/<div[^>]*id="[^"]*main[^"]*"[^>]*>(.*?)<\/div>/is',
        ];
        
        foreach ($mainContentPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $sample = $matches[1];
                if (strlen($sample) > 1000) {  // Ensure we got something substantial
                    return substr($sample, 0, $maxLength);
                }
            }
        }
        
        // If no main content area found, just take the first part of the body
        if (preg_match('/<body\b[^>]*>(.*)/is', $content, $matches)) {
            return substr($matches[1], 0, $maxLength);
        }
        
        // Fallback to just taking the first chunk
        return substr($content, 0, $maxLength);
    }
}
