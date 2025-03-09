<?php
namespace App\Services\Parsers;

/**
 * Base class for website parsers
 */
abstract class BaseParser implements ParserInterface {
    /**
     * Check if a date matches today's date
     * 
     * @param string $date The date string to check
     * @return bool Whether the date is today
     */
    protected function isToday(string $date): bool {
        $articleDate = strtotime($date);
        if ($articleDate === false) {
            return false;
        }
        
        $today = strtotime('today');
        return date('Y-m-d', $articleDate) === date('Y-m-d', $today);
    }
    
    /**
     * Convert a relative URL to an absolute URL
     * 
     * @param string $relativeUrl The relative URL
     * @param string $baseUrl The base URL of the website
     * @return string The absolute URL
     */
    protected function makeAbsoluteUrl(string $relativeUrl, string $baseUrl): string {
        if (strpos($relativeUrl, 'http') === 0) {
            return $relativeUrl;
        }
        
        $parsedBase = parse_url($baseUrl);
        $base = $parsedBase['scheme'] . '://' . $parsedBase['host'];
        
        if (strpos($relativeUrl, '/') === 0) {
            return $base . $relativeUrl;
        }
        
        $path = isset($parsedBase['path']) ? $parsedBase['path'] : '/';
        $path = rtrim(dirname($path), '/') . '/';
        
        return $base . $path . $relativeUrl;
    }
    
    /**
     * Extract text content from an HTML element, removing unnecessary whitespace
     * 
     * @param \DOMElement $element The DOM element to extract text from
     * @return string The cleaned text content
     */
    protected function extractText(\DOMElement $element): string {
        $text = $element->textContent;
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
