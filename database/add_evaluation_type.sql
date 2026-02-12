-- Migration: Add evaluation_type color to evaluations table
-- Created: 2026-02-12

ALTER TABLE evaluations
ADD COLUMN evaluation_type ENUM('remota', 'presencial', 'llamada', 'chat') NULL AFTER form_template_id;
