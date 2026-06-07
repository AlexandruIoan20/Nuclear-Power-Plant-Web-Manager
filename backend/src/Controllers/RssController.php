<?php

require_once __DIR__ . '/../Services/RssService.php';

class RssController {
    public function __construct(
        private RssService $rssService
    ) {}

    public function handleGetPlantsFeed() {
      
        header('Content-Type: text/xml; charset=UTF-8');
        http_response_code(200);

        try {
            $rssXml = $this->rssService->generatePlantsRssFeed();
            echo $rssXml;
        } catch (Exception $e) {
           
            echo '<?xml version="1.0" encoding="UTF-8" ?>';
            echo '<rss version="2.0"><channel><title>Error</title><description>' . htmlspecialchars($e->getMessage()) . '</description></channel></rss>';
        }
        exit;
    }
}