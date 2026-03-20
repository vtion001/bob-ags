-- Create user_settings table for storing user preferences
CREATE TABLE IF NOT EXISTS public.user_settings (
  id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  user_id TEXT UNIQUE NOT NULL,
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

-- Enable RLS
ALTER TABLE public.user_settings ENABLE ROW LEVEL SECURITY;

-- Policy: Users can manage their own settings
CREATE POLICY "Users can manage own settings" ON public.user_settings
  FOR ALL USING (auth.uid()::text = user_id);

-- Enable RLS on user_roles for viewing own record
ALTER TABLE public.user_roles ENABLE ROW LEVEL SECURITY;

-- Updated policy: Users can view their own role
DROP POLICY IF EXISTS "Users can view own role" ON public.user_roles;
CREATE POLICY "Users can view own role" ON public.user_roles
  FOR SELECT USING (auth.uid()::text = user_id);

-- Updated policy: Anyone authenticated can insert their own role
DROP POLICY IF EXISTS "Users can insert own role" ON public.user_roles;
CREATE POLICY "Users can insert own role" ON public.user_roles
  FOR INSERT WITH CHECK (auth.uid()::text = user_id);

-- Policy: Users can update their own role
DROP POLICY IF EXISTS "Users can update own role" ON public.user_roles;
CREATE POLICY "Users can update own role" ON public.user_roles
  FOR UPDATE USING (auth.uid()::text = user_id);
