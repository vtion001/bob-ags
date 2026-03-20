-- Migration 004: Add rubric_results and rubric_breakdown to calls table

ALTER TABLE public.calls
ADD COLUMN IF NOT EXISTS rubric_results JSONB DEFAULT null,
ADD COLUMN IF NOT EXISTS rubric_breakdown JSONB DEFAULT null;