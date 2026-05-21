#!/usr/bin/env bash
# install-caveman-global.sh
# Jalankan dari /www/wwwroot/app2.yiscalazhar.web.id

set -e

SRC="/www/wwwroot/app2.yiscalazhar.web.id"
BASE="/www/wwwroot"

PROJECTS=(
  "app.yiscalazhar.web.id"
  "app_admin"
  "cloud.yiscalazhar.web.id"
  "e-sii.yiscalazhar.web.id"
  "e-yac.yiscalazhar.web.id"
  "mail.yiscalazhar.web.id"
  "musleng.yiscalazhar.web.id"
  "panel.yiscalazhar.web.id"
  "ppab.yiscalazhar.web.id"
  "ppab_frontend"
  "temp_app2_backup"
  "yisc-alazhar.or.id"
)

GEMINI_CONTENT='@./skills/caveman/SKILL.md
@./skills/caveman-commit/SKILL.md
@./skills/caveman-review/SKILL.md
@./skills/caveman-compress/SKILL.md
'

GITIGNORE_BLOCK='
# Caveman AI skill (server-local, tidak perlu di-commit)
/GEMINI.md
/skills/
'

for PROJECT in "${PROJECTS[@]}"; do
  TARGET="$BASE/$PROJECT"

  if [ ! -d "$TARGET" ]; then
    echo "⚠️  Skip $PROJECT — folder tidak ditemukan"
    continue
  fi

  echo "→ Installing ke $PROJECT..."

  # Copy skill files (force overwrite)
  cp -rf "$SRC/skills" "$TARGET/"

  # Buat GEMINI.md
  echo "$GEMINI_CONTENT" > "$TARGET/GEMINI.md"

  # Update .gitignore
  if [ -f "$TARGET/.gitignore" ]; then
    if ! grep -q "caveman" "$TARGET/.gitignore"; then
      echo "$GITIGNORE_BLOCK" >> "$TARGET/.gitignore"
      echo "   ✓ .gitignore updated"
    else
      echo "   ✓ .gitignore sudah ada entry caveman"
    fi
  else
    echo "$GITIGNORE_BLOCK" > "$TARGET/.gitignore"
    echo "   ✓ .gitignore baru dibuat"
  fi

  echo "   ✓ $PROJECT selesai"
done

echo ""
echo "✅ Caveman terinstall di semua project!"
echo ""
echo "Verifikasi:"
for PROJECT in "${PROJECTS[@]}"; do
  TARGET="$BASE/$PROJECT"
  [ -f "$TARGET/GEMINI.md" ] && echo "  ✓ $PROJECT" || echo "  ✗ $PROJECT (gagal)"
done
