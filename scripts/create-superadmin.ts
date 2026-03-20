import { createClient } from '@supabase/supabase-js'

const supabaseAdmin = createClient(
  'https://mmrhryddyjjkyhstytox.supabase.co',
  'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im1tcmhyeWRkeWpqa3loc3R5dG94Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc3MzkzODY2MywiZXhwIjoyMDg5NTE0NjYzfQ.8F-xTH3W6A3Av-Spi3jYJiSQymgW9Bs9jQspQiA4KYY',
  {
    auth: {
      autoRefreshToken: false,
      persistSession: false
    }
  }
)

async function createSuperadmin() {
  console.log('Creating superadmin user...')
  
  const email = 'agsdev@allianceglobalsolutions.com'
  const password = 'ags2026@@..'
  
  const { data, error } = await supabaseAdmin.auth.admin.createUser({
    email,
    password,
    email_confirm: true,
    user_metadata: {
      role: 'superadmin',
      name: 'AGS Dev'
    }
  })
  
  if (error) {
    console.error('Error creating user:', error.message)
    process.exit(1)
  }
  
  console.log('Superadmin user created successfully!')
  console.log('User ID:', data.user.id)
  console.log('Email:', data.user.email)
}

createSuperadmin()