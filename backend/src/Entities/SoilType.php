<?php 

enum SoilType: string { 
    case BEDROCK = 'BEDROCK';
    case STIFF_CLAY = 'STIFF_CLAY';
    case DENSE_SAND = 'DENSE_SAND';
    case GRAVEL = 'GRAVEL';

    case SHALE = 'SHALE';
    case LIMESTONE = 'LIMESTONE';
    case SANDSTONE = 'SANDSTONE';

    case SOFT_CLAY = 'SOFT_CLAY';
    case LOOSE_SAND = 'LOOSE_SAND';
    case SILT = 'SILT';
    case LOAM = 'LOAM';
    case PEAT = 'PEAT';
}