<?php
namespace App\Models;

class Website {
    private string $url;
    private string $domain;
    private string $folderPath;
    private ?string $lastParseDate = null;
    private ?string $lastParseStatus = null;

    public function __construct(string $url) {
        $this->url = $url;
        $this->domain = $this->extractDomain($url);
        $this->folderPath = dirname(__DIR__, 2) . '/public/sites/' . $this->domain;
    }

    private function extractDomain(string $url): string {
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['host'])) {
            throw new \InvalidArgumentException('Invalid URL provided');
        }
        return preg_replace('/^www\./', '', $parsedUrl['host']);
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function getDomain(): string {
        return $this->domain;
    }

    public function getFolderPath(): string {
        return $this->folderPath;
    }

    public function getParserPath(): string {
        return $this->folderPath . '/Parser.php';
    }

    public function getRssPath(): string {
        return $this->folderPath . '/rss.xml';
    }

    public function setLastParseDate(string $date): void {
        $this->lastParseDate = $date;
    }

    public function getLastParseDate(): ?string {
        return $this->lastParseDate;
    }

    public function setLastParseStatus(string $status): void {
        $this->lastParseStatus = $status;
    }

    public function getLastParseStatus(): ?string {
        return $this->lastParseStatus;
    }

    public function toArray(): array {
        return [
            'url' => $this->url,
            'domain' => $this->domain,
            'lastParseDate' => $this->lastParseDate,
            'lastParseStatus' => $this->lastParseStatus,
            'rssUrl' => '/sites/' . $this->domain . '/rss.xml'
        ];
    }
}
