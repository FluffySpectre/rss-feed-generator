<?php
namespace App\Services;

use App\Models\Website;
use App\Services\Parsers\ParserInterface;

class ParserService {
    private string $groqApiKey;
    private string $groqModel;
    private ContentService $contentService;
    
    public function __construct(ContentService $contentService) {
        $config = require dirname(__DIR__, 2) . '/config/config.php';
        $this->groqApiKey = $config['groq_api_key'];
        $this->groqModel = $config['groq_model'] ?? 'qwen-2.5-32b';
        $this->contentService = $contentService;
    }
    
    /**
     * Generate a parser for a website based on its content
     * 
     * @param Website $website The website
     * @param string $content The website content
     * @param bool $forcedRegeneration Whether to force regeneration of the parser
     * @return bool Whether the parser was generated successfully
     */
    public function generateParser(Website $website, string $content, bool $forcedRegeneration = false): bool {
        $parserPath = $website->getParserPath();
        
        // Check if parser already exists and we're not forcing regeneration
        if (!$forcedRegeneration && file_exists($parserPath)) {
            return true;
        }
        
        // Get a sample of the content to analyze
        $contentSample = $this->contentService->getContentSample($content);

        // Generate the parser code using the LLM
        $parserCode = $this->generateParserCode($website, $contentSample);
        
        if (empty($parserCode)) {
            return false;
        }
        
        // Save the parser
        file_put_contents($parserPath, $parserCode);
        
        // Validate the parser
        return $this->validateParser($website);
    }
    
    /**
     * Generate the parser code using the Groq API
     * 
     * @param Website $website The website
     * @param string $contentSample Sample of the website content
     * @return string|null The generated parser code or null on failure
     */
    private function generateParserCode(Website $website, string $contentSample): ?string {
        $prompt = $this->buildParserPrompt($website, $contentSample);

        // Call the Groq API
        $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
        
        $data = [
            'model' => $this->groqModel,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert PHP developer specializing in web scraping and parsing HTML content. Your task is to create a PHP parser class that can extract news articles from a given website. Focus only on generating clean, functional PHP code without additional explanations.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.2,
            'max_tokens' => 4000
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->groqApiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // throw new \Exception('Parser response ' . $response);

        if ($httpCode !== 200 || $response === false) {
            return null;
        }
        
        $responseData = json_decode($response, true);
        
        if (!isset($responseData['choices'][0]['message']['content'])) {
            return null;
        }
        
        $generatedContent = $responseData['choices'][0]['message']['content'];
        
        // Extract only the code part if there are additional explanations
        if (preg_match('/```php(.*?)```/s', $generatedContent, $matches)) {
            return trim($matches[1]);
        }
        
        // Wrap the code in PHP tags if not present
        if (strpos($generatedContent, '<?php') !== 0) {
            $generatedContent = "<?php\n" . $generatedContent;
        }
        
        return $generatedContent;
    }
    
    /**
     * Build the prompt for generating the parser
     * 
     * @param Website $website The website
     * @param string $contentSample Sample of the website content
     * @return string The generated prompt
     */
    private function buildParserPrompt(Website $website, string $contentSample): string {
        $baseUrl = $website->getUrl();
        $domain = $website->getDomain();

        // Make domain name "Namespace friendly"
        $domain = str_replace(['.', '-'], '', $domain);
        
        return <<<EOT
Create a PHP Parser class for the website {$baseUrl} that extends the BaseParser class and implements the App\Services\Parsers\ParserInterface.

The Parser class should:
1. Be named "Parser" and saved in the namespace "App\\Sites\\{$domain}"
2. Extend the App\Services\Parsers\BaseParser class
3. Implement the parse method that takes website content as a string and returns an array of news articles
4. Extract ONLY today's news articles from the content
5. Use DOMDocument for HTML parsing
6. For each article, extract:
   - title
   - link (as absolute URL)
   - publication date (if available)
   - author (if available)
   - description or excerpt
7. Return the articles in the following format:
   [
     [
       'title' => 'Article title',
       'link' => 'https://full-article-url.com/article',
       'pubDate' => 'Publication date string',
       'author' => 'Author name',
       'description' => 'Article excerpt or description'
     ],
     // More articles...
   ]

Here's a sample of the website content to analyze:

```html
{$contentSample}
```

Return ONLY the PHP code without explanations. Make sure the code handles edge cases and validates the extracted data.
EOT;
    }
    
    /**
     * Validate that the parser is working correctly
     * 
     * @param Website $website The website
     * @return bool Whether the parser is valid
     */
    private function validateParser(Website $website): bool {
        $parserPath = $website->getParserPath();
        
        if (!file_exists($parserPath)) {
            return false;
        }
        
        // Try to include the parser
        try {
            // Use a temporary class name to avoid conflicts
            $tempContent = file_get_contents($parserPath);
            $tempContent = preg_replace('/namespace\s+([^;]+);/', 'namespace $1_Temp;', $tempContent);
            $tempContent = preg_replace('/class\s+Parser\b/', 'class ParserTemp', $tempContent);
            
            // Create a temporary file
            $tempFile = $website->getFolderPath() . '/ParserTemp.php';
            file_put_contents($tempFile, $tempContent);
            
            // Include the file
            require_once $tempFile;
            
            // Clean up
            unlink($tempFile);
            
            return true;
        } catch (\Throwable $e) {
            // If there was an error, the parser is invalid
            return false;
        }
    }
    
    /**
     * Parse website content using the generated parser
     * 
     * @param Website $website The website
     * @param string $content The website content
     * @return array|null The parsed articles or null on failure
     */
    public function parseContent(Website $website, string $content): ?array {
        $parserPath = $website->getParserPath();
        
        if (!file_exists($parserPath)) {
            return null;
        }
        
        // Include the parser
        require_once $parserPath;
        
        // Make domain name "Namespace friendly"
        $domain = str_replace(['.', '-'], '', $website->getDomain());

        $className = 'App\\Sites\\' . $domain . '\\Parser';
        
        if (!class_exists($className)) {
            return null;
        }
        
        try {
            $parser = new $className();
            return $parser->parse($content);
        } catch (\Throwable $e) {
            // If there was an error parsing, return null
            return null;
        }
    }
}
