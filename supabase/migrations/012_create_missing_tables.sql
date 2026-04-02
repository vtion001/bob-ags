-- Migration: Create missing ctm_assignments and call_notes tables
-- Project: bob-ags-nextjs-backup

-- ============================================
-- CTM Assignments Table
-- Stores CTM (Call Tracking/Monitoring) assignments per user
-- ============================================
CREATE TABLE IF NOT EXISTS public.ctm_assignments (
  id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  user_id UUID NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Enable RLS
ALTER TABLE public.ctm_assignments ENABLE ROW LEVEL SECURITY;

-- Drop existing policies if any
DROP POLICY IF EXISTS "Users can view own ctm_assignments" ON public.ctm_assignments;
DROP POLICY IF EXISTS "Users can manage own ctm_assignments" ON public.ctm_assignments;
DROP POLICY IF EXISTS "Admins can view all ctm_assignments" ON public.ctm_assignments;

-- Policy: Users can view their own CTM assignments
CREATE POLICY "Users can view own ctm_assignments" ON public.ctm_assignments
  FOR SELECT USING (auth.uid() = user_id);

-- Policy: Users can insert/update their own CTM assignments
CREATE POLICY "Users can manage own ctm_assignments" ON public.ctm_assignments
  FOR ALL USING (auth.uid() = user_id)
  WITH CHECK (auth.uid() = user_id);

-- Policy: Admins can view all CTM assignments
CREATE POLICY "Admins can view all ctm_assignments" ON public.ctm_assignments
  FOR SELECT USING (
    EXISTS (
      SELECT 1 FROM public.user_roles
      WHERE user_id = auth.uid()::text AND role = 'admin'
    )
  );

-- Index for faster lookups
CREATE INDEX IF NOT EXISTS idx_ctm_assignments_user_id ON public.ctm_assignments(user_id);

-- ============================================
-- Call Notes Table
-- Stores notes for each call
-- ============================================
CREATE TABLE IF NOT EXISTS public.call_notes (
  id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  call_id TEXT NOT NULL,
  notes TEXT DEFAULT '',
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Enable RLS
ALTER TABLE public.call_notes ENABLE ROW LEVEL SECURITY;

-- Drop existing policies if any
DROP POLICY IF EXISTS "Users can view call_notes" ON public.call_notes;
DROP POLICY IF EXISTS "Users can manage call_notes" ON public.call_notes;

-- Policy: Authenticated users can view call notes
CREATE POLICY "Users can view call_notes" ON public.call_notes
  FOR SELECT TO authenticated USING (true);

-- Policy: Authenticated users can insert/update call notes
CREATE POLICY "Users can manage call_notes" ON public.call_notes
  FOR ALL TO authenticated USING (true) WITH CHECK (true);

-- Index for faster lookups by call_id
CREATE INDEX IF NOT EXISTS idx_call_notes_call_id ON public.call_notes(call_id);
