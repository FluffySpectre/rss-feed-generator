<?php
namespace App\Services;

use App\Models\Website;
use App\Utils\FileUtils;

class WebsiteService {
    private string $sitesDirectory;
    private string $websitesFile;
    
    public function __construct() {
        $this->sitesDirectory = dirname(__DIR__, 2) . '/public/sites';
        $this->websitesFile = $this->sitesDirectory . '/websites.json';
        
        // Ensure the sites directory exists
        if (!is_dir($this->sitesDirectory)) {
            mkdir($this->sitesDirectory, 0755, true);
        }
        
        // Create websites file if it doesn't exist
        if (!file_exists($this->websitesFile)) {
            file_put_contents($this->websitesFile, json_encode([]));
        }
    }
    
    /**
     * Register a new website
     * 
     * @param string $url The website URL
     * @return Website The registered website
     */
    public function registerWebsite(string $url): Website {
        $website = new Website($url);
        $domain = $website->getDomain();
        
        // Check if website already exists
        $websites = $this->getAllWebsites();
        foreach ($websites as $existingWebsite) {
            if ($existingWebsite->getDomain() === $domain) {
                return $existingWebsite;
            }
        }
        
        // Create domain directory
        $domainDir = $website->getFolderPath();
        if (!is_dir($domainDir)) {
            mkdir($domainDir, 0755, true);
        }
        
        // Save website to list
        $websites[] = $website;
        $this->saveWebsites($websites);
        
        return $website;
    }
    
    /**
     * Get all registered websites
     * 
     * @return array Array of Website objects
     */
    public function getAllWebsites(): array {
        $data = json_decode(file_get_contents($this->websitesFile), true);
        $websites = [];
        
        foreach ($data as $websiteData) {
            $website = new Website($websiteData['url']);
            if (isset($websiteData['lastParseDate'])) {
                $website->setLastParseDate($websiteData['lastParseDate']);
            }
            if (isset($websiteData['lastParseStatus'])) {
                $website->setLastParseStatus($websiteData['lastParseStatus']);
            }
            $websites[] = $website;
        }
        
        return $websites;
    }
    
    /**
     * Get a website by domain
     * 
     * @param string $domain The domain to find
     * @return Website|null The website or null if not found
     */
    public function getWebsiteByDomain(string $domain): ?Website {
        $websites = $this->getAllWebsites();
        
        foreach ($websites as $website) {
            if ($website->getDomain() === $domain) {
                return $website;
            }
        }
        
        return null;
    }
    
    /**
     * Save the list of websites
     * 
     * @param array $websites Array of Website objects
     * @return void
     */
    private function saveWebsites(array $websites): void {
        $data = [];
        
        foreach ($websites as $website) {
            $data[] = $website->toArray();
        }
        
        file_put_contents($this->websitesFile, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    /**
     * Update a website's parse status
     * 
     * @param Website $website The website to update
     * @param string $status The parse status
     * @return void
     */
    public function updateWebsiteStatus(Website $website, string $status): void {
        $website->setLastParseDate(date('Y-m-d H:i:s'));
        $website->setLastParseStatus($status);
        
        $websites = $this->getAllWebsites();
        
        foreach ($websites as $key => $existingWebsite) {
            if ($existingWebsite->getDomain() === $website->getDomain()) {
                $websites[$key] = $website;
                break;
            }
        }
        
        $this->saveWebsites($websites);
    }
}
