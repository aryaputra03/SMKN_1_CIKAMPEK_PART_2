// admin/assets/js/supabase-client.js

import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/+esm';

const SUPABASE_URL = 'https://XXXX.supabase.co';     // ganti dengan URL project kamu
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImlqZXd2YnVvaWlzdW55ZGJ0b2dsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODA5MjEzODQsImV4cCI6MjA5NjQ5NzM4NH0.FGYtpQXsTftK-V385r7vgnxuJxrm9xXVo7BGfx2c91o';    // ganti dengan anon key

export const supabase = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);