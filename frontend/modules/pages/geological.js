import { powerPlantService } from '../services/powerPlantService.js'; 
import { SoilType, WaterSourceType } from '../config/enums.js'; 
import { loadSelect } from '../ui/selectLoader.js';
import { getQueryParam } from '../utils/urlHelper.js';
import { showError, showSuccess, clearStatus } from '../ui/showMessage.js';
import { GeologicalDataRequestDTO } from '../dto/GeologicalDataRequestDTO.js'; 

const plantId = getQueryParam("id"); 
const geologicalId = getQueryParam("geologicalId"); 

loadSelect("soil_type", SoilType); 
loadSelect("water_source_type", WaterSourceType); 

document.addEventListener("DOMContentLoaded", async () => { 
    const form = document.getElementById("geological-form"); 
    const statusMessage = document.getElementById("status-message"); 

    if(!plantId) { 
        showError(statusMessage, "ID centrala lipsa in URL"); 
        return; 
    }

    /*
        Se cauta geologicalId 
            -> Daca nu exista => logica pentru post 
            -> Daca exista => logica de get + put
    */ 
    if(!geologicalId) { 
        form.addEventListener("submit", async (e) => { 
            e.preventDefault(); 
            clearStatus(statusMessage); 

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
            }); 

            try { 
                await powerPlantService.createGeological(dto, plantId); 
                showSuccess(statusMessage, "Datele au fost salvate cu succes!"); 

                form.reset(); 
            } catch(error) { 
                console.error(error.message); 
                showError(statusMessage, "Eroare la adaugarea informatiilor despre centrala.") 
            }
        }); 
    } else { 
        try {
            const response = await powerPlantService.getGeological(plantId);
            const d = response.data;
        
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
        } catch (error) {
            console.error(error.message);
            showError(statusMessage, "Eroare la încărcarea datelor geologice.");
        }

        form.addEventListener("submit", async(e) => { 
            e.preventDefault(); 
            clearStatus(statusMessage); 

            const dto = GeologicalDataRequestDTO({ 
                waterSourceType: document.getElementById("water_source_type").value,
                seismicStability: document.getElementById("seismic_stability").value,
                floodRisk: document.getElementById("flood_risk").value,
                groundwaterLevel: document.getElementById("groundwater_level").value,
                waterProximity: document.getElementById("water_proximity").value,
                waterFlowRate: document.getElementById("water_flow_rate").value,
                populationDensity: document.getElementById("population_density").value,
                transportInfrastructureScore: document.getElementById("transport_infrastructure_score").value,
                geologicalRiskScore: document.getElementById("geological_risk_score").value,
            }); 

            try { 
                await powerPlantService.createGeological(dto, plantId); 
                showSuccess(statusMessage, "Datele au fost actualizate cu succes!"); 

                form.reset(); 
            } catch(error) { 
                console.error(error.message); 
                showError(statusMessage, "Eroare la actualizarea informatiilor despre centrala.") 
            }
        })
    }

}) 