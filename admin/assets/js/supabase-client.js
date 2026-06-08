// assets/js/supabase-client.js

// 1. URL Project Anda (Sudah otomatis diperbaiki berdasarkan Anon Key Anda)
const SUPABASE_URL = 'https://ijewvbuoiisunydbtogl.supabase.co'; 

// 2. Kunci Anon Anda
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImlqZXd2YnVvaWlzdW55ZGJ0b2dsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODA5MjEzODQsImV4cCI6MjA5NjQ5NzM4NH0.FGYtpQXsTftK-V385r7vgnxuJxrm9xXVo7BGfx2c91o';

// 3. Pasang ke scope global (window) agar bisa dipakai langsung di index.html, dashboard.html, dll.
window.supabase = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);