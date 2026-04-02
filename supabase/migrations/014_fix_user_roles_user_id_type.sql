-- Migration: Fix user_roles.user_id type from TEXT to UUID
-- Project: bob-ags-nextjs-backup
-- Issue: user_roles.user_id is TEXT but auth.users(id) is UUID
-- This causes type mismatches in RLS policies and foreign key references

-- Create backup of existing data
DROP TABLE IF EXISTS public.user_roles_backup;

ALTER TABLE public.user_roles RENAME TO user_roles_backup;

-- Create new user_roles table with correct UUID user_id
CREATE TABLE IF NOT EXISTS public.user_roles (
  id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  user_id UUID UNIQUE NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
  email TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'viewer' CHECK (role IN ('admin', 'manager', 'viewer', 'qa')),
  permissions JSONB DEFAULT '{
    "can_view_calls": true,
    "can_view_monitor": true,
    "can_view_history": false,
    "can_view_agents": false,
    "can_manage_settings": false,
    "can_manage_users": false,
    "can_run_analysis": false
  }'::jsonb,
  approved BOOLEAN DEFAULT false,
  approved_by UUID,
  approved_at TIMESTAMP WITH TIME ZONE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Enable RLS
ALTER TABLE public.user_roles ENABLE ROW LEVEL SECURITY;

-- Drop existing policies
DROP POLICY IF EXISTS "Users can view own role" ON public.user_roles;
DROP POLICY IF EXISTS "Users can insert own role" ON public.user_roles;
DROP POLICY IF EXISTS "Users can update own role" ON public.user_roles;
DROP POLICY IF EXISTS "Admins can manage roles" ON public.user_roles;

-- Policy: Users can view their own role
CREATE POLICY "Users can view own role" ON public.user_roles
  FOR SELECT USING (auth.uid() = user_id);

-- Policy: Anyone authenticated can insert their own role
CREATE POLICY "Users can insert own role" ON public.user_roles
  FOR INSERT WITH CHECK (auth.uid() = user_id);

-- Policy: Users can update their own role
CREATE POLICY "Users can update own role" ON public.user_roles
  FOR UPDATE USING (auth.uid() = user_id);

-- Policy: Admins can manage all roles
CREATE POLICY "Admins can manage roles" ON public.user_roles
  FOR ALL USING (
    EXISTS (
      SELECT 1 FROM public.user_roles
      WHERE user_id = auth.uid() AND role = 'admin'
    )
  );

-- Create index
CREATE INDEX IF NOT EXISTS idx_user_roles_user_id ON public.user_roles(user_id);
CREATE INDEX IF NOT EXISTS idx_user_roles_role ON public.user_roles(role);

-- Function to auto-create user role on signup (updated to use UUID)
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER AS $$
BEGIN
  INSERT INTO public.user_roles (user_id, email, role, permissions)
  VALUES (
    NEW.id,
    NEW.email,
    'viewer',
    '{
      "can_view_calls": true,
      "can_view_monitor": true,
      "can_view_history": false,
      "can_view_agents": false,
      "can_manage_settings": false,
      "can_manage_users": false,
      "can_run_analysis": false
    }'::jsonb
  )
  ON CONFLICT (user_id) DO NOTHING;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Trigger to auto-create user role on auth.users insert
DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users;
CREATE TRIGGER on_auth_user_created
  AFTER INSERT ON auth.users
  FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();

-- Function to get user permissions (updated signature to use UUID)
CREATE OR REPLACE FUNCTION get_user_permissions(p_user_id UUID)
RETURNS JSONB AS $$
DECLARE
  permissions JSONB;
BEGIN
  SELECT permissions INTO permissions
  FROM public.user_roles
  WHERE user_id = p_user_id;

  RETURN COALESCE(permissions, '{
    "can_view_calls": true,
    "can_view_monitor": true,
    "can_view_history": false,
    "can_view_agents": false,
    "can_manage_settings": false,
    "can_manage_users": false,
    "can_run_analysis": false
  }'::jsonb);
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Migrate data from backup table if data exists
INSERT INTO public.user_roles (id, user_id, email, role, permissions, approved, approved_by, approved_at, created_at, updated_at)
SELECT
  id,
  user_id::UUID,  -- Convert TEXT to UUID
  email,
  role,
  permissions,
  approved,
  approved_by::UUID,
  approved_at,
  created_at,
  updated_at
FROM public.user_roles_backup
ON CONFLICT (user_id) DO NOTHING;

-- Clean up backup table
DROP TABLE IF EXISTS public.user_roles_backup;
