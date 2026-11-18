-- =====================================================================
-- WADULIN - Wadah Aduan Digital dan Layanan Informasi
-- Skema basis data (MySQL 8) - DBML v2.0 dengan revisi kecil
-- Siap di-import ke drawSQL (Import > MySQL)
--
-- Revisi dari dokumen v2.0:
--  1. Status 'dibatalkan' ditambahkan untuk pembatalan oleh pelapor.
--  2. complaint_status_histories.is_public: catatan internal petugas
--     tidak ikut tampil di halaman lacak publik.
--  3. Tabel riwayat (histories, dispositions, attachments) memakai
--     RESTRICT, bukan CASCADE, agar jejak audit tidak ikut terhapus.
-- Tabel bawaan Laravel (sessions, cache, jobs, failed_jobs, migrations,
-- password_reset_tokens) tidak dicantumkan.
-- =====================================================================


CREATE TABLE districts (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code       VARCHAR(10)     NOT NULL COMMENT 'Kode wilayah kecamatan',
    name       VARCHAR(100)    NOT NULL,
    boundary   JSON            NULL     COMMENT 'GeoJSON batas wilayah, opsional',
    created_at TIMESTAMP       NULL,
    updated_at TIMESTAMP       NULL,
    PRIMARY KEY (id),
    UNIQUE KEY districts_code_unique (code)
) COMMENT = 'Master 31 kecamatan Kabupaten Jember';

CREATE TABLE users (
    id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name              VARCHAR(255)    NOT NULL,
    email             VARCHAR(255)    NOT NULL,
    email_verified_at TIMESTAMP       NULL,
    password          VARCHAR(255)    NOT NULL,
    phone             VARCHAR(20)     NOT NULL,
    address           TEXT            NULL,
    avatar_path       VARCHAR(255)    NULL,
    role              ENUM('masyarakat','admin_kecamatan','admin_kabupaten') NOT NULL DEFAULT 'masyarakat',
    district_id       BIGINT UNSIGNED NULL COMMENT 'Wajib untuk admin_kecamatan',
    is_active         BOOLEAN         NOT NULL DEFAULT TRUE,
    last_login_at     TIMESTAMP       NULL,
    remember_token    VARCHAR(100)    NULL,
    created_at        TIMESTAMP       NULL,
    updated_at        TIMESTAMP       NULL,
    PRIMARY KEY (id),
    UNIQUE KEY users_email_unique (email),
    CONSTRAINT users_district_id_foreign FOREIGN KEY (district_id)
        REFERENCES districts (id) ON DELETE RESTRICT
);

CREATE TABLE categories (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100)    NOT NULL,
    slug        VARCHAR(120)    NOT NULL,
    icon        VARCHAR(50)     NULL COMMENT 'Nama ikon Lucide untuk UI',
    description VARCHAR(255)    NULL,
    is_active   BOOLEAN         NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP       NULL,
    updated_at  TIMESTAMP       NULL,
    PRIMARY KEY (id),
    UNIQUE KEY categories_slug_unique (slug)
);

CREATE TABLE qr_locations (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    district_id BIGINT UNSIGNED NOT NULL,
    code        VARCHAR(32)     NOT NULL COMMENT 'Token acak untuk /lapor/{code}',
    name        VARCHAR(150)    NOT NULL COMMENT 'Nama fasilitas publik',
    address     TEXT            NULL,
    latitude    DECIMAL(10,7)   NOT NULL,
    longitude   DECIMAL(10,7)   NOT NULL,
    is_active   BOOLEAN         NOT NULL DEFAULT TRUE,
    created_by  BIGINT UNSIGNED NOT NULL,
    created_at  TIMESTAMP       NULL,
    updated_at  TIMESTAMP       NULL,
    PRIMARY KEY (id),
    UNIQUE KEY qr_locations_code_unique (code),
    CONSTRAINT qr_locations_district_id_foreign FOREIGN KEY (district_id)
        REFERENCES districts (id) ON DELETE RESTRICT,
    CONSTRAINT qr_locations_created_by_foreign FOREIGN KEY (created_by)
        REFERENCES users (id) ON DELETE RESTRICT
);

