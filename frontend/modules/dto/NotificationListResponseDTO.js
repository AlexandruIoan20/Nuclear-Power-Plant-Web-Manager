export function NotificationListResponseDTO(data) {
    return {
        id: data.id ?? null,
        severity: data.severity ?? null,
        title: data.title ?? null,
        date: data.date ?? null,
        message: data.message ?? null,
        type: data.type ?? null,
        targetRole: data.targetRole ?? data.target_role ?? null,
        relatedEntityType: data.relatedEntityType ?? null,
        relatedEntityId: data.relatedEntityId ?? null,
        isRead: data.isRead === true,
        createdAt: data.createdAt ?? null,
    };
}
