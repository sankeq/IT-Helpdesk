# Entity Relationship Diagram

```mermaid
erDiagram
    DEPARTMENTS ||--o{ USERS : contains
    LOCATIONS ||--o{ USERS : assigned_to
    USERS ||--o{ TICKETS : requests
    USERS ||--o{ TICKETS : assigned_to
    TICKET_GROUPS ||--o{ TICKETS : owns
    TICKET_CATEGORIES ||--o{ TICKETS : classifies
    SLA_POLICIES ||--o{ TICKETS : applies
    BUSINESS_CALENDARS ||--o{ SLA_POLICIES : defines
    TICKETS ||--o{ TICKET_COMMENTS : contains
    USERS ||--o{ TICKET_COMMENTS : writes
    TICKETS ||--o{ TICKET_HISTORIES : records
    USERS ||--o{ TICKET_HISTORIES : performs
    TICKETS ||--o{ TICKET_ATTACHMENTS : includes
    USERS ||--o{ TICKET_ATTACHMENTS : uploads
    TICKETS ||--o{ TICKET_SLA_EVENTS : measures
    TICKETS ||--o{ TICKET_ASSETS : references
    ASSETS ||--o{ TICKET_ASSETS : linked
    USERS ||--o{ ASSETS : assigned
    DEPARTMENTS ||--o{ ASSETS : owns
    LOCATIONS ||--o{ ASSETS : located
    TICKETS ||--o{ APPROVALS : requires
    USERS ||--o{ APPROVALS : approves
    TICKET_CATEGORIES ||--o{ KNOWLEDGE_ARTICLES : organizes
    USERS ||--o{ KNOWLEDGE_ARTICLES : authors
    USERS ||--o{ AUDIT_LOGS : acts
```

## Catatan
- Satu tiket memiliki satu requester dan dapat memiliki satu assignee utama serta satu group.
- Jika satu tiket dapat memengaruhi banyak aset, gunakan `ticket_assets`; hindari mengandalkan satu kolom `asset_id`.
- `ticket_histories` menyimpan timeline operasional. `audit_logs` menyimpan jejak tindakan keamanan/administratif. Keduanya memiliki tujuan berbeda.
- Approval dapat mempunyai beberapa baris per tiket bila multi-level approval dibutuhkan.
- User/department/location dapat dinonaktifkan tanpa menghapus tiket historis.