CREATE TABLE complaints (
    id             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    ticket_code    VARCHAR(20)      NOT NULL COMMENT 'Format WDL-2026-000123',
    user_id        BIGINT UNSIGNED  NOT NULL COMMENT 'Pelapor',
    category_id    BIGINT UNSIGNED  NOT NULL,
    district_id    BIGINT UNSIGNED  NOT NULL COMMENT 'Kecamatan penanggung jawab saat ini',
    qr_location_id BIGINT UNSIGNED  NULL,
    title          VARCHAR(150)     NOT NULL,
    description    TEXT             NOT NULL,
    address        TEXT             NULL,
    latitude       DECIMAL(10,7)    NOT NULL,
    longitude      DECIMAL(10,7)    NOT NULL,
    status         ENUM('pending','diproses','selesai','ditolak','dibatalkan') NOT NULL DEFAULT 'pending',
    is_anonymous   BOOLEAN          NOT NULL DEFAULT FALSE COMMENT 'Sembunyikan nama di tampilan publik',
    due_at         TIMESTAMP        NOT NULL COMMENT 'Batas SLA',
    escalated_at   TIMESTAMP        NULL,
    resolved_at    TIMESTAMP        NULL,
    rating         TINYINT UNSIGNED NULL COMMENT '1-5, diisi pelapor setelah selesai',
    feedback       TEXT             NULL,
    created_at     TIMESTAMP        NULL,
    updated_at     TIMESTAMP        NULL,
    deleted_at     TIMESTAMP        NULL COMMENT 'Soft delete',
    PRIMARY KEY (id),
    UNIQUE KEY complaints_ticket_code_unique (ticket_code),
    KEY complaints_district_status_index (district_id, status),
    KEY complaints_status_due_at_index (status, due_at),
    KEY complaints_category_created_at_index (category_id, created_at),
    CONSTRAINT complaints_user_id_foreign FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE RESTRICT,
    CONSTRAINT complaints_category_id_foreign FOREIGN KEY (category_id)
        REFERENCES categories (id) ON DELETE RESTRICT,
    CONSTRAINT complaints_district_id_foreign FOREIGN KEY (district_id)
        REFERENCES districts (id) ON DELETE RESTRICT,
    CONSTRAINT complaints_qr_location_id_foreign FOREIGN KEY (qr_location_id)
        REFERENCES qr_locations (id) ON DELETE SET NULL
);

CREATE TABLE complaint_attachments (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    complaint_id BIGINT UNSIGNED NOT NULL,
    type         ENUM('bukti','hasil') NOT NULL DEFAULT 'bukti' COMMENT 'bukti = dari pelapor, hasil = dari petugas',
    file_path    VARCHAR(255)    NOT NULL,
    mime_type    VARCHAR(50)     NOT NULL,
    size_kb      INT UNSIGNED    NOT NULL COMMENT 'Maks. 2048',
    uploaded_by  BIGINT UNSIGNED NOT NULL,
    created_at   TIMESTAMP       NULL,
    PRIMARY KEY (id),
    CONSTRAINT complaint_attachments_complaint_id_foreign FOREIGN KEY (complaint_id)
        REFERENCES complaints (id) ON DELETE RESTRICT,
    CONSTRAINT complaint_attachments_uploaded_by_foreign FOREIGN KEY (uploaded_by)
        REFERENCES users (id) ON DELETE RESTRICT
);

