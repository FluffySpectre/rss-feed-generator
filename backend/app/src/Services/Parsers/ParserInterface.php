<?php
namespace App\Services\Parsers;

/**
 * Interface for website content parsers
 */
interface ParserInterface {
    /**
     * Parse the website content and extract news articles
     * 
     * @param string $content The HTML content of the website
     * @return array The parsed news articles
     */
    public function parse(string $content): array;
}
