<?php

require_once __DIR__ . '/../Constants/urls.php';
require_once __DIR__ . '/../Repositories/PlantRepository/DetailsPlantRepository.php';
require_once __DIR__ . '/../Repositories/PlantRepository/BasicPlantRepository.php';
require_once __DIR__ . '/../Repositories/PlantRepository/GeologicalPlantRepository.php';
require_once __DIR__ . '/../Repositories/PlantRepository/TechnicalPlantRepository.php';
require_once __DIR__ . '/../Repositories/ReactorRepository.php';

class RssService {
    private DetailsPlantRepository $detailsRepo;
    private BasicPlantRepository $basicRepo;
    private GeologicalPlantRepository $geologicalRepo;
    private TechnicalPlantRepository $technicalRepo;
    private ReactorRepository $reactorRepo;

    public function __construct(
        private PDO $pdo
    ) {
        $this->detailsRepo = new DetailsPlantRepository($this->pdo);
        $this->basicRepo = new BasicPlantRepository($this->pdo);
        $this->geologicalRepo = new GeologicalPlantRepository($this->pdo);
        $this->technicalRepo = new TechnicalPlantRepository($this->pdo);
        $this->reactorRepo = new ReactorRepository($this->pdo);
    }

    private function statusLabel(string $status): string {
        return match (strtoupper($status)) {
            'DRAFT'     => 'Proiect',
            'PENDING'   => 'În așteptare',
            'REVIEW'    => 'În revizuire',
            'APPROVED'  => 'Aprobat',
            'REJECTED'  => 'Respins',
            'OPERATIONAL' => 'Operațional',
            'DECOMMISSIONED' => 'Decomisionat',
            default     => $status,
        };
    }

    private function reactorTypeLabel(string $type): string {
        return match (strtoupper($type)) {
            'PWR'  => 'PWR (Apă ușoară sub presiune)',
            'BWR'  => 'BWR (Apă ușoară în fierbere)',
            'PHWR' => 'PHWR (Apă grea sub presiune)',
            'FBR'  => 'FBR (Reactor cu neutroni rapizi)',
            default => $type,
        };
    }

    private function coolingTypeLabel(string $type): string {
        return match (strtoupper($type)) {
            'COOLING_TOWER'        => 'Turn de răcire',
            'COOLING_TOWER_NATURAL_DRAFT' => 'Turn de răcire cu tiraj natural',
            'ONCE_THROUGH_FRESH'   => 'Circuit deschis (apă dulce)',
            'ONCE_THROUGH_SALT'    => 'Circuit deschis (apă sărată)',
            'COOLING_POND'         => 'Bazin de răcire',
            default => $type,
        };
    }

