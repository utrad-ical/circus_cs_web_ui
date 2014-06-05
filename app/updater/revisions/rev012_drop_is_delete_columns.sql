-- CIRCUS CS DB Migration 12

ALTER TABLE series_list DROP COLUMN IF EXISTS is_deleted;
ALTER TABLE study_list DROP COLUMN IF EXISTS is_deleted;
ALTER TABLE patient_list DROP COLUMN IF EXISTS is_deleted;
