<?php
session_start();
include __DIR__ . "/db_connect.php";

/* Admin only */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    exit("Unauthorized");
}

/* Latest location per collector */
$result = $conn->query("
    SELECT u.fullname, cl.lat, cl.lng, cl.created_at
    FROM collector_locations cl
    JOIN users u ON cl.collector_id = u.id
    JOIN (
        SELECT collector_id, MAX(created_at) AS last_time
        FROM collector_locations
        GROUP BY collector_id
    ) latest
      ON cl.collector_id = latest.collector_id
     AND cl.created_at = latest.last_time
");

/* Collect data */
$collectors = [];
while ($r = $result->fetch_assoc()) {
    $collectors[] = $r;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Live Collector Map</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="dashboard_theme.css">
<style>
body{font-family:"Manrope","Segoe UI",Arial,sans-serif;margin:0}
#map { height: 80vh; width:100%; }
</style>
</head>
<body>

<?php $activePage = 'collector_map'; include __DIR__ . "/admin_layout_start.php"; ?>
<h3 style="margin-top:0;">Live Collector Map</h3>
<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
const map = L.map('map').setView([20.5937,78.9629], 5);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

// store markers
const markers = {};

// fetch live locations every 5 sec
function fetchLiveLocations() {
    fetch("fetch_live_locations.php")
        .then(res => res.json())
        .then(data => {
            const now = new Date();

            data.forEach(c => {
                const last = new Date(c.created_at);
                const diffSec = (now - last) / 1000;

                // ONLINE / OFFLINE logic
                const isOnline = diffSec < 60; // 1 minute
                const statusText = isOnline ? "🟢 Online" : "🔴 Offline";

                const popupHtml = `
                    <b>${c.fullname}</b><br>
                    ${statusText}<br>
                    Last update: ${c.created_at}
                `;

                if (!markers[c.id]) {
                    // create marker first time
                    markers[c.id] = L.marker([c.lat, c.lng], {
                        riseOnHover: true
                    }).addTo(map)
                      .bindPopup(popupHtml);
                } else {
                    // smooth move (no reload)
                    markers[c.id].setLatLng([c.lat, c.lng]);
                    markers[c.id].getPopup().setContent(popupHtml);
                }
            });
        })
        .catch(err => console.error("Map fetch error:", err));
}

// first load
fetchLiveLocations();

// repeat every 5 seconds
setInterval(fetchLiveLocations, 5000);
</script>

<?php include __DIR__ . "/admin_layout_end.php"; ?>
</body>
</html>
