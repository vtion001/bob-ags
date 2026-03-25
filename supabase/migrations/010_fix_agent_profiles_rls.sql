-- Migration: Fix agent_profiles RLS for admin access
-- Project: mmrhryddyjjkyhstytox

-- Drop existing restrictive policies
DROP POLICY IF EXISTS "Users can view their own agent profiles" ON agent_profiles;
DROP POLICY IF EXISTS "Users can insert their own agent profiles" ON agent_profiles;
DROP POLICY IF EXISTS "Users can update their own agent profiles" ON agent_profiles;
DROP POLICY IF EXISTS "Users can delete their own agent profiles" ON agent_profiles;

-- Policy: Allow authenticated users to view agent profiles (filtered in app layer based on role)
CREATE POLICY "Authenticated users can view agent profiles"
  ON agent_profiles
  FOR SELECT
  TO authenticated
  USING (true);

-- Policy: Allow authenticated users to insert agent profiles
CREATE POLICY "Authenticated users can insert agent profiles"
  ON agent_profiles
  FOR INSERT
  TO authenticated
  WITH CHECK (true);

-- Policy: Allow authenticated users to update agent profiles
CREATE POLICY "Authenticated users can update agent profiles"
  ON agent_profiles
  FOR UPDATE
  TO authenticated
  USING (true)
  WITH CHECK (true);

-- Policy: Allow authenticated users to delete agent profiles
CREATE POLICY "Authenticated users can delete agent profiles"
  ON agent_profiles
  FOR DELETE
  TO authenticated
  USING (true);

-- Also update public.users RLS to allow viewing all for admins
DROP POLICY IF EXISTS "Users can view own profile" ON public.users;
CREATE POLICY "Users can view own profile"
  ON public.users
  FOR SELECT
  TO authenticated
  USING (auth.uid() = id OR EXISTS (
    SELECT 1 FROM public.user_roles 
    WHERE user_roles.user_id = auth.uid() 
    AND user_roles.role = 'admin'
    AND user_roles.approved = true
  ));

DROP POLICY IF EXISTS "Users can update own profile" ON public.users;
CREATE POLICY "Users can update own profile"
  ON public.users
  FOR UPDATE
  TO authenticated
  USING (auth.uid() = id OR EXISTS (
    SELECT 1 FROM public.user_roles 
    WHERE user_roles.user_id = auth.uid() 
    AND user_roles.role = 'admin'
    AND user_roles.approved = true
  ))
  WITH CHECK (auth.uid() = id OR EXISTS (
    SELECT 1 FROM public.user_roles 
    WHERE user_roles.user_id = auth.uid() 
    AND user_roles.role = 'admin'
    AND user_roles.approved = true
  ));

DROP POLICY IF EXISTS "Users can insert own profile" ON public.users;
CREATE POLICY "Users can insert own profile"
  ON public.users
  FOR INSERT
  TO authenticated
  WITH CHECK (auth.uid() = id);
