-- Enable live analysis for all users by default
UPDATE public.user_roles 
SET permissions = jsonb_set(
  permissions::jsonb, 
  '{can_run_analysis}', 
  'true'::jsonb
);

-- Update the default permissions in the table constraint
ALTER TABLE public.user_roles 
ALTER COLUMN permissions SET DEFAULT '{
  "can_view_calls": true,
  "can_view_monitor": true,
  "can_view_history": false,
  "can_view_agents": false,
  "can_manage_settings": false,
  "can_manage_users": false,
  "can_run_analysis": true
}'::jsonb;

-- Update the trigger function to use new defaults
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
      "can_run_analysis": true
    }'::jsonb
  )
  ON CONFLICT (user_id) DO NOTHING;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Update the get_user_permissions function
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
    "can_run_analysis": true
  }'::jsonb);
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
