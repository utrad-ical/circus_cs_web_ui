-- CIRCUS CS DB Migration 13

DELETE FROM group_privileges WHERE privilege='researchShow' OR privilege='researchExec';
