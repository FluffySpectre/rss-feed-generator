<?php
namespace App\Services;

use App\Models\Website;

class RssService {
    /**
     * Generate an RSS feed for a website based on parsed articles
     * 
     * @param Website $website The website
     * @param array $articles The parsed articles
     * @return bool Whether the RSS feed was generated successfully
     */
    public function generateRssFeed(Website $website, array $articles): bool {
        $rssPath = $website->getRssPath();
        $url = $website->getUrl();
        $domain = $website->getDomain();
        
        // Create the RSS XML
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"></rss>');
        
        // Add channel information
        $channel = $xml->addChild('channel');
        $channel->addChild('title', htmlspecialchars("{$domain} News Feed"));
        $channel->addChild('link', htmlspecialchars($url));
        $channel->addChild('description', htmlspecialchars("Latest news from {$domain}"));
        $channel->addChild('language', 'en-us');
        $channel->addChild('pubDate', date(DATE_RFC2822));
        $channel->addChild('lastBuildDate', date(DATE_RFC2822));
        $channel->addChild('generator', 'AI-Powered RSS Feed Generator');
        
        // Add items
        foreach ($articles as $article) {
            $item = $channel->addChild('item');
            
            // Required elements
            $item->addChild('title', htmlspecialchars($article['title'] ?? 'No title'));
            $item->addChild('link', htmlspecialchars($article['link'] ?? $url));
            $item->addChild('description', htmlspecialchars($article['description'] ?? 'No description'));
            
            // Optional elements
            if (isset($article['pubDate']) && !empty($article['pubDate'])) {
                try {
                    $date = new \DateTime($article['pubDate']);
                    $item->addChild('pubDate', $date->format(DATE_RFC2822));
                } catch (\Exception $e) {
                    $item->addChild('pubDate', date(DATE_RFC2822));
                }
            } else {
                $item->addChild('pubDate', date(DATE_RFC2822));
            }
            
            if (isset($article['author']) && !empty($article['author'])) {
                $item->addChild('author', htmlspecialchars($article['author']));
            }
            
            // Generate a unique GUID
            $guid = $item->addChild('guid', htmlspecialchars($article['link'] ?? uniqid('article-')));
            $guid->addAttribute('isPermaLink', isset($article['link']) ? 'true' : 'false');
        }
        
        // Save the RSS file
        return $xml->asXML($rssPath) !== false;
    }
    
    /**
     * Check if an RSS feed exists and is valid
     * 
     * @param Website $website The website
     * @return bool Whether the RSS feed exists and is valid
     */
    public function isRssFeedValid(Website $website): bool {
        $rssPath = $website->getRssPath();
        
        if (!file_exists($rssPath)) {
            return false;
        }
        
        // Try to parse the RSS file
        try {
            $xml = simplexml_load_file($rssPath);
            return $xml !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