CREATE TABLE complaint_status_histories (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    complaint_id BIGINT UNSIGNED NOT NULL,
    changed_by   BIGINT UNSIGNED NOT NULL,
    from_status  ENUM('pending','diproses','selesai','ditolak','dibatalkan') NULL COMMENT 'NULL saat pengaduan dibuat',
    to_status    ENUM('pending','diproses','selesai','ditolak','dibatalkan') NOT NULL,
    note         TEXT            NULL COMMENT 'Wajib bila ditolak',
    is_public    BOOLEAN         NOT NULL DEFAULT TRUE COMMENT 'FALSE = catatan internal, tidak tampil di lacak tiket',
    created_at   TIMESTAMP       NULL,
    PRIMARY KEY (id),
    KEY complaint_status_histories_complaint_created_index (complaint_id, created_at),
    CONSTRAINT complaint_status_histories_complaint_id_foreign FOREIGN KEY (complaint_id)
        REFERENCES complaints (id) ON DELETE RESTRICT,
    CONSTRAINT complaint_status_histories_changed_by_foreign FOREIGN KEY (changed_by)
        REFERENCES users (id) ON DELETE RESTRICT
);

CREATE TABLE complaint_dispositions (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    complaint_id     BIGINT UNSIGNED NOT NULL,
    from_district_id BIGINT UNSIGNED NOT NULL,
    to_district_id   BIGINT UNSIGNED NOT NULL,
    moved_by         BIGINT UNSIGNED NOT NULL,
    reason           TEXT            NOT NULL,
    created_at       TIMESTAMP       NULL,
    PRIMARY KEY (id),
    CONSTRAINT complaint_dispositions_complaint_id_foreign FOREIGN KEY (complaint_id)
        REFERENCES complaints (id) ON DELETE RESTRICT,
    CONSTRAINT complaint_dispositions_from_district_id_foreign FOREIGN KEY (from_district_id)
        REFERENCES districts (id) ON DELETE RESTRICT,
    CONSTRAINT complaint_dispositions_to_district_id_foreign FOREIGN KEY (to_district_id)
        REFERENCES districts (id) ON DELETE RESTRICT,
    CONSTRAINT complaint_dispositions_moved_by_foreign FOREIGN KEY (moved_by)
        REFERENCES users (id) ON DELETE RESTRICT
);

CREATE TABLE announcements (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title        VARCHAR(200)    NOT NULL,
    slug         VARCHAR(220)    NOT NULL,
    excerpt      VARCHAR(300)    NULL,
    body         TEXT            NOT NULL,
    cover_path   VARCHAR(255)    NULL,
    is_published BOOLEAN         NOT NULL DEFAULT FALSE,
    published_at TIMESTAMP       NULL,
    author_id    BIGINT UNSIGNED NOT NULL,
    created_at   TIMESTAMP       NULL,
    updated_at   TIMESTAMP       NULL,
    PRIMARY KEY (id),
    UNIQUE KEY announcements_slug_unique (slug),
    CONSTRAINT announcements_author_id_foreign FOREIGN KEY (author_id)
        REFERENCES users (id) ON DELETE RESTRICT
) COMMENT = 'Berita & pengumuman website publik';

CREATE TABLE faqs (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    question     VARCHAR(255)    NOT NULL,
    answer       TEXT            NOT NULL,
    sort_order   INT             NOT NULL DEFAULT 0,
    is_published BOOLEAN         NOT NULL DEFAULT TRUE,
    created_at   TIMESTAMP       NULL,
    updated_at   TIMESTAMP       NULL,
    PRIMARY KEY (id)
);

CREATE TABLE notifications (
    id              CHAR(36)        NOT NULL,
    type            VARCHAR(255)    NOT NULL,
    notifiable_type VARCHAR(255)    NOT NULL,
    notifiable_id   BIGINT UNSIGNED NOT NULL,
    data            JSON            NOT NULL COMMENT 'judul, pesan, link, ticket_code',
    read_at         TIMESTAMP       NULL,
    created_at      TIMESTAMP       NULL,
    PRIMARY KEY (id),
    KEY notifications_notifiable_index (notifiable_type, notifiable_id)
) COMMENT = 'Tabel notifikasi bawaan Laravel, dipakai untuk ikon lonceng';
