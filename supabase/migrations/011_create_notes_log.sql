-- Create notes_log table to track all notes changes
CREATE TABLE IF NOT EXISTS notes_log (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  call_id TEXT NOT NULL,
  user_id UUID NOT NULL REFERENCES auth.users(id),
  notes TEXT NOT NULL,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_notes_log_call_id ON notes_log(call_id);
CREATE INDEX IF NOT EXISTS idx_notes_log_created_at ON notes_log(created_at DESC);

-- Enable RLS
ALTER TABLE notes_log ENABLE ROW LEVEL SECURITY;

-- Policy: users can only see notes logs for their calls
CREATE POLICY "Users can view their own notes logs"
  ON notes_log FOR SELECT
  USING (user_id = auth.uid());

-- Policy: users can insert their own notes logs
CREATE POLICY "Users can insert their own notes logs"
  ON notes_log FOR INSERT
  WITH CHECK (user_id = auth.uid());
