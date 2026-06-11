<?php

// URL-ul endpoint-ului tău local expus prin Docker
$endpointUrl = 'http://localhost/api/alerts/receive';

// Generăm un UUID fals pentru a respecta constrângerea bazei de date
$dummyPlantId = '550e8400-e29b-41d4-a716-446655440000';

// Definim setul de teste acoperind toate cele 4 stări (1 ignorată, 3 procesate)
$testPayloads = [
    [
        'plant_id' => $dummyPlantId,
        'type' => 'NORMAL',
        'message' => 'Toți parametrii fizici sunt în limitele operaționale standard. Nicio acțiune necesară.'
    ],
    [
        'plant_id' => $dummyPlantId,
        'type' => 'ALERT',
        'message' => 'Avertisment: Fluctuație minoră de presiune detectată în circuitul primar de răcire.'
    ],
    [
        'plant_id' => $dummyPlantId,
        'type' => 'ALARM',
        'message' => 'Critic: Temperatura miezului reactorului se apropie de pragul maxim de siguranță.'
    ],
    [
        'plant_id' => $dummyPlantId,
        'type' => 'SCRAM',
        'message' => 'OPRIRE DE URGENȚĂ: Barele de control au fost inserate complet. Reacția de fisiune a fost oprită.'
    ]
];

echo "=== INIȚIERE SIMULARE SENZORI REACTOR ===\n\n";

foreach ($testPayloads as $payload) {
    echo "Expediere semnal: [" . $payload['type'] . "]...\n";
    
    $ch = curl_init($endpointUrl);
    
    $jsonData = json_encode($payload);
    
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        echo "[EROARE cURL] " . curl_error($ch) . "\n";
    } else {
        echo "[STATUS HTTP {$httpCode}] Răspuns server: {$response}\n";
    }
    
    curl_close($ch);
    

    echo "Așteptare 2 secunde...\n\n";
    sleep(6);
}

echo "=== SIMULARE FINALIZATĂ ===\n";