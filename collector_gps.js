console.log("Collector GPS JS loaded");

function sendLocation() {

    if (!navigator.geolocation) {
        console.error("Geolocation not supported");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (position) {

            // ✅ DEFINE VARIABLES HERE (THIS WAS MISSING)
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            console.log("Sending GPS:", lat, lng);

            fetch("update_location.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `latitude=${lat}&longitude=${lng}`
            })
            .then(res => res.text())
            .then(data => console.log("Server response:", data))
            .catch(err => console.error("Fetch error:", err));
        },
        function (error) {
            console.error("GPS ERROR:", error.message);
        },
        {
            enableHighAccuracy: false,
            timeout: 30000,
            maximumAge: 60000
        }
    );
}

// 🚀 run immediately
sendLocation();

// 🔁 repeat every 15 seconds
setInterval(sendLocation, 15000);
