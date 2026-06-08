// admin/assets/js/supabase-client.js

const SUPABASE_URL = 'https://ijewvbuoiisunydbtogl.supabase.co'; 
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImlqZXd2YnVvaWlzdW55ZGJ0b2dsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODA5MjEzODQsImV4cCI6MjA5NjQ5NzM4NH0.FGYtpQXsTftK-V385r7vgnxuJxrm9xXVo7BGfx2c91o';

// 1. Ambil fungsi createClient dari library global CDN secara aman
const { createClient } = window.supabase || {};

if (createClient) {
    // 2. Inisialisasi client dan timpa ke scope global agar bisa langsung dipakai di berita.html
    window.supabase = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
} else {
    console.error("Gagal menginisialisasi: Pastikan script CDN Supabase dipasang sebelum file ini!");
}