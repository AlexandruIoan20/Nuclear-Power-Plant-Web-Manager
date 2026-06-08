<?php

class RssService {
    public function __construct(
        private PlantServiceFacade $plantServiceFacade
    ) {}

    public function generatePlantsRssFeed(): string {
      
        $plants = $this->plantServiceFacade->getAllPowerPlants();

   
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        
    
        $xml .= '<title>Nuclear Power Plants Registry</title>';
        $xml .= '<link>http://localhost:8081/api/rss/power-plants</link>';
        $xml .= '<description>Latest nuclear power plants added to the global registry system.</description>';
        $xml .= '<language>en-us</language>';
        $xml .= '<lastBuildDate>' . date(DATE_RSS) . '</lastBuildDate>';

        
        foreach ($plants as $plant) {
            
            $name = htmlspecialchars($plant['name'] ?: 'Unnamed Plant');
            $country = htmlspecialchars($plant['country'] ?: 'Unknown Country');
            $status = htmlspecialchars($plant['status'] ?? 'DRAFT');
            $id = $plant['id'];
            
            $xml .= '<item>';
            $xml .= '<title>' . $name . ' (' . $country . ')</title>';
         
            $xml .= '<link>http://localhost:5500/pages/power-plants/details.html?id=' . urlencode($id) . '</link>';
            $xml .= '<guid isPermaLink="false">' . $id . '</guid>';
            $xml .= '<description><![CDATA[';
            $xml .= 'A nuclear power plant has been updated or registered.<br/>';
            $xml .= '<strong>Name:</strong> ' . $name .   '<br/>';
            $xml .= '<strong> Country:</strong> ' . $country .  '<br/>';
            $xml .= '<strong> System Status:</strong> ' . $status ;
            $xml .= ']]></description>';
            $xml .= '<pubDate>' . date(DATE_RSS) . '</pubDate>'; 
            $xml .= '</item>';
        }

        $xml .= '</channel>';
        $xml .= '</rss>';

        return $xml;
    }
}