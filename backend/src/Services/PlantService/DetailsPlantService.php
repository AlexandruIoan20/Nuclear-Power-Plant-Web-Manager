<?php

require_once __DIR__ . '/../../Helpers/generateUUID.php'; 
require_once __DIR__ . '/../../Entities/PlantStatus.php'; 
require_once __DIR__ . '/../../Entities/Plant.php'; 
require_once __DIR__ . '/../../Entities/GeologicalPlantData.php';
require_once __DIR__ . '/../../Entities/SoilType.php';
require_once __DIR__ . '/../../Entities/WaterSourceType.php';   

require_once __DIR__ . '/../../Dto/CreateDataResponseDTO.php'; 



class DetailsPlantService { 
    private PlantRepositoryFacade $plantRepositoryFacade; 

    public function __construct(PlantRepositoryFacade $plantRepositoryFacade) { 
        $this->plantRepositoryFacade = $plantRepositoryFacade; 
    }

    public function savePlantDetails(array $data): CreateDataResponseDTO { 
        $name = $data['name'] ?? ''; 
        $name = ($name !== '') ? $name : null; 

        $country = $data['country'] ?? ''; 
        $country = ($country !== '') ? $country : null; 

        $latitude = $data['latitude'] ?? '';
        $latitude = ($latitude !== '') ? (float) $latitude : null;

        $longitude = $data['longitude'] ?? '';
        $longitude = ($longitude !== '') ? (float) $longitude : null;

        $status = PlantStatus::DRAFT; 
        $id = generateUUID(); 


        $plant = new Plant($country, $id, $name, $status, $latitude, $longitude); 
        $this->plantRepositoryFacade->savePlantDetails($plant); 

    
        error_log("[DEBUG GEO] Încercare salvare date geologice pentru ID: {$id}");
        error_log("[DEBUG GEO] Coordonate primite: Lat: " . ($latitude ?? 'NULL') . ", Lon: " . ($longitude ?? 'NULL'));

       
        if ($latitude !== null && $longitude !== null) {
            $latNorm = round($latitude, 6);
            $lonNorm = round($longitude, 6);

            $soilTypeEnum = null;
            $waterSourceTypeEnum = WaterSourceType::FRESH_WATER; 
            $seismicStability = null;
            $floodRisk = null;
            $groundwaterLevel = null;
            $waterProximity = null;
            $waterFlowRate = null;
            $populationDensity = null;
            $transportScore = null;

            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => "Accept: application/json\r\nUser-Agent: NuclearProjectBackend/1.0\r\n",
                    'ignore_errors' => true,
                    'timeout' => 3
                ]
            ];
            $context = stream_context_create($opts);

       
            try {
                $geoQuery = http_build_query(['latitude' => $latNorm, 'longitude' => $lonNorm, 'localityLanguage' => 'ro']);
                $geoUrl = "https://api.bigdatacloud.net/data/reverse-geocode-client?{$geoQuery}";
                $geoResp = file_get_contents($geoUrl, false, $context);
                
                error_log("[DEBUG GEO] Răspuns BigDataCloud: " . ($geoResp ? substr($geoResp, 0, 100) . '...' : 'FĂRĂ RĂSPUNS'));

                if ($geoResp !== false) {
                    $geoData = json_decode($geoResp, true);
                    if (isset($geoData['localityInfo']['administrative'])) {
                        $adminLevels = count($geoData['localityInfo']['administrative']);
                        $populationDensity = (float) min(100.0, $adminLevels * 20.0);
                        $transportScore = (float) min(10.0, $adminLevels * 2.0);
                    }
                }
            } catch (Throwable $e) {
                error_log("[GEO SERVICE ERROR] BigDataCloud a crăpat: " . $e->getMessage());
            }

        
            try {
                $seismicQuery = http_build_query(['format' => 'geojson', 'starttime' => '2000-01-01', 'latitude' => $latNorm, 'longitude' => $lonNorm, 'maxradiuskm' => 120, 'minmagnitude' => 4.0]);
                $seismicUrl = "https://earthquake.usgs.gov/fdsnws/event/1/query?{$seismicQuery}";
                $seismicResp = file_get_contents($seismicUrl, false, $context);
                
                error_log("[DEBUG GEO] Răspuns USGS (Seismic): " . ($seismicResp ? 'DATE PRIMITE (marime: '.strlen($seismicResp).')' : 'FĂRĂ RĂSPUNS'));

                if ($seismicResp !== false) {
                    $seismicData = json_decode($seismicResp, true);
                    $totalEvents = $seismicData['metadata']['count'] ?? 0;
                    $seismicStability = (float) round(max(0.0, 10.0 - ($totalEvents * 1.5)), 2);
                }
            } catch (Throwable $e) {
                error_log("[SEISMIC SERVICE ERROR] USGS a crăpat: " . $e->getMessage());
            }

     
            try {
                $floodQuery = http_build_query(['latitude' => $latNorm, 'longitude' => $lonNorm, 'daily' => 'river_discharge', 'forecast_days' => 1]);
                $floodUrl = "https://flood-api.open-meteo.com/v1/flood?{$floodQuery}";
                $floodResp = file_get_contents($floodUrl, false, $context);
                
                error_log("[DEBUG GEO] Răspuns Open-Meteo Flood: " . ($floodResp ? substr($floodResp, 0, 100) . '...' : 'FĂRĂ RĂSPUNS'));

                if ($floodResp !== false) {
                    $floodData = json_decode($floodResp, true);
                    $currentDischarge = $floodData['daily']['river_discharge'][0] ?? 0.0;
                    $waterFlowRate = (float) round($currentDischarge, 2);

                    if ($waterFlowRate > 0) {
                        $waterProximity = 1.2;
                        $floodRisk = (float) round(min(100.0, ($waterFlowRate / 150) * 100), 2);
                    } else {
                        $waterProximity = 15.0;
                        $floodRisk = 0.0;
                    }
                }
            } catch (Throwable $e) {
                error_log("[FLOOD SERVICE ERROR] Open-Meteo a crăpat: " . $e->getMessage());
            }

            
   
