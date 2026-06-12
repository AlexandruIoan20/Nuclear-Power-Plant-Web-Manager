import { api } from '../core/api.js';

export const mapService = {
    getMapData: () => api.get('/power-plants/map-data')
};
