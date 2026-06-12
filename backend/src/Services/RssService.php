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

            if (empty($reactors)) {
                continue;
            }

            foreach ($reactors as $r) {
                $reactorId = $r->getId();
                $reactorCode = $r->getReactorCode();
                $reactorType = $r->getReactorType()->value;
                $cooling = $r->getCoolingType()->value;
                $powerMw = $r->getElectricalPowerMw();

                $xml .= '<item>';
                $xml .= '<title>' . htmlspecialchars($reactorCode) . ' — ' . htmlspecialchars($name) . ' (' . htmlspecialchars($country) . ')</title>';
                $xml .= '<link>' . URL_FRONTEND . '/pages/reactors/detail.html?reactorId=' . urlencode($reactorId) . '&amp;plantId=' . urlencode($id) . '</link>';
                $xml .= '<guid isPermaLink="false">' . $reactorId . '</guid>';
                $xml .= '<pubDate>' . $pubDate . '</pubDate>';

                $xml .= '<category>' . htmlspecialchars($reactorType) . '</category>';

                if (!empty($plant['created_by'])) {
                    $xml .= '<author>' . htmlspecialchars($plant['created_by']) . '</author>';
                }

                $xml .= '<description><![CDATA[';
                $xml .= '<h2>' . htmlspecialchars($reactorCode) . '</h2>';
                $xml .= '<table>';

                $xml .= '<tr><td><strong>Centrală</strong></td><td>' . htmlspecialchars($name) . '</td></tr>';
                $xml .= '<tr><td><strong>Țară</strong></td><td>' . htmlspecialchars($country) . '</td></tr>';
                $xml .= '<tr><td><strong>Tip reactor</strong></td><td>' . $this->reactorTypeLabel($reactorType) . '</td></tr>';
                $xml .= '<tr><td><strong>Răcire</strong></td><td>' . $this->coolingTypeLabel($cooling) . '</td></tr>';
                if ($powerMw !== null) {
                    $xml .= '<tr><td><strong>Putere electrică</strong></td><td>' . number_format($powerMw, 0) . ' MWe</td></tr>';
                }
                $xml .= '<tr><td><strong>Statut centrală</strong></td><td>' . $this->statusLabel($status) . '</td></tr>';
                $operStatus = $r->getOperationalStatus()->value;
                $xml .= '<tr><td><strong>Status operare</strong></td><td>' . htmlspecialchars($operStatus) . '</td></tr>';

                if ($geo) {
                    $lat = $geo->getLatitude();
                    $lon = $geo->getLongitude();
                    if ($lat !== null && $lon !== null) {
                        $xml .= '<tr><td><strong>Coordonate</strong></td><td>' . sprintf('%.4f, %.4f', $lat, $lon) . '</td></tr>';
                    }
                }
                if ($basic) {
                    $cap = $basic->getCapacity();
                    if ($cap !== null) {
                        $xml .= '<tr><td><strong>Capacitate instalată</strong></td><td>' . number_format($cap, 0) . ' MW</td></tr>';
                    }
                    $desc = $basic->getDescription();
                    if (!empty($desc)) {
                        $xml .= '<tr><td colspan="2"><br/><em>' . htmlspecialchars($desc) . '</em></td></tr>';
                    }
                }

                $xml .= '</table>';

                $xml .= ']]></description>';
                $xml .= '</item>';
            }
        }

        $xml .= '</channel>';
        $xml .= '</rss>';

        return $xml;
    }
}
