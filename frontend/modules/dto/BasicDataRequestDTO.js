export function BasicDataRequestDTO({ capacity, constructionDurationYears, description}) { 
    return {
        capacity: capacity != null ? parseFloat(capacity) : null, 
        constructionDurationYears: constructionDurationYears != null ? parseInt(constructionDurationYears) : null, 
        description
    }
}