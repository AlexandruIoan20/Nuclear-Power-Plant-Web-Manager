import { powerPlantService } from '../../services/powerPlantService.js'; 
import { SoilType, WaterSourceType } from '../../config/enums.js'; 
import { loadSelect } from '../../ui/selectLoader.js';
import { getQueryParam } from '../../utils/urlHelper.js';
import { showError, showSuccess, clearStatus } from '../../ui/showMessage.js';
import { GeologicalDataRequestDTO } from '../../dto/GeologicalDataRequestDTO.js'; 
import { saveHeaderState, getHeaderState } from '../../ui/form-header/formHeaderState.js'; 
import { setupCoordinatePickerMap } from '../../ui/map/coordinatePicker.js'; 
import { API_BASE } from '../../config/api.config.js'; 
import { logger } from '../../core/logger.js';

const plantId = getQueryParam("id"); 
const urlGeologicalId = getQueryParam("geologicalId");
const geologicalId = urlGeologicalId ?? getHeaderState().geologicalId ?? null; 

loadSelect("soil_type", SoilType); 
loadSelect("water_source_type", WaterSourceType); 

async function loadCountryList() {
    const datalist = document.getElementById('country-list');
    if (!datalist) return;
    try {
        const response = await fetch(`${API_BASE}/countries`);
        if (!response.ok) throw new Error('Eroare la preluarea listei de țări.');
        const countries = await response.json();
        countries.forEach(country => {
            const option = document.createElement('option');
            option.value = country;
            datalist.appendChild(option);
        });
    } catch (error) {
        logger.error('Nu s-au putut încărca țările:', error);
    }
}

