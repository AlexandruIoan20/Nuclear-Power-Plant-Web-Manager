import { api } from '../../core/api.js';
import { logger } from '../../core/logger.js';

export function setupCoordinatePickerMap({
    mapId,
    latitudeInputId,
    longitudeInputId,
    statusId,
    countryInputId,
    latitude,
    longitude,
    onPreview,
    fallbackCenter = [45.9432, 24.9668],
    fallbackZoom = 5,
    zoom = 6
}) {
    if (typeof L === 'undefined') {
        logger.error('Leaflet nu este încărcat.');
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

    const rawLat = latitudeInput ? latitudeInput.value.trim() : '';
    const rawLon = longitudeInput ? longitudeInput.value.trim() : '';
    const initialLatitude = latitude ?? (rawLat !== '' ? Number(rawLat) : null);
    const initialLongitude = longitude ?? (rawLon !== '' ? Number(rawLon) : null);
    const hasInitialCoordinates = initialLatitude !== null && initialLongitude !== null
        && Number.isFinite(initialLatitude) && Number.isFinite(initialLongitude);
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
        const result = await api.post('/power-plants/coordinates-preview', {
            latitude: nextLatitude,
            longitude: nextLongitude
        });

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
        setStatus(payload.message || payload.coordinatesLabel || 'Locație validată de backend.');

        if (typeof onPreview === 'function') {
            onPreview(payload);
        }
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
