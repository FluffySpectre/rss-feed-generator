<?php
namespace App\Controllers;

use App\Models\Website;
use App\Services\WebsiteService;
use App\Services\ContentService;
use App\Services\ParserService;
use App\Services\RssService;

class MainController {
    private WebsiteService $websiteService;
    private ContentService $contentService;
    private ParserService $parserService;
    private RssService $rssService;
    
    public function __construct() {
        $this->websiteService = new WebsiteService();
        $this->contentService = new ContentService();
        $this->parserService = new ParserService($this->contentService);
        $this->rssService = new RssService();
    }
    
    /**
     * Register a new website
     * 
     * @param string $url The website URL
     * @return array Response data
     */
    public function registerWebsite(string $url): array {
        try {
            $website = $this->websiteService->registerWebsite($url);
            
            return [
                'success' => true,
                'message' => 'Website registered successfully',
                'data' => $website->toArray()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to register website: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all registered websites
     * 
     * @return array Response data
     */
    public function getAllWebsites(): array {
        try {
            $websites = $this->websiteService->getAllWebsites();
            $data = [];
            
            foreach ($websites as $website) {
                $data[] = $website->toArray();
            }
            
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get websites: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process a website and generate RSS feed
     * 
     * @param string $domain The website domain
     * @param bool $forceRegeneration Whether to force parser regeneration
     * @return array Response data
     */
    public function processWebsite(string $domain, bool $forceRegeneration = false): array {
        try {
            // Get the website
            $website = $this->websiteService->getWebsiteByDomain($domain);
            
            if (!$website) {
                return [
                    'success' => false,
                    'message' => 'Website not found'
                ];
            }
            
            // Download the content
            $content = $this->contentService->downloadContent($website);
            
            if (!$content) {
                $this->websiteService->updateWebsiteStatus($website, 'error_download');
                return [
                    'success' => false,
                    'message' => 'Failed to download website content'
                ];
            }
            
            // Generate parser
            try {
                $parserGenerated = $this->parserService->generateParser($website, $content, $forceRegeneration);
            } catch (\Throwable $e) {
                $this->websiteService->updateWebsiteStatus($website, 'error_parser_generation');
                return [
                    'success' => false,
                    'message' => 'Failed to generate parser: ' . $e->getMessage()
                ];
            }
            // $parserGenerated = $this->parserService->generateParser($website, $content, $forceRegeneration);
            
            // if (!$parserGenerated) {
            //     $this->websiteService->updateWebsiteStatus($website, 'error_parser_generation');
            //     return [
            //         'success' => false,
            //         'message' => 'Failed to generate parser'
            //     ];
            // }
            
            // Parse content
            $articles = $this->parserService->parseContent($website, $content);
            
            if ($articles === null) {
                // Try to regenerate the parser
                $parserRegenerated = $this->parserService->generateParser($website, $content, true);
                
                if (!$parserRegenerated) {
                    $this->websiteService->updateWebsiteStatus($website, 'error_parsing');
                    return [
                        'success' => false,
                        'message' => 'Failed to parse content and regenerate parser'
                    ];
                }
                
                // Try parsing again
                $articles = $this->parserService->parseContent($website, $content);
                
                if ($articles === null) {
                    $this->websiteService->updateWebsiteStatus($website, 'error_parsing_final');
                    return [
                        'success' => false,
                        'message' => 'Failed to parse content after parser regeneration'
                    ];
                }
            }
            
            // Generate RSS feed
            $rssGenerated = $this->rssService->generateRssFeed($website, $articles);
            
            if (!$rssGenerated) {
                $this->websiteService->updateWebsiteStatus($website, 'error_rss_generation');
                return [
                    'success' => false,
                    'message' => 'Failed to generate RSS feed'
                ];
            }
            
            // Update website status
            $this->websiteService->updateWebsiteStatus($website, 'success');
            
            return [
                'success' => true,
                'message' => 'Website processed successfully',
                'data' => [
                    'website' => $website->toArray(),
                    'articlesCount' => count($articles)
                ]
            ];
        } catch (\Exception $e) {
            if (isset($website)) {
                $this->websiteService->updateWebsiteStatus($website, 'error_exception');
            }
            
            return [
                'success' => false,
                'message' => 'Error processing website: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process all registered websites
     * 
     * @return array Response data
     */
    public function processAllWebsites(): array {
        try {
            $websites = $this->websiteService->getAllWebsites();
            $results = [];
            
            foreach ($websites as $website) {
                $result = $this->processWebsite($website->getDomain());
                $results[$website->getDomain()] = $result;
            }
            
            return [
                'success' => true,
                'data' => $results
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to process websites: ' . $e->getMessage()
            ];
        }
    }
}
