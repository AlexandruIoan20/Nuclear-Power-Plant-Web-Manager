export const SoilType = [
    { value: "BEDROCK", label: "Stâncă de bază" },
    { value: "STIFF_CLAY", label: "Argilă tare" },
    { value: "DENSE_SAND", label: "Nisip compact" },
    { value: "GRAVEL", label: "Pietriș" },
    { value: "SHALE", label: "Șist" },
    { value: "LIMESTONE", label: "Calcar" },
    { value: "SANDSTONE", label: "Gresie" },
    { value: "SOFT_CLAY", label: "Argilă moale" },
    { value: "LOOSE_SAND",  label: "Nisip afânat" },
    { value: "SILT", label: "Silt" },
    { value: "LOAM", label: "Lut" },
    { value: "PEAT", label: "Turbă" },
];

export const WaterSourceType = [
    { value: "FRESH_WATER", label: "Apă dulce" },
    { value: "SALT_WATER", label: "Apă sărată" },
    { value: "BRACKISH_WATER", label: "Apă salmastră" },
];

export const ReactorType = [
    { value: "PWR", label: "PWR" },
    { value: "BWR", label: "BWR" },
    { value: "PHWR", label: "PHWR" },
    { value: "FBR", label: "FBR" },
];

export const CoolingType = [
    { value: "ONCE_THROUGH_FRESH", label: "Flux Direct - Apă Dulce" },
    { value: "ONCE_THROUGH_SALT", label: "Flux Direct - Apă Sărată" },
    { value: "NATURAL_DRAFT_WET", label: "Tiraj Natural Umed" },
    { value: "MECHANICAL_DRAFT_WET",label: "Tiraj Mecanic Umed" },
    { value: "DRY_COOLING", label: "Răcire Uscată" },
    { value: "HYBRID", label: "Hibrid" },
    { value: "COOLING_POND", label: "Bazin de Răcire" },
];

export const ReactorOperationalStatus = [
    { value: "SHUTDOWN", label: "Oprit" },
    { value: "COLD_STANDBY", label: "Rezervă la Rece" },
    { value: "HOT_STANDBY", label: "Rezervă la Cald" },
    { value: "STARTUP", label: "Pornire" },
    { value: "POWER_ASCENT", label: "Creștere Putere" },
    { value: "FULL_POWER", label: "Putere Maximă" },
    { value: "PARTIAL_POWER", label: "Putere Parțială" },
    { value: "PLANNED_OUTAGE", label: "Oprire Planificată" },
    { value: "UNPLANNED_OUTAGE", label: "Oprire Neplanificată" },
    { value: "EMERGENCY_SHUTDOWN", label: "Oprire de Urgență" },
];

export const PlantStatus = [
    { value: "APPROVED", label: "Aprobate" }, 
    { value: "REJECTED", label: "Respins\u0103" },
    { value: "REVIEW", label: "În verificare" }, 
    { value: "DRAFT", label: "În lucru" }, 
]
