(function () {
    const DEFAULT_CENTER = [45.9432, 24.9668];
    const DEFAULT_ZOOM = 5;

    function escapeHtml(value) {
        return String(value).replace(/[&<>"]|'/g, (character) => {
            const escapeMap = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            };

            return escapeMap[character] || character;
        });
    }

    function addTileLayer(map) {
        return L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
    }

    function setInputValues(latitudeInput, longitudeInput, latitude, longitude) {
        if (latitudeInput) {
            latitudeInput.value = Number(latitude).toFixed(6);
        }

        if (longitudeInput) {
            longitudeInput.value = Number(longitude).toFixed(6);
        }
    }

    function setupPlantsOverviewMap({
        mapId,
        plants,
        fallbackCenter = DEFAULT_CENTER,
        fallbackZoom = DEFAULT_ZOOM
    }) {
        if (typeof L === 'undefined') {
            throw new Error('Leaflet nu este încărcat.');
        }

        const mapElement = document.getElementById(mapId);
        if (!mapElement) {
            return null;
        }

        const map = L.map(mapId, { scrollWheelZoom: false }).setView(fallbackCenter, fallbackZoom);
        addTileLayer(map);

        const markerBounds = [];

        plants.forEach((plant) => {
            if (!plant.has_coordinates) {
                return;
            }

            const marker = L.marker([plant.latitude, plant.longitude]).addTo(map);
            const popupHtml = `
                <div class="map-popup">
                    <strong>${escapeHtml(plant.popup_title || 'Centrală')}</strong>
                    <span>${escapeHtml(plant.popup_subtitle || 'Țară nespecificată')}</span>
                    <span class="map-popup-meta">${escapeHtml(plant.coordinates_label || '')}</span>
                    <a class="button secondary" href="${escapeHtml(plant.edit_url || '#')}">Mergi la editare</a>
                </div>
            `;

            marker.bindPopup(popupHtml);
            marker.on('click', () => {
                window.location.href = plant.edit_url;
            });
            markerBounds.push([plant.latitude, plant.longitude]);
        });

        if (markerBounds.length > 0) {
            map.fitBounds(markerBounds, { padding: [30, 30] });
        }

        return map;
    }

    function setupCoordinatePickerMap({
        mapId,
        latitudeInputId,
        longitudeInputId,
        statusId,
        countryInputId,
        previewUrl,
        latitude,
        longitude,
        fallbackCenter = DEFAULT_CENTER,
        fallbackZoom = DEFAULT_ZOOM,
        zoom = 6
    }) {
        if (typeof L === 'undefined') {
            throw new Error('Leaflet nu este încărcat.');
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
        addTileLayer(map);

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
            const response = await fetch(previewUrl, {
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
            setInputValues(latitudeInput, longitudeInput, payload.latitude, payload.longitude);
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

    window.PlantMap = {
        setupPlantsOverviewMap,
        setupCoordinatePickerMap
    };
})();