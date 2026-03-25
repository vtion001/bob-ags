-- Migration 009: Create QA Overrides table and QA Analysis view
-- Run this manually in Supabase SQL Editor if migrations have issues

-- Drop existing objects first
DROP VIEW IF EXISTS public.qa_analysis_view CASCADE;
DROP TABLE IF EXISTS public.qa_overrides CASCADE;

-- Create qa_overrides table
CREATE TABLE public.qa_overrides (
  id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  call_id TEXT NOT NULL,
  ctm_call_id TEXT,
  user_id UUID NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
  overrides JSONB NOT NULL DEFAULT '[]'::jsonb,
  manual_score INTEGER,
  ai_score INTEGER,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Create indexes
CREATE INDEX qa_overrides_call_id_idx ON public.qa_overrides(call_id);
CREATE INDEX qa_overrides_ctm_call_id_idx ON public.qa_overrides(ctm_call_id);
CREATE INDEX qa_overrides_user_id_idx ON public.qa_overrides(user_id);
CREATE INDEX qa_overrides_created_at_idx ON public.qa_overrides(created_at DESC);

-- Enable RLS on table
ALTER TABLE public.qa_overrides ENABLE ROW LEVEL SECURITY;

-- RLS policies for qa_overrides
CREATE POLICY "Users can view own overrides" ON public.qa_overrides
  FOR SELECT USING (user_id = auth.uid());

CREATE POLICY "Users can insert own overrides" ON public.qa_overrides
  FOR INSERT WITH CHECK (user_id = auth.uid());

CREATE POLICY "Admins and QA can view all overrides" ON public.qa_overrides
  FOR SELECT USING (
    EXISTS (
      SELECT 1 FROM public.user_roles ur
      WHERE ur.user_id = auth.uid()::TEXT
      AND ur.role IN ('admin', 'qa')
    )
  );

-- Create view
CREATE VIEW public.qa_analysis_view AS
SELECT 
  c.id AS call_db_id,
  c.ctm_call_id,
  c.user_id,
  c.phone,
  c.direction,
  c.duration,
  c.status,
  c.timestamp,
  c.caller_number,
  c.tracking_number,
  c.tracking_label,
  c.source,
  c.source_id,
  c.agent_id,
  c.agent_name,
  c.recording_url,
  c.transcript,
  c.city,
  c.state,
  c.postal_code,
  c.notes,
  c.talk_time,
  c.wait_time,
  c.ring_time,
  c.score AS ai_score,
  c.sentiment,
  c.summary,
  c.tags,
  c.disposition,
  c.rubric_results,
  c.rubric_breakdown,
  c.synced_at AS ctm_synced_at,
  c.created_at AS call_created_at,
  c.updated_at AS call_updated_at,
  qo.id AS override_id,
  qo.user_id AS override_user_id,
  qo.overrides,
  qo.manual_score,
  qo.ai_score AS override_ai_score,
  (qo.manual_score - qo.ai_score) AS score_change,
  qo.created_at AS override_created_at,
  jsonb_array_length(CASE WHEN jsonb_typeof(qo.overrides) = 'array' THEN qo.overrides ELSE '[]'::jsonb END) AS override_count,
  au.email AS override_user_email,
  CASE WHEN qo.id IS NOT NULL THEN true ELSE false END AS has_overrides
FROM public.calls c
LEFT JOIN LATERAL (
  SELECT qo.id, qo.user_id, qo.overrides, qo.manual_score, qo.ai_score, qo.created_at
  FROM public.qa_overrides qo
  WHERE qo.ctm_call_id = c.ctm_call_id
  ORDER BY qo.created_at DESC
  LIMIT 1
) qo ON true
LEFT JOIN auth.users au ON au.id = qo.user_id;

-- Grant access
GRANT SELECT ON public.qa_analysis_view TO authenticated;

-- RLS policy for view
CREATE POLICY "Users can view own calls in qa_analysis" ON public.qa_analysis_view
  FOR SELECT USING (user_id = auth.uid()::TEXT);

-- Comments
COMMENT ON VIEW public.qa_analysis_view IS 'Combined view of calls with latest QA overrides';
COMMENT ON TABLE public.qa_overrides IS 'Stores manual QA overrides made by QA and Admin users';

-- SECURITY DEFINER function to get analyzed calls with agent names
-- This bypasses RLS to allow joining with agent_profiles
CREATE OR REPLACE FUNCTION get_analyzed_calls(p_limit INT DEFAULT 100, p_offset INT DEFAULT 0)
RETURNS TABLE (
  id UUID,
  ctm_call_id TEXT,
  phone TEXT,
  direction TEXT,
  duration INT,
  score INT,
  sentiment TEXT,
  created_at TIMESTAMPTZ,
  agent_id TEXT,
  agent_name TEXT,
  rubric_results JSONB
)
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
  RETURN QUERY
  SELECT 
    c.id,
    c.ctm_call_id,
    c.phone,
    c.direction,
    c.duration,
    c.score,
    c.sentiment,
    c.created_at,
    c.agent_id,
    COALESCE(ap.name, c.agent_name)::TEXT AS agent_name,
    c.rubric_results
  FROM public.calls c
  LEFT JOIN public.agent_profiles ap ON ap.agent_id = c.agent_id
  WHERE c.rubric_results IS NOT NULL
  ORDER BY c.created_at DESC
  LIMIT p_limit
  OFFSET p_offset;
END;
$$;

GRANT EXECUTE ON FUNCTION get_analyzed_calls TO authenticated;