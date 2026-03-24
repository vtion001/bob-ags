-- Migration: Add QA role to user_roles
-- Add 'qa' role with permissions to view all calls and run analysis

-- 1. Update CHECK constraint to include 'qa' role
ALTER TABLE public.user_roles DROP CONSTRAINT IF EXISTS user_roles_role_check;
ALTER TABLE public.user_roles ADD CONSTRAINT user_roles_role_check 
  CHECK (role IN ('admin', 'manager', 'viewer', 'qa'));

-- 2. QA role gets same permissions as manager (can view calls, history, run analysis)
-- But cannot manage settings or users