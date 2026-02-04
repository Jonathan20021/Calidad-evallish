-- Migration: Add lead column to calls table
-- Created: 2026-02-04

-- Add lead column to calls table
ALTER TABLE calls
ADD COLUMN lead VARCHAR(200) NULL AFTER customer_phone;
