-- Migration: Fix user_settings schema to match API expectations
-- Project: bob-ags-nextjs-backup
-- Issue: Migrations 002 and 004 use TEXT user_id and JSONB settings which conflicts with explicit columns schema
-- Solution: Recreate table with correct schema (UUID user_id, JSONB settings column)

-- Drop the existing table and recreate with correct schema
-- This is safe because:
-- 1. The API routes (GET, POST, DELETE in app/api/users/settings/route.ts) use .select('settings').eq('user_id', user.id)
-- 2. They upsert with { user_id, settings: body } - settings is a JSONB object
-- 3. No explicit column access is used

-- Create backup of existing data (if any)
DROP TABLE IF EXISTS public.user_settings_backup;

ALTER TABLE public.user_settings RENAME TO user_settings_backup;

-- Create new user_settings table with correct schema
CREATE TABLE IF NOT EXISTS public.user_settings (
  id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  user_id UUID NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
  settings JSONB DEFAULT '{
    "ctm_access_key": "",
    "ctm_secret_key": "",
    "ctm_account_id": "",
    "openrouter_api_key": "",
    "default_client": "flyland",
    "light_mode": true,
    "email_notifications": false,
    "auto_sync_calls": true,
    "call_sync_interval": 60
  }'::jsonb,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create index
CREATE INDEX IF NOT EXISTS idx_user_settings_user_id ON public.user_settings(user_id);

-- Enable RLS
ALTER TABLE public.user_settings ENABLE ROW LEVEL SECURITY;

-- Drop existing policies
DROP POLICY IF EXISTS "Users can view own settings" ON public.user_settings;
DROP POLICY IF EXISTS "Users can insert own settings" ON public.user_settings;
DROP POLICY IF EXISTS "Users can update own settings" ON public.user_settings;
DROP POLICY IF EXISTS "Users can manage own settings" ON public.user_settings;
DROP POLICY IF EXISTS "Admins can view all settings" ON public.user_settings;

-- Policy: Users can only see their own settings
CREATE POLICY "Users can view own settings" ON public.user_settings
  FOR SELECT USING (auth.uid() = user_id);

-- Policy: Users can insert their own settings
CREATE POLICY "Users can insert own settings" ON public.user_settings
  FOR INSERT WITH CHECK (auth.uid() = user_id);

-- Policy: Users can update their own settings
CREATE POLICY "Users can update own settings" ON public.user_settings
  FOR UPDATE USING (auth.uid() = user_id) WITH CHECK (auth.uid() = user_id);

-- Policy: Admins can view all settings
CREATE POLICY "Admins can view all settings" ON public.user_settings
  FOR SELECT USING (
    EXISTS (
      SELECT 1 FROM public.user_roles
      WHERE user_id = auth.uid()::text AND role = 'admin'
    )
    OR auth.uid() = user_id
  );

-- Function to auto-update updated_at
CREATE OR REPLACE FUNCTION public.update_user_settings_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger to auto-update updated_at
DROP TRIGGER IF EXISTS update_user_settings_updated_at ON public.user_settings;
CREATE TRIGGER update_user_settings_updated_at
  BEFORE UPDATE ON public.user_settings
  FOR EACH ROW
  EXECUTE FUNCTION public.update_user_settings_updated_at();
