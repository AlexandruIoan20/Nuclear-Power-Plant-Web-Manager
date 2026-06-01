<?php 

enum PlantStatus: string { 
    case DRAFT = "DRAFT"; 
    case REVIEW = "REVIEW"; 
    case APPROVED = "APPROVED"; 
    case REJECTED = "REJECTED"; 
}