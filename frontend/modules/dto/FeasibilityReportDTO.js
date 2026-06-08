export function FeasibilityReportDTO(data) {
    return {
        reportId: data.reportId ?? null,
        status: data.status ?? null,
        nsviScore: data.nsviScore ?? null,
        deficiencies: Array.isArray(data.deficiencies) ? data.deficiencies : [],
        createdAt: data.createdAt ?? null,
    };
}