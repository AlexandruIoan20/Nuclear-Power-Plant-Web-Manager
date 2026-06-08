import { API_BASE } from '../../config/api.config.js';

export function setupCoordinatePickerMap({
    mapId,
    latitudeInputId,
    longitudeInputId,
    statusId,
    countryInputId,
    latitude,
    longitude,
    fallbackCenter = [45.9432, 24.9668],
    fallbackZoom = 5,
    zoom = 6
}) {
    if (typeof L === 'undefined') {
        console.error('Leaflet nu este încărcat.');
        return null;
    }

    const mapElement = document.getElementById(mapId);
    if (!mapElement) {
        return null;
    }

    const latitudeInput = document.getElementById(latitudeInputId);
    const longitudeInput = document.getElementById(longitudeInputId);
    const countryInput = countryInputId ? document.getElementById(countryInputId) : null;
    const statusElement = statusId ? document.getElementById(statusId) : null;

    if (latitudeInput) latitudeInput.step = 'any';
    if (longitudeInput) longitudeInput.step = 'any';

    const initialLatitude = latitude ?? (latitudeInput ? Number(latitudeInput.value) : null);
    const initialLongitude = longitude ?? (longitudeInput ? Number(longitudeInput.value) : null);
    const hasInitialCoordinates = Number.isFinite(Number(initialLatitude)) && Number.isFinite(Number(initialLongitude));
    const startCenter = hasInitialCoordinates ? [Number(initialLatitude), Number(initialLongitude)] : fallbackCenter;
    const startZoom = hasInitialCoordinates ? zoom : fallbackZoom;

    const map = L.map(mapId, { scrollWheelZoom: false }).setView(startCenter, startZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    function setStatus(message) {
        if (statusElement) {
            statusElement.textContent = message;
        }
    }

    function setMarker(nextLatitude, nextLongitude, shouldPan = true) {
        const nextCoordinates = [nextLatitude, nextLongitude];
        if (!marker) {
            marker = L.marker(nextCoordinates, { draggable: false }).addTo(map);
        } else {
            marker.setLatLng(nextCoordinates);
        }
        if (shouldPan) {
            map.panTo(nextCoordinates);
        }
    }

    async function sendCoordinates(nextLatitude, nextLongitude) {
        const response = await fetch(`${API_BASE}/power-plants/coordinates-preview`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                latitude: nextLatitude,
                longitude: nextLongitude
            })
        });

        const result = await response.json();

        if (!response.ok || result.status !== 'success') {
            throw new Error(result.message || 'Coordonate invalide.');
        }

        const payload = result.data || {};

        if (latitudeInput && payload.latitude !== undefined) {
            latitudeInput.value = Number(payload.latitude).toFixed(6);
        }
        if (longitudeInput && payload.longitude !== undefined) {
            longitudeInput.value = Number(payload.longitude).toFixed(6);
        }
        if (countryInput && payload.country) {
            countryInput.value = payload.country;
        }

        setMarker(payload.latitude, payload.longitude, false);
        setStatus(payload.message || payload.coordinates_label || 'Locație validată de backend.');
    }

    if (hasInitialCoordinates) {
        setMarker(Number(initialLatitude), Number(initialLongitude), false);
        setStatus('Locație încărcată din backend.');
    } else {
        setStatus('Dă click pe hartă pentru a seta locația.');
    }

    map.on('click', async (event) => {
        try {
            setStatus('Validare în curs prin backend...');
            const wrappedCoordinates = event.latlng.wrap();
            await sendCoordinates(wrappedCoordinates.lat, wrappedCoordinates.lng);
        } catch (error) {
            setStatus(error.message);
        }
    });

    return { map };
}