    public function getLatestUpdate(): ?string {
        $stmt = $this->pdo->query("
            SELECT MAX(GREATEST(
                COALESCE(p.updated_at, p.created_at),
                COALESCE(b.updated_at, b.created_at, '1970-01-01'),
                COALESCE(g.updated_at, g.created_at, '1970-01-01'),
                COALESCE(t.updated_at, t.created_at, '1970-01-01')
            )) AS latest
            FROM power_plants p
            LEFT JOIN basic_data b ON b.power_plant_id = p.id
            LEFT JOIN geological_data g ON g.power_plant_id = p.id
            LEFT JOIN technical_data t ON t.power_plant_id = p.id
        ");
        return $stmt->fetchColumn() ?: null;
    }

    public function generatePlantsRssFeed(): string {
        $plants = $this->detailsRepo->findAll();

        $visibleStatuses = ['APPROVED', 'OPERATIONAL', 'REVIEW'];
        $filtered = array_filter($plants, fn($p) =>
            in_array(strtoupper($p['status'] ?? 'DRAFT'), $visibleStatuses)
        );

        $latestUpdate = $this->getLatestUpdate();
        $buildDate = $latestUpdate ? date(DATE_RSS, strtotime($latestUpdate)) : date(DATE_RSS);

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        $xml .= '<title>Registrul Centralelor Nucleare</title>';
        $xml .= '<link>' . URL_BACKEND . '/api/rss/power-plants</link>';
        $xml .= '<atom:link href="' . URL_BACKEND . '/api/rss/power-plants" rel="self" type="application/rss+xml"/>';
        $xml .= '<description>Cele mai recente centrale nucleare înregistrate în sistemul global de monitorizare.</description>';
        $xml .= '<language>ro</language>';
        $xml .= '<lastBuildDate>' . $buildDate . '</lastBuildDate>';
        $xml .= '<generator>Nuclear Power Plant Web Manager</generator>';

        foreach ($filtered as $plant) {
            $id = $plant['id'];
            $name = $plant['name'] ?? 'Centrală fără nume';
            $country = $plant['country'] ?? 'Nespecificată';
            $status = $plant['status'] ?? 'DRAFT';

            $reactors = $this->reactorRepo->findByPlantId($id);
            $basic = $this->basicRepo->findByPlantId($id);
            $geo = $this->geologicalRepo->findByPlantId($id);
            $tech = $this->technicalRepo->findByPlantId($id);

            $pubDate = $plant['created_at']
                ? date(DATE_RSS, strtotime($plant['created_at']))
                : date(DATE_RSS);

            $categories = [];
            $reactorLines = '';
            $totalCapacityMw = 0.0;
            foreach ($reactors as $r) {
                $type = $r->getReactorType()->value;
                $categories[] = $type;
                $cooling = $r->getCoolingType()->value;
                $powerMw = $r->getElectricalPowerMw();
                if ($powerMw !== null) {
                    $totalCapacityMw += $powerMw;
                }
                $reactorLines .= sprintf(
                    '   • %s: %s, răcire %s',
                    htmlspecialchars($r->getReactorCode()),
                    $this->reactorTypeLabel($type),
                    $this->coolingTypeLabel($cooling)
                );
                if ($powerMw !== null) {
                    $reactorLines .= sprintf(', %d MWe', (int)$powerMw);
                }
                $reactorLines .= "<br/>\n";
            }
            $categories = array_unique($categories);

            $xml .= '<item>';
            $xml .= '<title>' . htmlspecialchars($name) . ' (' . htmlspecialchars($country) . ')</title>';
            $xml .= '<link>' . URL_FRONTEND . '/pages/power-plants/details.html?id=' . urlencode($id) . '</link>';
            $xml .= '<guid isPermaLink="false">' . $id . '</guid>';
            $xml .= '<pubDate>' . $pubDate . '</pubDate>';

            foreach ($categories as $cat) {
                $xml .= '<category>' . htmlspecialchars($cat) . '</category>';
            }

            if (!empty($plant['created_by'])) {
                $xml .= '<author>' . htmlspecialchars($plant['created_by']) . '</author>';
            }

            $xml .= '<description><![CDATA[';
            $xml .= '<h2>' . htmlspecialchars($name) . '</h2>';
            $xml .= '<table>';

            $xml .= '<tr><td><strong>Țară</strong></td><td>' . htmlspecialchars($country) . '</td></tr>';
            if ($geo) {
                $lat = $geo->getLatitude();
                $lon = $geo->getLongitude();
                if ($lat !== null && $lon !== null) {
                    $xml .= '<tr><td><strong>Coordonate</strong></td><td>' . sprintf('%.4f, %.4f', $lat, $lon) . '</td></tr>';
                }
            }
            $xml .= '<tr><td><strong>Statut</strong></td><td>' . $this->statusLabel($status) . '</td></tr>';

            if (!empty($reactors)) {
                $xml .= '<tr><td><strong>Reactoare</strong></td><td>' . count($reactors) . '</td></tr>';
            }
            if ($tech) {
                $eff = $tech->getEstimatedEfficiency();
                if ($eff !== null) {
                    $xml .= '<tr><td><strong>Randament estimat</strong></td><td>' . (int)$eff . '%</td></tr>';
                }
                $numReactorConfigs = count($tech->getReactorConfigurations());
                if ($numReactorConfigs > 0) {
                    $xml .= '<tr><td><strong>Configurații reactor</strong></td><td>';
                    $configLines = [];
                    foreach ($tech->getReactorConfigurations() as $rc) {
                        $configLines[] = $this->reactorTypeLabel($rc->getType()->value)
                            . ' + '
                            . $this->coolingTypeLabel($rc->getCooling()->value);
                    }
                    $xml .= implode('<br/>', $configLines);
                    $xml .= '</td></tr>';
                }
            }
            if ($basic) {
                $cap = $basic->getCapacity();
                if ($cap !== null) {
                    $xml .= '<tr><td><strong>Capacitate instalată</strong></td><td>' . number_format($cap, 0) . ' MW</td></tr>';
                }
                if ($totalCapacityMw > 0) {
                    $xml .= '<tr><td><strong>Capacitate electrică totală</strong></td><td>' . number_format($totalCapacityMw, 0) . ' MWe</td></tr>';
                }
                $dur = $basic->getConstructionDurationYears();
                if ($dur !== null) {
                    $xml .= '<tr><td><strong>Durata de construcție</strong></td><td>' . $dur . ' ani</td></tr>';
                }
                $desc = $basic->getDescription();
                if (!empty($desc)) {
                    $xml .= '<tr><td colspan="2"><br/><em>' . htmlspecialchars($desc) . '</em></td></tr>';
                }
            }

            $xml .= '</table>';

            if (!empty($reactorLines)) {
                $xml .= '<h3>Detalii reactoare</h3>';
                $xml .= $reactorLines;
            }

            if ($geo) {
                $details = [];
                $seismic = $geo->getSeismicStability();
                if ($seismic !== null) {
                    $details[] = 'Stabilitate seismică: ' . number_format($seismic, 1) . '/10';
                }
                $flood = $geo->getFloodRisk();
                if ($flood !== null) {
                    $details[] = 'Risc inundații: ' . number_format($flood, 1) . '/10';
                }
                $waterP = $geo->getWaterProximity();
                if ($waterP !== null) {
                    $details[] = 'Distanță sursă apă: ' . number_format($waterP, 2) . ' km';
                }
                $pop = $geo->getPopulationDensity();
                if ($pop !== null) {
                    $details[] = 'Densitate populație: ' . number_format($pop, 0) . ' loc/km²';
                }
                $transport = $geo->getTransportInfrastructureScore();
                if ($transport !== null) {
                    $details[] = 'Infrastructură transport: ' . number_format($transport, 1) . '/10';
                }
                if (!empty($details)) {
                    $xml .= '<h3>Amplasament</h3>';
                    $xml .= implode('<br/>', $details);
                }
            }

            $xml .= ']]></description>';
            $xml .= '</item>';
        }

        $xml .= '</channel>';
        $xml .= '</rss>';

        return $xml;
    }
}
