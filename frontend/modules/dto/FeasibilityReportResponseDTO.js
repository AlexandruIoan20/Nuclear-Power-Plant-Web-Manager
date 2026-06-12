export function FeasibilityReportResponseDTO(data) {
    return {
        reportId: data.reportId ?? null,
        status: data.status ?? null,
        nsviScore: data.nsviScore != null ? parseFloat(data.nsviScore) : null,
        deficiencies: Array.isArray(data.deficiencies) ? data.deficiencies : [],
        errors: Array.isArray(data.errors) ? data.errors : [],
        message: data.message ?? null,
        createdAt: data.createdAt ?? null,
    };
}
