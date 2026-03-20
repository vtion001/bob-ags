-- Create user_roles table for permission management
CREATE TABLE IF NOT EXISTS public.user_roles (
  id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  user_id TEXT UNIQUE NOT NULL,
  email TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'viewer' CHECK (role IN ('admin', 'manager', 'viewer')),
  permissions JSONB DEFAULT '{
    "can_view_calls": true,
    "can_view_monitor": true,
    "can_view_history": false,
    "can_view_agents": false,
    "can_manage_settings": false,
    "can_manage_users": false,
    "can_run_analysis": false
  }',
  approved BOOLEAN DEFAULT false,
  approved_by TEXT,
  approved_at TIMESTAMP WITH TIME ZONE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Enable RLS
ALTER TABLE public.user_roles ENABLE ROW LEVEL SECURITY;

-- Policy: Users can view their own role
CREATE POLICY "Users can view own role" ON public.user_roles
  FOR SELECT USING (auth.uid()::text = user_id);

-- Policy: Anyone authenticated can insert (for auto-registration)
CREATE POLICY "Users can insert own role" ON public.user_roles
  FOR INSERT WITH CHECK (auth.uid()::text = user_id);

-- Policy: Admins can manage all roles
CREATE POLICY "Admins can manage roles" ON public.user_roles
  FOR ALL USING (
    EXISTS (
      SELECT 1 FROM public.user_roles 
      WHERE user_id = auth.uid()::text AND role = 'admin'
    )
  );

-- Policy: Users can update their own role
CREATE POLICY "Users can update own role" ON public.user_roles
  FOR UPDATE USING (auth.uid()::text = user_id);

-- Function to auto-create user role on signup
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

-- Function to get user permissions
CREATE OR REPLACE FUNCTION get_user_permissions(p_user_id TEXT)
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
