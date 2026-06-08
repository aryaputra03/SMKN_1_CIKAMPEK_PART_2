#!/usr/bin/env python3
"""
Script: add_wa_button.py
Adds a floating WhatsApp button to all HTML pages.
Run from your project root directory:
    python3 add_wa_button.py
"""

import os
import re
import glob

WA_CSS = """
/* ── WHATSAPP FLOAT BUTTON ─────────────────── */
.wa-float {
  position: fixed;
  bottom: 80px;
  right: 28px;
  z-index: 9999;
  width: 54px;
  height: 54px;
  border-radius: 50%;
  background: #25D366;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(37,211,102,0.45), 0 2px 8px rgba(0,0,0,0.15);
  text-decoration: none;
  transition: transform 0.25s cubic-bezier(0.4,0,0.2,1),
              box-shadow 0.25s cubic-bezier(0.4,0,0.2,1);
  animation: wa-pulse 2.5s ease-in-out infinite;
}
.wa-float:hover {
  transform: scale(1.10) translateY(-3px);
  box-shadow: 0 8px 28px rgba(37,211,102,0.55), 0 4px 12px rgba(0,0,0,0.18);
  animation: none;
}
.wa-float svg {
  width: 30px;
  height: 30px;
  fill: #ffffff;
  flex-shrink: 0;
}
@keyframes wa-pulse {
  0%, 100% { box-shadow: 0 4px 16px rgba(37,211,102,0.45), 0 0 0 0 rgba(37,211,102,0.35); }
  50%       { box-shadow: 0 4px 16px rgba(37,211,102,0.45), 0 0 0 10px rgba(37,211,102,0); }
}
"""

WA_HTML = """
<!-- WHATSAPP FLOAT BUTTON -->
<a href="https://wa.me/6282233532724" target="_blank" rel="noopener noreferrer"
   class="wa-float" aria-label="Chat via WhatsApp" title="Hubungi kami via WhatsApp">
  <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
    <path d="M16.002 2.667C8.638 2.667 2.667 8.638 2.667 16c0 2.355.636 4.655 1.842 6.667L2.667 29.333l6.865-1.803A13.255 13.255 0 0016.002 29.333C23.366 29.333 29.333 23.362 29.333 16S23.366 2.667 16.002 2.667zm0 2.4c5.923 0 10.931 5.008 10.931 10.933 0 5.924-5.008 10.933-10.931 10.933a10.878 10.878 0 01-5.526-1.507l-.396-.232-4.073 1.07 1.092-3.972-.257-.41A10.872 10.872 0 015.07 16c0-5.925 5.007-10.933 10.932-10.933zm-3.61 5.6c-.196 0-.515.073-.785.365-.27.293-1.03 1.003-1.03 2.446s1.054 2.836 1.2 3.031c.146.195 2.05 3.264 5.042 4.454.704.277 1.252.443 1.68.568.707.205 1.35.176 1.858.107.567-.077 1.746-.71 1.993-1.397.246-.686.246-1.274.172-1.396-.073-.122-.27-.196-.565-.343-.294-.147-1.744-.858-2.014-.954-.27-.098-.466-.147-.662.147-.196.293-.76.954-.931 1.15-.171.196-.343.22-.637.073-.294-.147-1.242-.458-2.366-1.459-.875-.78-1.466-1.742-1.638-2.036-.171-.293-.018-.452.129-.597.131-.13.294-.343.44-.514.147-.171.196-.294.294-.489.098-.196.049-.367-.025-.514-.073-.147-.662-1.597-.907-2.186-.239-.572-.481-.494-.662-.503l-.564-.01z"/>
  </svg>
</a>
"""

HTML_FILES = [
    "index.html",
    "profil/visi-misi.html",
    "profil/fasilitas.html",
    "profil/jurusan.html",
    "profil/struktur-organisasi.html",
    "profil/staff-guru.html",
    "keuangan/bos.html",
    "keuangan/apbd.html",
    "informasi/berita.html",
    "informasi/prestasi.html",
    "informasi/ekskul.html",
    "kurikulum/index.html",
    "alumni.html",
]

# Also scan for any other HTML files automatically
extra = glob.glob("**/*.html", recursive=True)
all_files = list(set(HTML_FILES + extra))

patched = 0
skipped = 0

for filepath in sorted(all_files):
    if not os.path.isfile(filepath):
        skipped += 1
        continue

    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    # Skip if already patched
    if "wa-float" in content:
        print(f"  [SKIP]  {filepath} — already patched")
        skipped += 1
        continue

    # 1. Inject CSS before </style> (first closing style tag)
    if "</style>" in content:
        content = content.replace("</style>", WA_CSS + "\n</style>", 1)
    else:
        # Inject before </head> if no </style>
        content = content.replace("</head>", f"<style>{WA_CSS}</style>\n</head>", 1)

    # 2. Inject button before </body>
    if "</body>" in content:
        content = content.replace("</body>", WA_HTML + "\n</body>", 1)
    else:
        content += WA_HTML

    with open(filepath, "w", encoding="utf-8") as f:
        f.write(content)

    print(f"  [OK]    {filepath}")
    patched += 1

print(f"\n✅ Done! Patched: {patched}  |  Skipped/Not found: {skipped}")