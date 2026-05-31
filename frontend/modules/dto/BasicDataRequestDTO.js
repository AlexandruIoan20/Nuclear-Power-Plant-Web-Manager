export function BasicDataRequestDTO({ capacity, constructionDurationYears, description}) { 
    return {
        capacity: parseFloat(capacity), 
        constructionDurationYears: parseInt(constructionDurationYears), 
        description
    }
}