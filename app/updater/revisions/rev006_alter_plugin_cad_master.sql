-- CIRCUS CS DB Migration 6

ALTER TABLE plugin_cad_master
DROP COLUMN IF EXISTS score_table,
ADD COLUMN default_policy int NULL,
ADD FOREIGN KEY (default_policy) REFERENCES plugin_result_policy (policy_id)
	ON DELETE SET NULL ON UPDATE CASCADE;