            try {
                $soilQuery = http_build_query([
                    'latitude' => $latNorm, 
                    'longitude' => $lonNorm, 
                    'hourly' => 'soil_moisture_27_to_81cm', 
                    'forecast_days' => 1
                ]);
    
                $soilUrl = "https://api.open-meteo.com/v1/forecast?{$soilQuery}";
                $soilResp = file_get_contents($soilUrl, false, $context);
                
                if ($soilResp !== false) {
                    $soilData = json_decode($soilResp, true);
                    $moisture = $soilData['hourly']['soil_moisture_27_to_81cm'][0] ?? 0.3;
                    $groundwaterLevel = (float) round(max(0.2, (1.0 - $moisture) * 8), 2);
                }
            } catch (Throwable $e) {
                error_log("[GROUNDWATER SERVICE ERROR] Open-Meteo a crăpat: " . $e->getMessage());
            }

       
            try {
                $soilUrl = "https://rest.isric.org/soilgrids/v2.0/properties/query?lon={$lonNorm}&lat={$latNorm}&property=clay&property=sand&property=silt&depth=30-60cm&value=mean";
                $soilResp = file_get_contents($soilUrl, false, $context);
                
                error_log("[DEBUG GEO] Răspuns SoilGrids: " . ($soilResp ? substr($soilResp, 0, 100) . '...' : 'FĂRĂ RĂSPUNS'));

                if ($soilResp !== false) {
                    $sData = json_decode($soilResp, true);
                    $layers = $sData['properties']['layers'] ?? [];
                    $clayPct = 0; $sandPct = 0; $siltPct = 0;

                    foreach ($layers as $layer) {
                        $val = $layer['depths'][0]['values']['mean'] ?? 0;
                        $pct = $val / 10;
                        if ($layer['name'] === 'clay') $clayPct = $pct;
                        if ($layer['name'] === 'sand') $sandPct = $pct;
                        if ($layer['name'] === 'silt') $siltPct = $pct;
                    }

                    if (($clayPct + $sandPct + $siltPct) > 0) {
                        if ($clayPct > 40) $soilTypeEnum = SoilType::STIFF_CLAY;
                        elseif ($sandPct > 50) $soilTypeEnum = SoilType::LOOSE_SAND;
                        elseif ($siltPct > 40) $soilTypeEnum = SoilType::SILT;
                        else $soilTypeEnum = SoilType::LOAM;
                    }
                }
            } catch (Throwable $e) {
                error_log("[SOILGRIDS SERVICE ERROR] SoilGrids a crăpat: " . $e->getMessage());
            }

  
            $geoData = new GeologicalPlantData(
                $id,                  
                null,                 
                $soilTypeEnum,
                $waterSourceTypeEnum,
                $seismicStability,
                $floodRisk,
                $groundwaterLevel,
                $waterProximity,
                $waterFlowRate,
                $populationDensity,
                $transportScore,
                null                  
            );

      
            error_log("[DEBUG GEO] Obiectul GeologicalPlantData pregătit pentru salvare: " . print_r($geoData, true));

            $this->plantRepositoryFacade->saveGeologicalData($geoData);
            error_log("[DEBUG GEO] Salvarea în DB a fost apelată cu succes!");
        } else {
            error_log("[DEBUG GEO] ATENȚIE: Coordonatele sunt invalide, API-urile NU au fost apelate!");
        }

        return new CreateDataResponseDTO($id); 
    }
    public function updatePlantDetails(array $data, string $id) { 
        $name = $data['name'] ?? ''; 
        $country = $data['country'] ?? ''; 

        $latitude = (isset($data['latitude']) && $data['latitude'] !== '') ? (float) $data['latitude'] : null;         
        $longitude = (isset($data['longitude']) && $data['longitude'] !== '') ? (float) $data['longitude'] : null;
        $status = PlantStatus::DRAFT; 

        $plant = new Plant($country, $id, $name, $status, $latitude, $longitude); 

        error_log("[DEBUG] A power plant was built successfully"); 
        error_log("[DEBUG]" . print_r($plant, true)); 
        $this->plantRepositoryFacade->updatePlantDetails($plant); 
    }

    public function getAllPowerPlants(): array { 
        return $this->plantRepositoryFacade->getAllPowerPlants(); 
    }

    public function findById(string $plantId) { 
        $plant = $this->plantRepositoryFacade->getPlantDetailsById($plantId); 

        if($plant === null) { 
            echo "[ERROR] Plant with this id was not found"; 
        }

        return $plant; 
    }
}