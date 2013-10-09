-- CIRCUS CS DB Migration 7

ALTER TABLE process_machine_list
  ADD COLUMN allocated_job_num smallint NOT NULL DEFAULT 0,
  ADD COLUMN running_job_num smallint NOT NULL DEFAULT 0,
  ADD COLUMN max_job_num smallint NOT NULL DEFAULT 1;