document.addEventListener("DOMContentLoaded", async () => { 
    const form = document.getElementById("geological-form"); 
    const statusElement = document.getElementById("status-message"); 

    if(!plantId) { 
        showError(statusElement, "ID centrala lipsa in URL"); 
        return; 
    }

    loadCountryList();

    let initialLatitude = null;
    let initialLongitude = null;
    let isEdit = !!geologicalId;

    if (!isEdit) {
        try {
            const checkResponse = await powerPlantService.getGeological(plantId);
            if (checkResponse.data && checkResponse.data.id) {
                saveHeaderState({ geologicalId: checkResponse.data.id });
                window.history.replaceState({}, '', `?id=${plantId}&geologicalId=${checkResponse.data.id}`);
                isEdit = true;
            }
        } catch {
        }
    }

    if (isEdit) {
        try {
            const response = await powerPlantService.getGeological(plantId);
            const d = response.data;
            document.getElementById("country").value = d.country ?? "";
            document.getElementById("latitude").value = d.latitude ?? "";
            document.getElementById("longitude").value = d.longitude ?? "";
            document.getElementById("soil_type").value = d.soilType ?? "";
            document.getElementById("water_source_type").value = d.waterSourceType ?? "";
            document.getElementById("seismic_stability").value = d.seismicStability ?? "";
            document.getElementById("flood_risk").value = d.floodRisk ?? "";
            document.getElementById("groundwater_level").value = d.groundwaterLevel ?? "";
            document.getElementById("water_proximity").value = d.waterProximity ?? "";
            document.getElementById("water_flow_rate").value = d.waterFlowRate ?? "";
            document.getElementById("population_density").value = d.populationDensity ?? "";
            document.getElementById("transport_infrastructure_score").value = d.transportInfrastructureScore ?? "";
            document.getElementById("geological_risk_score").value = d.geologicalRiskScore ?? "";
            initialLatitude = d.latitude;
            initialLongitude = d.longitude;
        } catch (error) {
            logger.error(error.message);
            showError(statusElement, "Eroare la încărcarea datelor geologice.");
            return; 
        }
    }

    function populateGeologicalPreview(payload) {
        const fields = {
            soil_type: payload.soilType,
            water_source_type: payload.waterSourceType,
            seismic_stability: payload.seismicStability,
            flood_risk: payload.floodRisk,
            groundwater_level: payload.groundwaterLevel,
            water_proximity: payload.waterProximity,
            water_flow_rate: payload.waterFlowRate,
            population_density: payload.populationDensity,
            transport_infrastructure_score: payload.transportInfrastructureScore,
        };
        for (const [id, value] of Object.entries(fields)) {
            const el = document.getElementById(id);
            if (el && value !== null && value !== undefined) {
                el.value = value;
            }
        }
    }

    const coordinatePicker = setupCoordinatePickerMap({
        mapId: 'geological-map',
        latitudeInputId: 'latitude',
        longitudeInputId: 'longitude',
        statusId: 'geological-map-status',
        countryInputId: 'country',
        latitude: initialLatitude,
        longitude: initialLongitude,
        onPreview: populateGeologicalPreview,
        fallbackCenter: [45.9432, 24.9668],
        fallbackZoom: 5,
        zoom: 6
    });
    if (!coordinatePicker) {
        logger.warning('Harta nu a putut fi inițializată (Leaflet lipsă sau element lipsă).');
    }

    if(!isEdit) { 
        form.addEventListener("submit", async (e) => { 
            e.preventDefault(); 
            clearStatus(statusElement); 

            const dto = GeologicalDataRequestDTO({ 
                soilType: document.getElementById("soil_type").value,
                waterSourceType: document.getElementById("water_source_type").value,
                seismicStability: document.getElementById("seismic_stability").value,
                floodRisk: document.getElementById("flood_risk").value,
                groundwaterLevel: document.getElementById("groundwater_level").value,
                waterProximity: document.getElementById("water_proximity").value,
                waterFlowRate: document.getElementById("water_flow_rate").value,
                populationDensity: document.getElementById("population_density").value,
                transportInfrastructureScore: document.getElementById("transport_infrastructure_score").value,
                geologicalRiskScore: document.getElementById("geological_risk_score").value,
                country: document.getElementById("country").value,
                latitude: document.getElementById("latitude").value,
                longitude: document.getElementById("longitude").value,
            }); 

            try { 
                const response = await powerPlantService.createGeological(dto, plantId); 
                saveHeaderState({ geologicalId: response.geologicalId }); 
                showSuccess(statusElement, "Datele au fost salvate cu succes!"); 

                window.location.href = `/pages/power-plants/technical.html?id=${response.plantId}`;

                console.log({ response }); 

                form.reset(); 
            } catch(error) { 
                logger.error(error.message); 
                showError(statusElement, "Eroare la adaugarea informatiilor despre centrala.")  
            }
        }); 
    } else { 
        form.addEventListener("submit", async(e) => { 
            e.preventDefault(); 
            clearStatus(statusElement); 

            const dto = GeologicalDataRequestDTO({ 
                soilType: document.getElementById("soil_type").value,
                waterSourceType: document.getElementById("water_source_type").value,
                seismicStability: document.getElementById("seismic_stability").value,
                floodRisk: document.getElementById("flood_risk").value,
                groundwaterLevel: document.getElementById("groundwater_level").value,
                waterProximity: document.getElementById("water_proximity").value,
                waterFlowRate: document.getElementById("water_flow_rate").value,
                populationDensity: document.getElementById("population_density").value,
                transportInfrastructureScore: document.getElementById("transport_infrastructure_score").value,
                geologicalRiskScore: document.getElementById("geological_risk_score").value,
                country: document.getElementById("country").value,
                latitude: document.getElementById("latitude").value,
                longitude: document.getElementById("longitude").value,
            }); 

            try { 
                await powerPlantService.updateGeological(dto, plantId); 
                showSuccess(statusElement, "Datele au fost actualizate cu succes!"); 
            } catch(error) { 
                logger.error(error.message); 
                showError(statusElement, "Eroare la actualizarea informatiilor despre centrala.") 
            }
        })
    }
}) 
