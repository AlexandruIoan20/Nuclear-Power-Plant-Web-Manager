export function FeasibilityReportDTO(data) {
    return {
        reportId: data.reportId ?? null,
        status: data.status ?? null,
        nsviScore: data.nsviScore ?? null,
        deficiencies: Array.isArray(data.deficiencies) ? data.deficiencies : [],
        errors: Array.isArray(data.errors) ? data.errors : [],
        createdAt: data.createdAt ?? null,
    };
}