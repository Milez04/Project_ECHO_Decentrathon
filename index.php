<?php

if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] === '/api') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET');
    header('Access-Control-Allow-Headers: Content-Type');

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "utm_db";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die(json_encode(["error" => "Қосылу сәтсіз: " . $conn->connect_error]));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';

        if ($action === 'register') {
            $model = $data['model'] ?? '';
            $serial = $data['serial'] ?? '';
            $pilot_name = $data['pilot_name'] ?? '';
            $pilot_contact = $data['pilot_contact'] ?? '';
            $current_base = $data['current_base'] ?? 'Nurzhol';

            if ($model && $serial && $pilot_name && $pilot_contact) {
                $stmt = $conn->prepare("INSERT INTO drones (model, serial, pilot_name, pilot_contact, current_base) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $model, $serial, $pilot_name, $pilot_contact, $current_base);
                if ($stmt->execute()) {
                    echo json_encode(["message" => "Тіркеу сәтті"]);
                } else {
                    echo json_encode(["error" => "Тіркеу сәтсіз"]);
                }
                $stmt->close();
            } else {
                echo json_encode(["error" => "Барлық өрістер міндетті"]);
            }
        } elseif ($action === 'get_drones') {
            $result = $conn->query("SELECT id, model, serial, current_base, battery FROM drones");
            $drones = [];
            while ($row = $result->fetch_assoc()) {
                $drones[] = $row;
            }
            echo json_encode($drones);
        } elseif ($action === 'get_drones_by_base') {
            $base = $data['base'] ?? '';
            if ($base) {
                $stmt = $conn->prepare("SELECT id, model, serial, battery FROM drones WHERE current_base = ?");
                $stmt->bind_param("s", $base);
                $stmt->execute();
                $result = $stmt->get_result();
                $drones = [];
                while ($row = $result->fetch_assoc()) {
                    $drones[] = $row;
                }
                echo json_encode($drones);
                $stmt->close();
            } else {
                echo json_encode(["error" => "Негізгі қажет"]);
            }
        } elseif ($action === 'update_base') {
            $drone_id = $data['drone_id'] ?? '';
            $new_base = $data['new_base'] ?? '';
            if ($drone_id && $new_base) {
                $stmt = $conn->prepare("UPDATE drones SET current_base = ? WHERE id = ?");
                $stmt->bind_param("si", $new_base, $drone_id);
                if ($stmt->execute()) {
                    echo json_encode(["message" => "Негізі жаңартылды"]);
                } else {
                    echo json_encode(["error" => "Жаңарту сәтсіз"]);
                }
                $stmt->close();
            } else {
                echo json_encode(["error" => "Drone ID және негізі қажет"]);
            }
        } elseif ($action === 'update_battery') {
            $drone_id = $data['drone_id'] ?? '';
            $battery = $data['battery'] ?? 0;
            if ($drone_id && $battery >= 0) {
                $stmt = $conn->prepare("UPDATE drones SET battery = ? WHERE id = ?");
                $stmt->bind_param("di", $battery, $drone_id);
                if ($stmt->execute()) {
                    echo json_encode(["message" => "Батарея жаңартылды"]);
                } else {
                    echo json_encode(["error" => "Батареяны жаңарту сәтсіз"]);
                }
                $stmt->close();
            } else {
                echo json_encode(["error" => "Drone ID және батарея ақпараты қажет"]);
            }
        } elseif ($action === 'log_flight') {
            $drone_id = $data['drone_id'] ?? '';
            $start_base = $data['start_base'] ?? '';
            $end_base = $data['end_base'] ?? '';
            $distance = $data['distance'] ?? 0;
            $duration = $data['duration'] ?? 0;
            $altitude = $data['altitude'] ?? 0;
            $start_time = date('Y-m-d H:i:s');
            if ($drone_id && $start_base && $end_base) {
                $stmt = $conn->prepare("INSERT INTO flight_history (drone_id, start_base, end_base, distance, duration, altitude, start_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssdds", $drone_id, $start_base, $end_base, $distance, $duration, $altitude, $start_time);
                if ($stmt->execute()) {
                    echo json_encode(["message" => "Ұшу тіркелді"]);
                } else {
                    echo json_encode(["error" => "Тіркеу сәтсіз"]);
                }
                $stmt->close();
            } else {
                echo json_encode(["error" => "Қажетті өрістер жетіспейді"]);
            }
        } elseif ($action === 'get_flights') {
            $result = $conn->query("SELECT drone_id, start_base, end_base, distance, start_time FROM flight_history ORDER BY start_time DESC");
            $flights = [];
            while ($row = $result->fetch_assoc()) {
                $flights[] = $row;
            }
            echo json_encode($flights);
        }
    }

    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Астана Дрон UTM Жүйесі</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        h1 {
            text-align: center;
            font-size: 2.8em;
            margin-bottom: 20px;
            color: #fff;
            background: linear-gradient(90deg, #00c6ff, #0072ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 10px rgba(0, 198, 255, 0.5);
        }
        .status-weather {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .status-panel, .weather-info, .alert-panel {
            flex: 1;
            background: rgba(52, 58, 64, 0.9);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            border: 1px solid #495057;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .status-panel:hover, .weather-info:hover, .alert-panel:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 198, 255, 0.3);
        }
        .status-panel h4, .weather-info h4, .alert-panel h4 {
            margin-bottom: 10px;
            color: #00c6ff;
        }
        .weather-safe { color: #00f !important; }
        .weather-danger { color: #f00 !important; }
        .weather-danger::after { content: " ⚠️ Ұшуға қауіпті!"; font-weight: bold; }
        .alert-panel ul { list-style-type: none; padding: 0; }
        .alert-panel li { color: #ff4d4d; margin-bottom: 5px; }
        .main-content {
            display: flex;
            gap: 20px;
            height: 70vh;
        }
        .left-panel {
            flex: 1;
            min-width: 300px;
        }
        .panel {
            background: rgba(52, 58, 64, 0.9);
            border-radius: 10px;
            padding: 15px;
            overflow-y: auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            border: 1px solid #495057;
            transition: transform 0.3s;
        }
        .panel:hover {
            transform: translateY(-5px);
        }
        .map-container {
            flex: 3;
            position: relative;
        }
        #map {
            height: 100%;
            width: 100%;
            border-radius: 10px;
            border: 2px solid #00c6ff;
            box-shadow: 0 0 15px rgba(0, 198, 255, 0.5);
        }
        .form-container {
            background: rgba(52, 58, 64, 0.9);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        .btn-primary {
            background: linear-gradient(90deg, #00c6ff, #0072ff);
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0, 198, 255, 0.5);
        }
        .drone-icon {
            font-size: 40px;
            color: #ffeb3b;
            transition: transform 0.1s;
            text-shadow: 0 0 10px rgba(255, 235, 59, 0.7);
        }
        .drone-icon-low { box-shadow: 0 0 15px rgba(255, 0, 0, 0.7); }
        .drone-icon-high { box-shadow: 0 5px 15px rgba(0, 255, 0, 0.7); }
        @keyframes blink { 50% { opacity: 0.5; } }
        .drone-icon span { animation: blink 1s infinite; }
        .table {
            background: transparent;
            color: #e0e0e0;
        }
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            --bs-table-bg-type: rgba(73, 80, 87, 0.5);
        }
        .table th {
            color: #00c6ff;
        }
        .progress {
            height: 15px;
            background-color: #495057;
        }
        .progress-bar {
            background: linear-gradient(90deg, #28a745, #34c759);
        }
        .base-marker { cursor: pointer; }
        .cloud-icon { opacity: 0.5; }
        .modal-content {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e0e0e0;
            border: 1px solid #00c6ff;
        }
        .modal-header, .modal-footer {
            border-color: #495057;
        }
        .btn-secondary {
            background: #495057;
            border: none;
        }
        .btn-success {
            background: linear-gradient(90deg, #28a745, #34c759);
            border: none;
            border-radius: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-success:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(40, 167, 69, 0.5);
        }
        .route-safe { color: green; }
        .route-risky { color: red; }
        .alert-zone { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }
        .weather-warning-icon { font-size: 50px; animation: pulse 2s infinite; }
        .status-pending { color: #ffeb3b; }
        .status-approved { color: #28a745; }
        .status-rejected { color: #ff4d4d; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Астана Дрон UTM Жүйесі</h1>
        <div class="status-weather">
            <div class="status-panel">
                <h4>Жағдай Қорытындысы</h4>
                <p>Жалпы Дрондар: <span id="totalDrones">0</span> | Белсенді Ұшулар: <span id="activeFlights">0</span></p>
            </div>
            <div class="weather-info">
                <h4>Ауа-райы</h4>
                <div id="weatherInfo"></div>
            </div>
            <div class="alert-panel">
                <h4>Ескертулер</h4>
                <ul id="alertList"></ul>
            </div>
        </div>
        <div class="main-content">
            <div class="left-panel">
                <div class="panel">
                    <div class="form-container">
                        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#registerModal">Жаңа Дрон Тіркеу</button>
                    </div>
                    <h4>Белсенді Ұшулар</h4>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Дрон ID</th>
                                <th>Маршрут</th>
                                <th>Қашықтық</th>
                                <th>Ұзақтық</th>
                                <th>Биіктік</th>
                                <th>Батарея</th>
                                <th>Ілгерілеу</th>
                                <th>Жағдай</th>
                                <th>Рұқсат Күйі</th>
                            </tr>
                        </thead>
                        <tbody id="flightTable"></tbody>
                    </table>
                    <h4>Ұшу Тарихы</h4>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Дрон ID</th>
                                <th>Маршрут</th>
                                <th>Қашықтық</th>
                                <th>Басталу Уақыты</th>
                            </tr>
                        </thead>
                        <tbody id="historyTable"></tbody>
                    </table>
                </div>
            </div>
            <div class="map-container">
                <div class="panel" id="map"></div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Жаңа Дрон Тіркеу</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Жабу"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-2" id="droneModel" placeholder="Дрон Үлгісі">
                    <input type="text" class="form-control mb-2" id="droneSerial" placeholder="Серия Нөмірі">
                    <input type="text" class="form-control mb-2" id="pilotName" placeholder="Пилот Аты">
                    <input type="text" class="form-control mb-2" id="pilotContact" placeholder="Байланыс">
                    <select class="form-control mb-2" id="currentBase">
                        <option value="Nurzhol">Nurzhol</option>
                        <option value="Bayterek">Bayterek</option>
                        <option value="EXPO">EXPO</option>
                        <option value="Khan Shatyr">Khan Shatyr</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Жабу</button>
                    <button type="button" class="btn btn-primary" onclick="registerDrone()">Тіркеу</button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="droneModal" tabindex="-1" aria-labelledby="droneModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="droneModalLabel">Дрон Детальдары</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Жабу"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Дрон ID:</strong> <span id="droneId"></span></p>
                    <p><strong>Координата:</strong> <span id="droneCoords"></span></p>
                    <p><strong>Биіктік:</strong> <span id="droneAltitude"></span></p>
                    <p><strong>Батарея:</strong> <span id="droneBattery"></span>%</p>
                    <p><strong>Жылдамдық:</strong> <span id="droneSpeed"></span> км/с</p>
                    <p><strong>Қалған Уақыт:</strong> <span id="droneRemainingTime"></span> мин</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Жабу</button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="sendDroneModal" tabindex="-1" aria-labelledby="sendDroneModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendDroneModalLabel">Мақсатты Негіз Таңдау</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Жабу"></button>
                </div>
                <div class="modal-body">
                    <select class="form-control mb-2" id="targetBase">
                        
                    </select>
                    <select class="form-control mb-2" id="routeType">
                        <option value="short">Қысқа Маршрут</option>
                        <option value="energy">Энергияны Үнемдейтін Маршрут</option>
                    </select>
                    <input type="hidden" id="selectedDroneId">
                    <input type="hidden" id="selectedStartBase">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Жабу</button>
                    <button type="button" class="btn btn-success" onclick="confirmSendDroneFromModal()">Жіберу</button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="selectDroneModal" tabindex="-1" aria-labelledby="selectDroneModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectDroneModalLabel">Дрон Таңдау</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Жабу"></button>
                </div>
                <div class="modal-body">
                    <select class="form-control mb-2" id="startBaseSelect">
                        <option value="" disabled selected>Басталу Негізін Таңдау</option>
                       
                    </select>
                    <select class="form-control mb-2" id="droneSelect">
                        <option value="" disabled selected>Дрон Таңдау</option>
                        
                    </select>
                    <select class="form-control mb-2" id="routeType">
                        <option value="short">Қысқа Маршрут</option>
                        <option value="energy">Энергияны Үнемдейтін Маршрут</option>
                    </select>
                    <input type="hidden" id="targetCoordsLat">
                    <input type="hidden" id="targetCoordsLng">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Жабу</button>
                    <button type="button" class="btn btn-success" onclick="sendDroneToCoords()">Жіберу</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Карта: Астана
        var map = L.map('map').setView([51.1694, 71.4491], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        
        var bases = [
            { name: "Nurzhol", coords: [51.1282, 71.4307], city: "Астана", marker: null },
            { name: "Bayterek", coords: [51.1281, 71.4460], city: "Астана", marker: null },
            { name: "EXPO", coords: [51.0890, 71.4118], city: "Астана", marker: null },
            { name: "Khan Shatyr", coords: [51.1358, 71.4043], city: "Астана", marker: null }
        ];

        
        var buildingHeights = {
            "Астана Әуежайы": 50,
            "Ақорда Сарайы": 80,
            "Байтерек": 105,
            "EXPO": 60,
            "Хан Шатыр": 150
        };

        
        var noFlyZones = [
            { center: [51.1694, 71.4491], radius: 1000, name: "Астана Әуежайы" },
            { center: [51.1200, 71.4400], radius: 500, name: "Ақорда Сарайы" },
            { center: [51.1100, 71.4220], radius: 500, name: "Ақорда Сарайы" }
        ];

        noFlyZones.forEach(zone => {
            L.circle(zone.center, {
                radius: zone.radius,
                color: 'red',
                fillOpacity: 0.3
            }).addTo(map).bindPopup(`Тыйым салынған аймақ: ${zone.name}<br>Биіктік: ${buildingHeights[zone.name] || 'Білінбейді'}м`);
        });

        
        bases.forEach(base => {
            base.marker = L.marker(base.coords, { className: 'base-marker' }).addTo(map)
                .bindPopup(`<b>${base.name}</b><br>Координата: ${base.coords}<br><button onclick="showBaseDrones('${base.name}')" class="btn btn-sm btn-info mt-1">Дрондар</button>`);
            base.marker.on('click', () => showBaseDrones(base.name));
        });

        
        function showBaseDrones(baseName) {
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>/api', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_drones_by_base', base: baseName })
            }).then(r => r.json()).then(drones => {
                const base = bases.find(b => b.name === baseName);
                let content = `<b>${base.name}</b><br>Координата: ${base.coords}<br><h6>Дрондар:</h6>`;
                if (drones.length) {
                    content += '<ul>';
                    drones.forEach(d => {
                        content += `<li>ID: ${d.id}, Үлгі: ${d.model}, Батарея: ${d.battery.toFixed(0)}% <button class="btn btn-sm btn-success mt-1" onclick="openSendDroneModal('${d.id}', '${base.name}')">Жіберу</button></li>`;
                    });
                    content += '</ul>';
                } else {
                    content += 'Дрон жоқ.';
                }
                base.marker.setPopupContent(content).openPopup();
            });
        }

        
        function openSendDroneModal(droneId, startBaseName) {
            const currentHour = new Date().getHours();
            if (currentHour >= 20 || currentHour < 6) {
                alert('Қате: 20:00 - 06:00 аралығында ұшуға тыйым салынады!');
                addAlert('Түнгі ұшуға тыйым: 20:00 - 06:00 аралығында ұшу мүмкін емес.');
                return;
            }

            document.getElementById('selectedDroneId').value = droneId;
            document.getElementById('selectedStartBase').value = startBaseName;
            const targetBaseSelect = document.getElementById('targetBase');
            targetBaseSelect.innerHTML = '';
            bases.forEach(base => {
                if (base.name !== startBaseName) {
                    const option = document.createElement('option');
                    option.value = base.name;
                    option.text = base.name;
                    targetBaseSelect.appendChild(option);
                }
            });
            new bootstrap.Modal(document.getElementById('sendDroneModal')).show();
        }

        
        function confirmSendDrone(droneDbId, startBaseName, endBaseName, endCoords, routeType) {
            console.log('confirmSendDrone шақырылды:', { droneDbId, startBaseName, endBaseName, endCoords, routeType });

            const startBase = bases.find(b => b.name === startBaseName);
            if (!startBase) {
                alert('Жарамды басталу негізін таңдаңыз!');
                console.error('Басталу негізі табылмады:', startBaseName);
                return;
            }

            const startCoords = startBase.coords;

            let tooCloseToNoFlyZone = false;
            let maxBuildingHeight = 0;
            for (const zone of noFlyZones) {
                const distToEnd = calculateDistance(endCoords, zone.center) * 1000;
                const distToStart = calculateDistance(startCoords, zone.center) * 1000;
                if (distToEnd < zone.radius + 200 || distToStart < zone.radius + 200) {
                    tooCloseToNoFlyZone = true;
                    const height = buildingHeights[zone.name] || 0;
                    maxBuildingHeight = Math.max(maxBuildingHeight, height);
                }
            }

            let route;
            if (routeType === 'short') {
                route = createCurvedRoute(startCoords, endCoords);
            } else {
                route = createEnergyEfficientRoute(startCoords, endCoords);
            }

            let maxRouteBuildingHeight = maxBuildingHeight;
            for (const point of route) {
                for (const zone of noFlyZones) {
                    const dist = calculateDistance(point, zone.center) * 1000;
                    if (dist < zone.radius + 200) {
                        const height = buildingHeights[zone.name] || 0;
                        maxRouteBuildingHeight = Math.max(maxRouteBuildingHeight, height);
                    }
                }
            }

            let altitude = 100 + maxRouteBuildingHeight + 50;

            if (altitude > 400) {
                alert('Қате: Биіктік шегі 400м аспайды! Маршрут тоқтатылды.');
                addAlert(`Дрон ${droneDbId} үшін биіктік шегі (400м) асты.`);
                console.error('Биіктік шегі асты:', altitude);
                return;
            }

            let attempts = 0;
            const maxAttempts = 5;
            let collisionCheck = checkFlightCollision(route, altitude);

            while ((checkNoFlyZoneRoute(route) || collisionCheck.collision || collisionCheck.futureCollision) && attempts < maxAttempts) {
                if (checkNoFlyZoneRoute(route)) {
                    alert('Қате: Маршрут тыйым салынған аймақтан өтеді. Балама маршрут сынақтан өтіп жатыр.');
                    playAlertSound();
                    addAlert(`Дрон ${droneDbId} үшін маршрут тыйым салынған аймақтан өтеді.`);
                    route = createSafeRouteAroundNoFlyZone(startCoords, endCoords, noFlyZones, tooCloseToNoFlyZone);
                } else if (collisionCheck.collision || collisionCheck.futureCollision) {
                    alert('Қате: Соқтығысу немесе болашақта соқтығысу қаупі. Балама маршрут немесе биіктік сынақтан өтіп жатыр.');
                    playAlertSound();
                    addAlert(`Дрон ${droneDbId} үшін соқтығысу қаупі анықталды.`);
                    altitude += 50;
                    if (altitude > 400) {
                        alert('Қате: Биіктік шегі 400м аспайды! Маршрут тоқтатылды.');
                        addAlert(`Дрон ${droneDbId} үшін биіктік шегі (400м) асты.`);
                        console.error('Биіктік шегі асты (соқтығыстан кейін):', altitude);
                        return;
                    }
                    collisionCheck = checkFlightCollision(route, altitude);
                    if (!collisionCheck.collision && !collisionCheck.futureCollision) break;
                    route = createSafeRouteAroundOtherDrones(startCoords, endCoords, collisionCheck.conflictingDrone || null);
                }
                attempts++;
            }

            if (checkNoFlyZoneRoute(route) || collisionCheck.collision || collisionCheck.futureCollision) {
                alert('Қате: Барлық балама маршруттар мен биіктіктер сынақтан өтілді, бірақ қауіпсіз маршрут табылмады. Басқа уақытта қайталаңыз.');
                playAlertSound();
                addAlert(`Дрон ${droneDbId} үшін қауіпсіз маршрут табылмады.`);
                console.error('Қауіпсіз маршрут табылмады:', { route, collisionCheck });
                return;
            }

            L.polyline(route, { color: collisionCheck.collision || collisionCheck.futureCollision ? 'red' : 'green', weight: 2, opacity: 0.5, className: collisionCheck.collision || collisionCheck.futureCollision ? 'route-risky' : 'route-safe' }).addTo(map);
            const distance = calculateDistance(startCoords, endCoords);
            const duration = calculateDuration(distance, 20);
            const uniqueDroneId = drones.length + 1;

            const drone = {
                id: uniqueDroneId,
                dbId: parseInt(droneDbId),
                marker: L.marker(startCoords, {
                    icon: L.divIcon({ html: '<span>✈️</span>', className: `drone-icon drone-icon-${altitude < 200 ? 'low' : 'high'}` })
                }).addTo(map),
                route: route,
                step: 0,
                steps: 1500,
                start: startCoords,
                end: endCoords,
                altitude: altitude,
                startTime: new Date(),
                startBase: startBaseName,
                endBase: endBaseName,
                status: 'Ұшып жатыр',
                battery: 100,
                baseSpeed: 20,
                speed: 20,
                distance: distance,
                duration: duration,
                clearanceStatus: 'Күтілуде'
            };

            drones.push(drone);
            updateFlightTable();

            let clearanceIndex = 0;
            const clearanceStates = ['Күтілуде', 'Мақұлданды'];
            const interval = setInterval(() => {
                if (clearanceIndex < clearanceStates.length) {
                    drone.clearanceStatus = clearanceStates[clearanceIndex];
                    updateFlightTable();
                    clearanceIndex++;
                } else {
                    clearInterval(interval);
                    if (drone.clearanceStatus === 'Мақұлданды') {
                        drone.speed = calculateDroneSpeed(drone);
                        drone.marker.bindTooltip(`Батарея: ${drone.battery.toFixed(0)}% | Жылдамдық: ${drone.speed.toFixed(1)}км/с`, { permanent: true, direction: 'top', offset: [0, -20] });
                        drone.marker.on('click', () => {
                            document.getElementById('droneId').innerText = drone.id;
                            document.getElementById('droneCoords').innerText = `${drone.marker.getLatLng().lat.toFixed(4)}, ${drone.marker.getLatLng().lng.toFixed(4)}`;
                            document.getElementById('droneAltitude').innerText = `${drone.altitude.toFixed(0)}м`;
                            document.getElementById('droneBattery').innerText = `${drone.battery.toFixed(0)}`;
                            document.getElementById('droneSpeed').innerText = `${drone.speed.toFixed(1)}`;
                            const remainingTime = (drone.duration - ((new Date() - drone.startTime) / 1000 / 60)).toFixed(2);
                            document.getElementById('droneRemainingTime').innerText = remainingTime > 0 ? remainingTime : 0;
                            new bootstrap.Modal(document.getElementById('droneModal')).show();
                        });
                        fetch('<?php echo $_SERVER['PHP_SELF']; ?>/api', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'log_flight', drone_id: drone.dbId, start_base: startBaseName, end_base: endBaseName, distance: distance, duration: duration, altitude: altitude })
                        }).then(() => updateHistoryTable());
                        alert(`Ұшу сұрауы: ${startBaseName} -> ${endBaseName}, Қашықтық: ${distance.toFixed(2)}км, Ұзақтық: ${duration.toFixed(2)}мин, Биіктік: ${altitude.toFixed(0)}м`);
                        animateDrones();
                        updateStatusPanel();
                    }
                }
            }, 3000);

            fetch('<?php echo $_SERVER['PHP_SELF']; ?>/api', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_drones_by_base', base: startBaseName })
            }).then(r => r.json()).then(dronesData => {
                const dbDrone = dronesData.find(d => d.id == droneDbId);
                if (dbDrone) {
                    drone.battery = dbDrone.battery;
                    console.log('Дрон батареясы жаңартылды:', drone.battery);
                } else {
                    console.error('Дрон дерекқорда табылмады:', droneDbId);
                }
            }).catch(err => {
                console.error('Дрон ақпараты алынғанда қате:', err);
            });
        }

        
        function confirmSendDroneFromModal() {
            const droneDbId = document.getElementById('selectedDroneId').value;
            const startBaseName = document.getElementById('selectedStartBase').value;
            const endBaseName = document.getElementById('targetBase').value;
            const routeType = document.getElementById('routeType').value;

            console.log('confirmSendDroneFromModal шақырылды:', { droneDbId, startBaseName, endBaseName, routeType });

            if (!droneDbId || !startBaseName || !endBaseName) {
                alert('Өрістерді толтырыңыз!');
                console.error('Толтырылмаған өрістер:', { droneDbId, startBaseName, endBaseName });
                return;
            }

            const endBase = bases.find(b => b.name === endBaseName);
            if (!endBase || startBaseName === endBaseName) {
                alert('Жарамды мақсатты негізді таңдаңыз!');
                console.error('Жарамсыз мақсатты негіз:', { endBase, startBaseName, endBaseName });
                return;
            }

            const endCoords = endBase.coords;

            confirmSendDrone(droneDbId, startBaseName, endBaseName, endCoords, routeType);

            const modal = bootstrap.Modal.getInstance(document.getElementById('sendDroneModal'));
            if (modal) {
                modal.hide();
            } else {
                console.error('sendDroneModal табылмады');
            }
        }

        
        map.on('click', function (e) {
            const clickedCoords = [e.latlng.lat, e.latlng.lng];
            console.log('Картаға тікті:', clickedCoords);

            let targetBase = null;
            for (const base of bases) {
                const dist = calculateDistance(clickedCoords, base.coords) * 1000;
                if (dist < 200) {
                    targetBase = base;
                    break;
                }
            }

            document.getElementById('targetCoordsLat').value = clickedCoords[0];
            document.getElementById('targetCoordsLng').value = clickedCoords[1];

            const startBaseSelect = document.getElementById('startBaseSelect');
            startBaseSelect.innerHTML = '<option value="" disabled selected>Басталу Негізін Таңдау</option>';
            bases.forEach(base => {
                if (!targetBase || base.name !== targetBase.name) {
                    const option = document.createElement('option');
                    option.value = base.name;
                    option.text = base.name;
                    startBaseSelect.appendChild(option);
                }
            });

            startBaseSelect.onchange = function () {
                const selectedBase = startBaseSelect.value;
                console.log('Басталу негізі таңдалды:', selectedBase);
                fetch('<?php echo $_SERVER['PHP_SELF']; ?>/api', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_drones_by_base', base: selectedBase })
                }).then(r => r.json()).then(dronesData => {
                    const droneSelect = document.getElementById('droneSelect');
                    droneSelect.innerHTML = '<option value="" disabled selected>Дрон Таңдау</option>';
                    dronesData.forEach(drone => {
                        const option = document.createElement('option');
                        option.value = drone.id;
                        option.text = `ID: ${drone.id}, Үлгі: ${drone.model}, Батарея: ${drone.battery.toFixed(0)}%`;
                        droneSelect.appendChild(option);
                    });
                    console.log('Дрон опциялары жүктелді:', dronesData);
                }).catch(err => {
                    console.error('Дрон ақпараты алынғанда қате:', err);
                });
            };

            new bootstrap.Modal(document.getElementById('selectDroneModal')).show();
        });

        
        function sendDroneToCoords() {
            const droneDbId = document.getElementById('droneSelect').value;
            const startBaseName = document.getElementById('startBaseSelect').value;
            const routeType = document.getElementById('routeType').value;
            const targetLat = parseFloat(document.getElementById('targetCoordsLat').value);
            const targetLng = parseFloat(document.getElementById('targetCoordsLng').value);
            const endCoords = [targetLat, targetLng];

            console.log('sendDroneToCoords шақырылды:', { droneDbId, startBaseName, routeType, targetLat, targetLng });

            if (!droneDbId || !startBaseName) {
                alert('Басталу негізі мен дронды таңдаңыз!');
                console.error('Таңдау жетіспейді:', { droneDbId, startBaseName });
                return;
            }

            let endBaseName = 'Арнайы Орын';
            for (const base of bases) {
                const dist = calculateDistance(endCoords, base.coords) * 1000;
                if (dist < 200) {
                    endBaseName = base.name;
                    endCoords[0] = base.coords[0];
                    endCoords[1] = base.coords[1];
                    break;
                }
            }

            confirmSendDrone(droneDbId, startBaseName, endBaseName, endCoords, routeType);

            const modal = bootstrap.Modal.getInstance(document.getElementById('selectDroneModal'));
            if (modal) {
                modal.hide();
            } else {
                console.error('selectDroneModal табылмады');
            }
        }

        function createSafeRouteAroundNoFlyZone(start, end, noFlyZones, tooCloseToNoFlyZone) {
            let route = createCurvedRoute(start, end);
            let intersects = false;
            let closestZone = null;
            let minDist = Infinity;

            for (const point of route) {
                for (const zone of noFlyZones) {
                    const dist = calculateDistance(point, zone.center) * 1000;
                    if (dist < zone.radius) {
                        intersects = true;
                        const distToZone = calculateDistance(start, zone.center) * 1000;
                        if (distToZone < minDist) {
                            minDist = distToZone;
                            closestZone = zone;
                        }
                    }
                }
            }

            if (!intersects) return route;

            const zoneCenter = closestZone.center;
            let safeDistance = closestZone.radius + 200;
            if (tooCloseToNoFlyZone) {
                safeDistance += 300;
            }

            const angleToStart = Math.atan2(start[1] - zoneCenter[1], start[0] - zoneCenter[0]);
            const angleToEnd = Math.atan2(end[1] - zoneCenter[1], end[0] - zoneCenter[0]);
            let detourAngle = (angleToStart + angleToEnd) / 2;

            const crossProduct = (end[0] - zoneCenter[0]) * (start[1] - zoneCenter[1]) - (end[1] - zoneCenter[1]) * (start[0] - zoneCenter[0]);
            if (crossProduct > 0) {
                detourAngle += Math.PI / 2;
            } else {
                detourAngle -= Math.PI / 2;
            }

            let waypointLat = zoneCenter[0] + (safeDistance / 1000 / 6371) * (180 / Math.PI) * Math.cos(detourAngle);
            let waypointLng = zoneCenter[1] + (safeDistance / 1000 / 6371) * (180 / Math.PI) * Math.sin(detourAngle) / Math.cos(zoneCenter[0] * Math.PI / 180);
            let waypoint = [waypointLat, waypointLng];

            let firstLeg = createCurvedRoute(start, waypoint);
            let secondLeg = createCurvedRoute(waypoint, end);
            route = firstLeg.slice(0, -1).concat(secondLeg);

            if (checkNoFlyZoneRoute(route)) {
                detourAngle += crossProduct > 0 ? Math.PI / 3 : -Math.PI / 3;
                safeDistance += 100;
                const waypoint2Lat = zoneCenter[0] + (safeDistance / 1000 / 6371) * (180 / Math.PI) * Math.cos(detourAngle);
                const waypoint2Lng = zoneCenter[1] + (safeDistance / 1000 / 6371) * (180 / Math.PI) * Math.sin(detourAngle) / Math.cos(zoneCenter[0] * Math.PI / 180);
                const waypoint2 = [waypoint2Lat, waypoint2Lng];

                firstLeg = createCurvedRoute(start, waypoint);
                const middleLeg = createCurvedRoute(waypoint, waypoint2);
                secondLeg = createCurvedRoute(waypoint2, end);
                route = firstLeg.slice(0, -1).concat(middleLeg.slice(0, -1), secondLeg);
            }

            return route;
        }

        function createSafeRouteAroundOtherDrones(start, end, conflictingDrone) {
            let route = createCurvedRoute(start, end);
            const midPoint = [(start[0] + end[0]) / 2, (start[1] + end[1]) / 2];
            let closestPoint = null;
            let minDist = Infinity;

            if (conflictingDrone) {
                for (const point of conflictingDrone.route) {
                    const dist = calculateDistance(point, midPoint) * 1000;
                    if (dist < minDist) {
                        minDist = dist;
                        closestPoint = point;
                    }
                }

                const angleToStart = Math.atan2(start[1] - closestPoint[1], start[0] - closestPoint[0]);
                let detourAngle = angleToStart + (Math.random() > 0.5 ? Math.PI / 2 : -Math.PI / 2);
                const safeDistance = 300;

                let waypointLat = closestPoint[0] + (safeDistance / 1000 / 6371) * (180 / Math.PI) * Math.cos(detourAngle);
                let waypointLng = closestPoint[1] + (safeDistance / 1000 / 6371) * (180 / Math.PI) * Math.sin(detourAngle) / Math.cos(closestPoint[0] * Math.PI / 180);
                let waypoint = [waypointLat, waypointLng];

                let firstLeg = createCurvedRoute(start, waypoint);
                let secondLeg = createCurvedRoute(waypoint, end);
                route = firstLeg.slice(0, -1).concat(secondLeg);
            }

            return route;
        }

        function createEnergyEfficientRoute(start, end) {
            let route = createCurvedRoute(start, end);
            const windDirection = astanaWindDirection || 0;
            const routeDirection = calculateDirection(start, end);
            const windEffect = Math.cos((windDirection - routeDirection) * Math.PI / 180);
            
            if (windEffect < 0) {
                const midLat = (start[0] + end[0]) / 2 + (Math.random() - 0.5) * 0.01;
                const midLng = (start[1] + end[1]) / 2 + (Math.random() - 0.5) * 0.01;
                const firstLeg = createCurvedRoute(start, [midLat, midLng]);
                const secondLeg = createCurvedRoute([midLat, midLng], end);
                route = firstLeg.slice(0, -1).concat(secondLeg);
            }

            return route;
        }

        let astanaWindSpeed = 0;
        let astanaWindDirection = 0;
        let astanaWeatherDanger = false;
        let astanaWeatherCondition = 'clear';
        let weatherWarningMarker = null;

        function updateWeather() {
            const windSpeed = 5 + Math.random() * 15;
            astanaWindSpeed = windSpeed;
            astanaWindDirection = Math.random() * 360;
            const weatherConditions = ['clear', 'cloudy', 'rain', 'storm'];
            const condition = weatherConditions[Math.floor(Math.random() * weatherConditions.length)];
            astanaWeatherCondition = condition;
            astanaWeatherDanger = windSpeed > 15 || condition === 'rain' || condition === 'storm';

            let weatherEmoji = '☀️';
            if (condition === 'cloudy') weatherEmoji = '☁️';
            if (condition === 'rain') weatherEmoji = '🌧️';
            if (condition === 'storm') weatherEmoji = '⚡';

            document.getElementById('weatherInfo').innerHTML = `
                Астана: ${weatherEmoji} ${condition}, 💨 Жел: ${windSpeed.toFixed(1)} м/с, Бағыт: ${astanaWindDirection.toFixed(0)}°
            `;
            document.getElementById('weatherInfo').classList.remove('weather-safe', 'weather-danger');
            document.getElementById('weatherInfo').classList.add(astanaWeatherDanger ? 'weather-danger' : 'weather-safe');

            if (weatherWarningMarker) {
                map.removeLayer(weatherWarningMarker);
            }
            if (astanaWeatherDanger) {
                weatherWarningMarker = L.marker([51.1494, 71.4491], {
                    icon: L.divIcon({
                        html: `<span class="weather-warning-icon">${weatherEmoji}</span>`,
                        className: 'weather-warning'
                    })
                }).addTo(map)
                .bindPopup(`Қауіпті Ауа-райы: ${condition}, Жел: ${windSpeed.toFixed(1)} м/с`);
            }

            setTimeout(updateWeather, 300000);
        }
        updateWeather();

        let simulatedDrones = [];
        function createSimulatedDrones() {
            const simRoutes = [
                { start: [51.1200, 71.4400], end: [51.0890, 71.4118] },
                { start: [51.1281, 71.4460], end: [51.1358, 71.4043] },
                { start: [51.1694, 71.4491], end: [51.1282, 71.4307] }
            ];
            simRoutes.forEach((route, i) => {
                const simRoute = createCurvedRoute(route.start, route.end);
                const simulatedDrone = {
                    id: `Sim-${i + 1}`,
                    marker: L.marker(route.start, {
                        icon: L.divIcon({ html: '<span>🛩️</span>', className: 'drone-icon' })
                    }).addTo(map),
                    route: simRoute,
                    step: 0,
                    steps: 1500,
                    start: route.start,
                    end: route.end,
                    altitude: 100 + Math.random() * 200,
                    status: 'Ұшып жатыр',
                    baseSpeed: 20,
                    speed: 20
                };
                simulatedDrone.speed = calculateDroneSpeed(simulatedDrone);
                simulatedDrones.push(simulatedDrone);
            });
            animateSimulatedDrones();
        }

        function animateSimulatedDrones() {
            let anyActive = false;
            simulatedDrones.forEach(drone => {
                if (drone.status === 'Ұшып жатыр' && drone.step <= drone.steps) {
                    anyActive = true;
                    const t = drone.step / drone.steps;
                    const routeIndex = Math.floor(t * (drone.route.length - 1));
                    const nextIndex = Math.min(routeIndex + 1, drone.route.length - 1);
                    const newPos = drone.route[routeIndex];
                    const nextPos = drone.route[nextIndex];
                    drone.marker.setLatLng(newPos);
                    const angle = calculateDirection(newPos, nextPos);
                    drone.speed = calculateDroneSpeed(drone);
                    drone.marker.setIcon(L.divIcon({
                        html: `<span style="transform: rotate(${angle}deg); display: inline-block;">🛩️</span>`,
                        className: 'drone-icon'
                    }));
                    drone.step++;
                    if (drone.step > drone.steps) {
                        drone.status = 'Тамамдалды';
                        map.removeLayer(drone.marker);
                    }
                }
            });
            if (anyActive) {
                setTimeout(animateSimulatedDrones, 300);
            } else {
                simulatedDrones = [];
                createSimulatedDrones();
            }
        }
        createSimulatedDrones();

        var drones = [];
        var registeredDrones = 0;
        var alerts = [];

        function addAlert(message) {
            alerts.push({ message: message, timestamp: new Date() });
            updateAlertPanel();
        }

        function updateAlertPanel() {
            const alertList = document.getElementById('alertList');
            alertList.innerHTML = '';
            alerts.forEach(alert => {
                const li = document.createElement('li');
                li.textContent = `[${alert.timestamp.toLocaleTimeString()}] ${alert.message}`;
                alertList.appendChild(li);
            });
            alerts = alerts.filter(alert => (new Date() - alert.timestamp) < 5 * 60 * 1000);
        }

        function playAlertSound() {
            const audio = new Audio('https://www.soundjay.com/buttons/beep-01a.mp3');
            audio.play().catch(() => console.log('Дыбыс ойнatıла алмады'));
        }

        function updateStatusPanel() {
            document.getElementById('totalDrones').innerText = registeredDrones;
            document.getElementById('activeFlights').innerText = drones.filter(d => d.status === 'Ұшып жатыр').length;
        }

        function loadDrones() {
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>/api', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_drones' })
            })
                .then(response => response.json())
                .then(data => {
                    registeredDrones = data.length;
                    updateStatusPanel();
                });
        }
        loadDrones();

        function registerDrone() {
            $droneModel = document.getElementById('droneModel').value;
            $droneSerial = document.getElementById('droneSerial').value;
            $pilotName = document.getElementById('pilotName').value;
            $pilotContact = document.getElementById('pilotContact').value;
            $currentBase = document.getElementById('currentBase').value;

            if (drones.some(d => d.serial === $droneSerial)) {
                alert('Бұл серия нөміріне ие дрон алдында тіркелген!');
                return;
            }

            if ($droneModel && $droneSerial && $pilotName && $pilotContact) {
                fetch('<?php echo $_SERVER['PHP_SELF']; ?>/api', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'register', model: $droneModel, serial: $droneSerial, pilot_name: $pilotName, pilot_contact: $pilotContact, current_base: $currentBase })
                })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message || data.error);
                        if (data.message) {
                            registeredDrones++;
                            updateStatusPanel();
                            document.getElementById('droneModel').value = '';
                            document.getElementById('droneSerial').value = '';
                            document.getElementById('pilotName').value = '';
                            document.getElementById('pilotContact').value = '';
                            bootstrap.Modal.getInstance(document.getElementById('registerModal')).hide();
                            loadDrones();
                        }
                    });
            } else {
                alert('Өрістерді толтырыңыз.');
            }
        }

        function calculateDistance(coord1, coord2) {
            const R = 6371;
            const dLat = (coord2[0] - coord1[0]) * Math.PI / 180;
            const dLon = (coord2[1] - coord1[1]) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(coord1[0] * Math.PI / 180) * Math.cos(coord2[0] * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        function calculateDuration(distance, speed) {
            const speedInKmPerMin = speed / 60;
            return distance / speedInKmPerMin;
        }

        function createCurvedRoute(start, end) {
            const midLat = (start[0] + end[0]) / 2 + (Math.random() - 0.5) * 0.005;
            const midLng = (start[1] + end[1]) / 2 + (Math.random() - 0.5) * 0.005;
            const points = [];
            for (let t = 0; t <= 1; t += 0.01) {
                const lat = (1-t)*(1-t)*start[0] + 2*(1-t)*t*midLat + t*t*end[0];
                const lng = (1-t)*(1-t)*start[1] + 2*(1-t)*t*midLng + t*t*end[1];
                points.push([lat, lng]);
            }
            return points;
        }

        function checkNoFlyZoneRoute(route) {
            for (const point of route) {
                for (const zone of noFlyZones) {
                    const dist = calculateDistance(point, zone.center) * 1000;
                    if (dist < zone.radius) {
                        return true;
                    }
                }
            }
            return false;
        }

        function checkFlightCollision(newRoute, newAltitude) {
            const collisionCircles = [];
            let result = { collision: false, futureCollision: false, point: null, conflictingDrone: null };

            const allDrones = [...drones, ...simulatedDrones];
            for (const drone of allDrones) {
                if (drone.status !== 'Ұшып жатыр') continue;

                const altitudeDiff = Math.abs(newAltitude - drone.altitude);
                if (altitudeDiff > 50) continue;

                for (let i = 0; i < newRoute.length; i++) {
                    const newPoint = newRoute[i];
                    const newStep = i / newRoute.length;
                    const newTime = newStep * calculateDuration(calculateDistance(newRoute[0], newRoute[newRoute.length - 1]), drone.speed) * 60;

                    for (let j = 0; j < drone.route.length; j++) {
                        const dronePoint = drone.route[j];
                        const droneStep = drone.step + j / drone.route.length;
                        const droneTime = droneStep * calculateDuration(calculateDistance(drone.start, drone.end), drone.speed) * 60;

                        const dist = calculateDistance(newPoint, dronePoint) * 1000;
                        if (dist < 100) {
                            const timeDiff = Math.abs(newTime - droneTime);
                            if (timeDiff < 30) {
                                const circle = L.circle(newPoint, {
                                    radius: 100,
                                    color: 'red',
                                    fillOpacity: 0.2,
                                    className: 'alert-zone'
                                }).addTo(map);
                                collisionCircles.push(circle);
                                setTimeout(() => map.removeLayer(circle), 5000);
                                result.collision = true;
                                result.point = newPoint;
                                result.conflictingDrone = drone;
                                return result;
                            } else if (timeDiff < 60) {
                                result.futureCollision = true;
                                result.point = newPoint;
                                result.conflictingDrone = drone;
                            }
                        }
                    }
                }
            }
            return result;
        }

        function calculateDirection(start, end) {
            const dLat = end[0] - start[0];
            const dLng = end[1] - start[1];
            const angle = Math.atan2(dLng, dLat) * 180 / Math.PI;
            return (angle + 360) % 360;
        }

        function calculateDroneSpeed(drone) {
            let speed = drone.baseSpeed;
            if (astanaWeatherDanger) {
                speed *= 0.7;
            }
            if (astanaWindSpeed > 10) {
                const windEffect = 1 - (astanaWindSpeed - 10) / 20;
                speed *= Math.max(0.5, windEffect);
            }
            const altitudeFactor = 1 - (drone.altitude - 100) / 1000;
            speed *= Math.max(0.6, altitudeFactor);
            const batteryFactor = drone.battery < 30 ? 0.6 : drone.battery < 50 ? 0.8 : 1;
            speed *= batteryFactor;
            return Math.max(5, speed);
        }

        function calculateBatteryConsumption(drone) {
            const baseConsumption = 0.1;
            const distanceFactor = drone.distance / 10;
            const altitudeFactor = drone.altitude / 100;
            const speedFactor = drone.speed / 20;
            const weatherFactor = astanaWeatherDanger ? 1.5 : 1;
            return (baseConsumption + distanceFactor + altitudeFactor + speedFactor) * weatherFactor / 300;
        }

        function chargeBattery(drone) {
            if (drone.status === 'Тамамдалды' && drone.battery < 100) {
                drone.battery = Math.min(100, drone.battery + 0.5);
                fetch('<?php echo $_SERVER['PHP_SELF']; ?>/api', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'update_battery', drone_id: drone.dbId, battery: drone.battery })
                });
            }
        }

        function animateDrones() {
            const now = new Date();
            let anyActive = false;
            drones.forEach(drone => {
                if (drone.status === 'Ұшып жатыр' && drone.step <= drone.steps) {
                    anyActive = true;
                    const t = drone.step / drone.steps;
                    const routeIndex = Math.floor(t * (drone.route.length - 1));
                    const nextIndex = Math.min(routeIndex + 1, drone.route.length - 1);
                    const newPos = drone.route[routeIndex];
                    const nextPos = drone.route[nextIndex];
                    drone.marker.setLatLng(newPos);
                    const angle = calculateDirection(newPos, nextPos);

                    drone.speed = calculateDroneSpeed(drone);
                    drone.duration = calculateDuration(drone.distance, drone.speed);

                    const batteryConsumption = calculateBatteryConsumption(drone);
                    drone.battery = Math.max(0, drone.battery - batteryConsumption);

                    if (drone.battery <= 10) {
                        drone.status = 'Қайтару Басталды';
                        const nearestBase = bases.reduce((a, b) => calculateDistance(a.coords, newPos) < calculateDistance(b.coords, newPos) ? a : b);
                        drone.end = nearestBase.coords;
                        drone.route = createCurvedRoute(newPos, drone.end);
                        drone.steps = 1500;
                        drone.step = 0;
                        drone.endBase = nearestBase.name;
                        alert(`Дрон ${drone.id} батарея деңгейі сынға түсті (%${drone.battery.toFixed(0)}) сондықтан ең жақын негізге ${nearestBase.name} бағытталды.`);
                        addAlert(`Дрон ${drone.id} батарея деңгейі сынға түсті (%${drone.battery.toFixed(0)}).`);
                    }

                    drone.marker.setIcon(L.divIcon({
                        html: `<span style="transform: rotate(${angle}deg); display: inline-block;">✈️</span>`,
                        className: `drone-icon drone-icon-${drone.altitude < 200 ? 'low' : 'high'}`
                    }));
                    drone.marker.bindTooltip(`Батарея: ${drone.battery.toFixed(0)}% | Жылдамдық: ${drone.speed.toFixed(1)}км/с`, { permanent: true, direction: 'top', offset: [0, -20] });
                    const elapsed = (now - drone.startTime) / 1000 / 60;
                    const remainingTime = (drone.duration - elapsed).toFixed(2);
                    drone.marker.bindPopup(`Дрон ${drone.id}<br>Координата: ${newPos[0].toFixed(4)}, ${newPos[1].toFixed(4)}<br>Биіктік: ${drone.altitude.toFixed(0)}м<br>Батарея: ${drone.battery.toFixed(0)}%<br>Жылдамдық: ${drone.speed.toFixed(1)}км/с<br>Қалған Уақыт: ${remainingTime > 0 ? remainingTime : 0}мин`);
                    drone.step++;
                    if (drone.step > drone.steps) {
                        drone.status = 'Тамамдалды';
                        fetch('<?php echo $_SERVER['PHP_SELF']; ?>/api', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'update_base', drone_id: drone.dbId, new_base: drone.endBase })
                        }).then(() => updateHistoryTable());
                        fetch('<?php echo $_SERVER['PHP_SELF']; ?>/api', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'update_battery', drone_id: drone.dbId, battery: drone.battery })
                        });
                    }
                } else if (drone.status === 'Тамамдалды') {
                    chargeBattery(drone);
                }
                updateFlightTable();
            });
            updateStatusPanel();
            if (anyActive) {
                setTimeout(animateDrones, 300);
            }
        }

        function updateFlightTable() {
            const table = document.getElementById('flightTable');
            table.innerHTML = '';
            drones.forEach(drone => {
                if (drone.status === 'Ұшып жатыр' || drone.status === 'Қайтару Басталды' || drone.status === 'Тамамдалды') {
                    const row = table.insertRow();
                    row.innerHTML = `
                        <td>${drone.id}</td>
                        <td>${drone.startBase} -> ${drone.endBase}</td>
                        <td>${drone.distance.toFixed(2)} км</td>
                        <td>${drone.duration.toFixed(2)} мин</td>
                        <td>${drone.altitude.toFixed(0)} м</td>
                        <td>${drone.battery.toFixed(0)}%</td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar" style="width: ${(drone.step / drone.steps * 100).toFixed(0)}%"></div>
                            </div>
                        </td>
                        <td>${drone.status}</td>
                        <td class="${drone.clearanceStatus === 'Мақұлданды' ? 'status-approved' : 'status-pending'}">${drone.clearanceStatus || 'Күтілуде'}</td>
                    `;
                }
            });
        }

        function updateHistoryTable() {
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>/api', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_flights' })
            }).then(r => r.json()).then(flights => {
                const table = document.getElementById('historyTable');
                table.innerHTML = '';
                flights.forEach(flight => {
                    const row = table.insertRow();
                    row.innerHTML = `
                        <td>${flight.drone_id}</td>
                        <td>${flight.start_base} -> ${flight.end_base}</td>
                        <td>${flight.distance.toFixed(2)} км</td>
                        <td>${new Date(flight.start_time).toLocaleString()}</td>
                    `;
                });
            });
        }

        window.onload = () => {
            updateHistoryTable();
            setInterval(updateHistoryTable, 60000);
        };
    </script>
</body>
</html>