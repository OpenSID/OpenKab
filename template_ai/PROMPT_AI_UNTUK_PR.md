# 🤖 Prompt AI untuk Generate PR Description (Auto-Analyze Code)

Gunakan salah satu prompt di bawah ini untuk meminta AI analyze code changes dan 
auto-generate PR description lengkap - **TANPA perlu input manual file yang diubah!**

---

## 🚀 FASTEST METHOD: Gunakan GitHub MCP untuk Direct Analysis

**Ini cara TERCEPAT! AI langsung analyze dari GitHub repo tanpa copy-paste manual.**

```
Kamu memiliki akses ke GitHub MCP. 

Tolong analyze PR branch saya di OpenSID/Layanan_OpenDESA repository:
- Branch: dev-1094
- Branch Tujuan: rilis-dev
- Issue: https://github.com/OpenSID/Layanan_OpenDESA/issues/1110

Gunakan GitHub MCP untuk:
1. Fetch semua file changes dari branch ke rilis-dev
2. Read semua file yang diubah untuk memahami context
3. Analyze setiap perubahan code
4. Understand the problem being solved
5. Generate complete PR description

Output format: Use template_ai/TEMPLATE_PR_DESCRIPTION.md dengan section:
- Judul PR yang jelas
- Deskripsi singkat (what & why)
- Perubahan yang dilakukan (dari code analysis)
- Alasan perubahan (dari context)
- Dampak perubahan
- File yang diubah dengan penjelasan detail
- Testing checklist yang di-suggest
- Steps to reproduce (jika applicable)
- Related issue link

Langsung analyze dari GitHub MCP - jangan tanya-tanya lagi!
Catatan:
- simpan hasil ke docs/pr#nomor-issue.md
```

**Keuntungan GitHub MCP:**
- ✅ Langsung akses repo, bukan copy-paste
- ✅ Bisa read actual file content, bukan hanya diff
- ✅ Analyze branch history & commits
- ✅ ~90 detik dari branch ke PR siap pakai
- ✅ Lebih akurat (full context bukan diff summary)

---

## 📋 Backup Method: Auto-Analyze dari Git Diff

**Jika GitHub MCP tidak tersedia, gunakan cara ini dengan git diff:**

```
Tolong analisis code changes dan buatkan PR description untuk Layanan_OpenDESA.

BRANCH PR: feat/hak_akses_kontak
BRANCH TUJUAN: rilis-dev
ISSUE: https://github.com/OpenSID/Layanan_OpenDESA/issues/1110

Lakukan git diff dari BRANCH PR ke BRANCH TUJUAN untuk melihat perubahan, pelajari konteks lebih dalam perbaikan, perubahan, penambahan dll. Contoh: git diff BRANCH TUJUAN...BRANCH PR

Yang saya minta kamu lakukan:
1. **Analyze setiap file yang berubah** - identifikasi apa yang diubah dan alasannya
2. **Understand the context** - dari code changes, apa masalah yang dipecahkan?
3. **Infer the testing** - suggest testing yang perlu dilakukan berdasarkan changes
4. **Generate complete PR description** menggunakan template_ai/TEMPLATE_PR_DESCRIPTION.md dengan section:
   - Deskripsi singkat (what & why)
   - Perubahan yang dilakukan (dari analysis diff)
   - Alasan perubahan (dari context analysis)
   - Dampak perubahan
   - Steps to reproduce (jika applicable)
   - Testing checklist (based on code changes)
   - Related issue link

Jangan tanya-tanya, langsung analyze dan buatkan PR description yang siap copy-paste!
Catatan:
- simpan hasil ke docs/pr#nomor-issue.md
```

---

## 📋 Prompt Alternatif: Dengan Branch Checkout

Jika Anda kasih akses ke local repo, saya bisa direct analyze:

```
Saya sedang di branch [nama-branch] di repo OpenSID Laravel 10.
Issue yang saya tackle: #[nomor issue]

Tolong:
1. Analyze semua file yang saya ubah (bandingkan dengan rilis-dev branch)
2. Pahami apa yang saya ubah dan mengapa
3. Buatkan PR description lengkap secara otomatis
4. Infer testing yang perlu dilakukan

Jangan perlu saya jelaskan detail, cukup analisis code langsung!
```

---

## � Cara Mendapatkan Git Diff (Required untuk Prompt)

### **Windows PowerShell / CMD**

```powershell
# Get diff vs rilis-dev branch
git diff rilis-dev..HEAD

# Save ke file (lebih mudah untuk copy)
git diff rilis-dev..HEAD > pr-changes.diff

# Lihat summary file yang berubah
git diff rilis-dev..HEAD --stat

# Lihat full diff dengan konteks lebih banyak
git diff -U5 rilis-dev..HEAD
```

### **Jika belum push ke remote**

```bash
# Lihat apa yang di-stage
git diff --cached

# Lihat apa yang di-working directory
git diff

# Lihat perbandingan antara 2 commit
git diff <commit-hash> <commit-hash>
```

### **Verify Diff Sebelum Copy ke AI**

```powershell
# Check branch mana yg active
git branch

# Check remote
git remote -v

# Verify perubahan
git diff rilis-dev..HEAD --name-only  # Hanya nama file
git diff rilis-dev..HEAD --name-status # File + status (added/modified/deleted)
git diff rilis-dev..HEAD --stat        # Summary
```

---

## ✨ Contoh Real Usage: Auto-Analyze Perubahan

### **Scenario 1: Bug Fix Kamera (Issue #10716)**

**Langkah 1: Get diff**
```powershell
git diff rilis-dev..HEAD > changes.diff
```

**Langkah 2: Copy ke AI dengan prompt**
```
Branch: fix/#10716-permissions-policy-camera
Issue: #10716

GIT DIFF:
\`\`\`diff
diff --git a/donjo-app/core/MY_Controller.php b/donjo-app/core/MY_Controller.php
index 1a2b3c4...5d6e7f8 100644
--- a/donjo-app/core/MY_Controller.php
+++ b/donjo-app/core/MY_Controller.php
@@ -1,6 +1,5 @@
 <?php
 
-use App\Http\Middleware\SecurityHeaders;
 
 class MY_Controller extends CI_Controller
 {
@@ -10,7 +9,6 @@ class MY_Controller extends CI_Controller
 	public function __construct()
 	{
 		parent::__construct();
-		SecurityHeaders::handle();
 		// ... rest of code
 	}
 }

diff --git a/donjo-app/core/Web_Controller.php b/donjo-app/core/Web_Controller.php
index 3e4c5d6...7f8a9b0 100644
--- a/donjo-app/core/Web_Controller.php
+++ b/donjo-app/core/Web_Controller.php
@@ -1,11 +1,13 @@
 <?php
 
+use App\Http\Middleware\SecurityHeaders;
 
 class Web_Controller extends MY_Controller
 {
 	public function __construct()
 	{
 		parent::__construct();
 		$CI = &get_instance();
+		SecurityHeaders::handle();
 		$this->header = identitas();
 		// ... rest of code
 	}
 }

diff --git a/tests/playwright/e2e/bugs/issue-10716.spec.ts b/tests/playwright/e2e/bugs/issue-10716.spec.ts
new file mode 100644
index 0000000..1a2b3c4
--- /dev/null
+++ b/tests/playwright/e2e/bugs/issue-10716.spec.ts
@@ -0,0 +1,45 @@
+import { test, expect } from '@playwright/test';
+
+test('Camera feature should work on admin edit resident page', async ({ page }) => {
+  // Navigate and test camera access
+  // ... test code
+});
\`\`\`

Analyze code changes dan buatkan PR description lengkap otomatis!
```

**Langkah 3: AI analyze dan hasilkan PR description** ✅

---

### **Scenario 2: Feature Baru (Export Excel)**

**Get diff:**
```powershell
git diff rilis-dev..HEAD
```

**Kirim ke AI:**
```
Branch: feature/export-excel
Issue: #12345

GIT DIFF:
\`\`\`diff
[PASTE FULL DIFF]
\`\`\`

Analyze perubahan dan buatkan PR description dengan auto-detect:
- Apa feature yang ditambahkan
- File mana yang baru
- File mana yang dimodifikasi
- Alasan perubahan
- Testing yang perlu dilakukan
```

---

### **Scenario 3: Refactor Controller**

**Kirim diff ke AI dan let it analyze:**
```
Branch: refactor/controller-cleanup
Issue: #11111

GIT DIFF:
\`\`\`diff
[PASTE FULL DIFF dari semua file yang di-refactor]
\`\`\`

Auto-analyze dan buatkan PR description!
```

---

## 💬 Prompt Interaktif: Tanya-Jawab untuk Analisis Lebih Detail

Jika Anda ingin AI ask clarifying questions sebelum generate PR description:

```
Berikut adalah git diff untuk PR saya:

BRANCH: [nama branch]
ISSUE: #[nomor]

\`\`\`diff
[PASTE GIT DIFF]
\`\`\`

Tolong analyze dan tanya pertanyaan clarifying questions jika diperlukan untuk
generate PR description yang lebih akurat:
- Apa masalah utama yang dipecahkan PR ini?
- Apakah ada breaking changes?
- Bagaimana cara test feature ini?
- Ada side effects yang perlu diperhatikan?

Setelah dapat jawaban, buatkan PR description lengkap.
```

## 🔍 Analisis yang AI Lakukan Otomatis dari Code Diff

Ketika Anda paste git diff, AI akan otomatis:

### **1. Identifikasi File Changes**
- File baru/added (`+`)
- File modified (`~`)
- File deleted (`-`)
- File renamed

### **2. Analyze Code Changes per File**
- Baca setiap `@@ @@` hunk
- Pahami context dari perubahan
- Identify function/method yang diubah
- Identify import/dependency yang ditambah/hapus

### **3. Infer Problem & Solution**
```
Contoh:
- Jika ada SecurityHeaders::handle() dihapus dari MY_Controller
  → Problem: Security headers diterapkan ke semua halaman
  → Solution: Move SecurityHeaders ke Web_Controller aja
```

### **4. Determine PR Type**
```
- Banyak deleted code? → Refactor atau Cleanup
- Banyak new files? → Feature baru
- Bug fix berdasarkan issue? → Bug fix
- Tests ditambahkan? → Biasanya feature atau bug fix
```

### **5. Auto-Generate Documentation**
- Deskripsi singkat
- Perubahan yang dilakukan
- Alasan perubahan
- Impact analysis
- Suggested testing
- File-by-file explanation

### **6. Suggest Testing Strategy**
Berdasarkan code changes:
- Manual testing steps
- Unit tests yang harus di-check
- Integration tests yang relevant
- Edge cases

---

## 🎯 Step-by-Step Workflow

### **1. Get Git Diff**
```powershell
# Ensure you're on correct branch
git branch

# Get full diff
git diff rilis-dev..HEAD > my-changes.diff

# Verify changes
git diff rilis-dev..HEAD --name-status
```

### **2. Copy Entire Diff to AI**
```
BRANCH: [nama-branch-anda]
ISSUE: #[nomor-issue]

GIT DIFF (dari: git diff rilis-dev..HEAD):
\`\`\`diff
[PASTE SELURUH ISI FILE my-changes.diff]
\`\`\`

Analyze dan generate PR description otomatis!
```

### **3. Receive Complete PR Description**
AI akan deliver:
- ✅ Title dengan format yang benar
- ✅ Deskripsi lengkap dengan context
- ✅ List semua file yang berubah dengan penjelasan
- ✅ Alasan perubahan dan impact analysis
- ✅ Testing checklist berdasarkan code changes
- ✅ Steps to reproduce (jika applicable)
- ✅ Related issue link
- ✅ Siap copy-paste ke GitHub! 🚀

### **4. Copy-Paste to GitHub PR**
Tinggal copy-paste hasil generate ke GitHub dan submit PR!

---

## 🔐 Privacy & Security

- Git diff hanya contain code changes, bukan secrets
- Jika ada config/credentials terlihat di diff, harap remove sebelum copy ke AI
- Aman untuk di-paste ke public conversation

---

## 📊 Perbandingan: Manual vs Git Diff vs GitHub MCP

| Aspek | Manual Input | Git Diff | GitHub MCP |
|-------|--------------|----------|-----------|
| Setup | 0 (instant) | ~1 min | ~1 min |
| Copy-paste | ✅ Manual | ✅ Manual | ❌ None |
| Fetch changes | ❌ Manual | ✅ Local git | ✅ API |
| Read full file | ❌ No | ❌ Diff only | ✅ Yes |
| Understand context | ⏱️ 10 min | ⏱️ 5 min | ✅ 30 sec |
| Analyze code | ⏱️ 15 min | ⏱️ 5 min | ✅ Otomatis |
| Generate desc | ⏱️ 10 min | ⏱️ 2 min | ✅ ~30 sec |
| **Total waktu** | **~50 min** | **~5 min** | **~2 min** |
| Accuracy | 70% | 85% | **95%+** |

---

---

## ⚠️ Tips Penting

### **Pastikan diff clean:**
```bash
# Jangan ada merge conflicts
git status

# Hanya diff terhadap base branch
git diff rilis-dev..HEAD  # ✅ Benar

git diff HEAD~1..HEAD     # ❌ Salah (bisa include commits lain)
```

### **Minimal context dalam diff:**
```bash
# Default context 3 lines, bisa perbesar jika perlu
git diff -U10 rilis-dev..HEAD  # 10 lines context
```

### **Untuk file yang banyak berubah:**
```bash
# Split menjadi beberapa diff jika perlu
git diff rilis-dev..HEAD -- "donjo-app/**" > backend-changes.diff
git diff rilis-dev..HEAD -- "tests/**" > test-changes.diff
```

---

## 📊 Contoh Penggunaan Real

### **Contoh 1: Bug Fix Kamera**

```
Saya sudah fix bug kamera di halaman admin OpenSID.

Perubahan:
- Hapus SecurityHeaders::handle() dari MY_Controller.php
- Tambah SecurityHeaders::handle() ke Web_Controller.php
- Tambah test file tests/playwright/e2e/bugs/issue-10716.spec.ts

Issue: #10716

Alasan: Permissions-Policy camera=() di MY_Controller memblokir akses kamera 
di halaman admin, padahal admin butuh kamera untuk upload foto.

Testing:
- Manual test kamera di halaman edit penduduk ✅
- Manual test kamera di halaman edit kelompok ✅
- Playwright test untuk memverifikasi Permissions-Policy ✅

Tolong buatkan PR description yang detail dan terstruktur sesuai template.
```

### **Contoh 2: Feature Baru**

```
Saya buat feature baru di OpenSID untuk export data ke Excel.

File baru:
- app/Exports/PendudukExport.php
- app/Http/Controllers/Admin/ExportController.php
- resources/views/admin/export/penduduk.blade.php

File modified:
- routes/web.php (tambah route export)
- app/Models/Penduduk.php (tambah method untuk export)

Issue: #12345

Testing:
- Unit test untuk ExportController
- Manual test export 100 records
- Manual test export dengan filter

Tolong buatkan PR description lengkap dengan section fitur, implementation detail, testing.
```

---

## 🚀 Tips Menggunakan Prompt

### **1. Berikan Info Sebanyak Mungkin**
Semakin detail info yang Anda berikan, semakin bagus PR description yang dihasilkan.

```
❌ Tidak ideal:
"Fix kamera"

✅ Ideal:
"Fix issue #10716 - Permissions-Policy camera=() memblokir akses kamera 
di halaman admin karena diterapkan di MY_Controller. Solusi: pindahkan 
SecurityHeaders::handle() ke Web_Controller agar hanya berlaku di frontend."
```

### **2. Sertakan Context Repository**
```
"Saya sedang develop untuk OpenSID Laravel 10 (branch opensid-laravel-10, 
base branch rilis-dev). Project menggunakan Laravel 10, CodeIgniter legacy, 
Playwright untuk e2e testing, dan Vite untuk asset bundling."
```

### **3. Sebutkan File yang Diubah**
```
"File yang diubah:
- donjo-app/core/MY_Controller.php (hapus SecurityHeaders)
- donjo-app/core/Web_Controller.php (tambah SecurityHeaders)
- tests/playwright/e2e/bugs/issue-10716.spec.ts (new file)"
```

### **4. Jelaskan Testing yang Sudah Dilakukan**
```
"Testing yang sudah dilakukan:
- Manual: buka halaman admin, test fitur kamera ✅
- Manual: test di Chrome, Firefox ✅
- Playwright: verify Permissions-Policy tidak ada di admin ✅
- Playwright: verify akses camera API berhasil ✅"
```

---

## 📋 Template Prompt Siap Pakai - Copy-Paste Langsung

### **Template 1: Minimal & Fast**

```
BRANCH: [nama-branch]
ISSUE: #[nomor]

GIT DIFF:
\`\`\`diff
[PASTE GIT DIFF]
\`\`\`

Generate PR description!
```

### **Template 2: Dengan Context Tambahan**

```
BRANCH: [nama-branch]
ISSUE: #[nomor]
TIPE PR: [Fix/Feature/Refactor]

GIT DIFF (dari: git diff rilis-dev..HEAD):
\`\`\`diff
[PASTE GIT DIFF]
\`\`\`

TESTING DILAKUKAN:
- [Test 1]
- [Test 2]

AUTO-ANALYZE diff dan generate PR description lengkap!
```

### **Template 3: Dengan Questioning**

```
BRANCH: [nama-branch]
ISSUE: #[nomor]

GIT DIFF:
\`\`\`diff
[PASTE GIT DIFF]
\`\`\`

TANYA: [Opsi jika ada ambiguitas]
- Adalah ini bug fix untuk issue #XXX?
- Feature apa yang ditambahkan?
- Ada breaking changes?

Analyze, clarify jika perlu, terus generate PR description!
```

---

## ✅ Checklist Sebelum Kirim Prompt ke AI

- [ ] Branch sudah di-checkout dengan benar
- [ ] Sudah run: `git diff rilis-dev..HEAD`
- [ ] Copy seluruh output diff (jangan partial)
- [ ] Tahu nomor issue yang ditargetkan
- [ ] Sudah test locally (minimal manual test)
- [ ] Git diff tidak mengandung credentials/secrets

---

## 🎬 Live Example: Step-by-Step Walkthrough

### **Scenario: Fix Issue #10716 - Camera Permission Bug**

**Step 1: Checkout branch**
```powershell
git checkout fix/#10716-permissions-policy-camera
```

**Step 2: Get diff**
```powershell
git diff rilis-dev..HEAD > changes.diff
```

**Step 3: View changes summary**
```powershell
git diff rilis-dev..HEAD --stat

# Output:
#  donjo-app/core/MY_Controller.php             |  2 -
#  donjo-app/core/Web_Controller.php            |  3 +
#  tests/playwright/e2e/bugs/issue-10716.spec.ts | 45 +++++++
#  3 files changed, 46 insertions(+), 2 deletions(-)
```

**Step 4: Copy diff ke AI**
```
BRANCH: fix/#10716-permissions-policy-camera
ISSUE: #10716

GIT DIFF:
\`\`\`diff
diff --git a/donjo-app/core/MY_Controller.php b/donjo-app/core/MY_Controller.php
index abc123..def456 100644
--- a/donjo-app/core/MY_Controller.php
+++ b/donjo-app/core/MY_Controller.php
@@ -1,6 +1,5 @@
 <?php
 
-use App\Http\Middleware\SecurityHeaders;
 
 class MY_Controller extends CI_Controller
 {
@@ -10,7 +9,6 @@ class MY_Controller extends CI_Controller
 	public function __construct()
 	{
 		parent::__construct();
-		SecurityHeaders::handle();
 		parent::__construct();
 		$config = config('app');
 	}

[... rest of diff ...]
\`\`\`

Analyze code changes dan generate PR description lengkap!
```

**Step 5: Terima PR description siap pakai** ✅

**Step 6: Copy-paste ke GitHub**
- Create PR di GitHub
- Paste hasil ke description
- Submit! 🚀

---

## 🔗 Pro Tips: Integrasi dengan Workflow

### **Alias Git untuk Quick Diff**

```bash
# Add ke ~/.bashrc atau PowerShell profile
git config --global alias.prdiff '!git diff rilis-dev..HEAD'

# Kemudian tinggal:
git prdiff | clip  # Copy to clipboard (Windows)
git prdiff | pbcopy # Copy to clipboard (Mac)
```

### **OneLiners untuk Copy-Paste**

```powershell
# Windows PowerShell - Get diff dan copy to clipboard langsung
git diff rilis-dev..HEAD | Set-Clipboard

# PowerShell - Save ke file
git diff rilis-dev..HEAD > pr-$(get-date -format yyyy-MM-dd-HHmm).diff

# List files only
git diff rilis-dev..HEAD --name-only
```

### **Verify sebelum submit PR**

```bash
# Validate branch
git branch -vv  # Verify tracking

# Count commits
git rev-list --count rilis-dev..HEAD

# Log commits
git log rilis-dev..HEAD --oneline
```

---

## 💡 FAQ

**Q: Apakah bisa analyze hanya sebagian dari diff?**
A: Bisa, tapi lebih baik full diff agar AI dapat context lengkap.

**Q: Bagaimana jika diff sangat besar?**
A: AI bisa handle sampai ratusan line diff. Jika terlalu besar, split menjadi multiple PRs.

**Q: Apakah secure untuk paste diff yang contain API keys?**
A: JANGAN! Hapus credentials sebelum paste. Gunakan git diff --ignore-all-space untuk skip secrets.

**Q: Bisa analyze multiple branches sekaligus?**
A: Bisa, tapi recommend satu per satu untuk clarity.

**Q: Apakah hasil PR description 100% accurate?**
A: ~95%. Review hasil sebelum submit ke GitHub, specially untuk:
- Complex business logic
- Breaking changes
- Migration guides

---

## 🚀 Quick Start (Fastest Way)

### **Option 1: Gunakan GitHub MCP (RECOMMENDED)**

```powershell
# Cukup bilang ke AI:
# "Analyze branch fix/#10716-camera di OpenSID/premium (base: rilis-dev, issue: #10716) 
#  menggunakan GitHub MCP dan generate PR description!"

# AI akan langsung:
# 1. Access repo via GitHub MCP
# 2. Fetch all changes
# 3. Analyze code
# 4. Generate PR description
# 5. Siap copy-paste! ✅

# Total: ~90 detik!
```

### **Option 2: Jika GitHub MCP tidak tersedia**

```powershell
# 1. Get diff
git diff rilis-dev..HEAD > changes.diff

# 2. Open changes.diff dan copy seluruh isinya

# 3. Kirim prompt ke AI:
# "BRANCH: [nama-branch]
#  ISSUE: #[nomor]
#  GIT DIFF:
#  \`\`\`diff
#  [PASTE SELURUH DIFF]
#  \`\`\`
#  Generate PR description!"

# 4. Copy hasil ke GitHub PR description

# 5. Submit PR! 🎉

# Total: ~5 menit
```

---

## 🔗 Info GitHub MCP untuk AI Analysis

Ketika meminta AI analyze menggunakan GitHub MCP, pastikan berikan:

```
Repository: OpenSID/premium
Owner: OpenSID
Branch to analyze: [nama-branch-anda]
Base branch: rilis-dev
Issue number: #[nomor-issue]
```

**AI akan bisa:**
- ✅ Fetch branch files langsung dari GitHub
- ✅ Read actual code (bukan hanya diff)
- ✅ Analyze git log/commits
- ✅ Compare dengan rilis-dev branch
- ✅ Understand full context dari changes
- ✅ Generate PR description yang akurat

---

## 📝 Template Prompt untuk GitHub MCP Analysis

**FASTEST METHOD:**

```
Gunakan GitHub MCP untuk analyze branch berikut:

REPOSITORY: OpenSID/premium
BRANCH: [nama-branch-anda]
BASE_BRANCH: rilis-dev
ISSUE: #[nomor-issue]

Fetch semua changes, read code, dan generate PR description lengkap
sesuai template_ai/TEMPLATE_PR_DESCRIPTION.md dengan section:
- Judul PR
- Deskripsi singkat
- Perubahan yang dilakukan
- Alasan perubahan
- Dampak perubahan
- File yang diubah
- Testing checklist
- Related issue

Langsung analyze dari repo - output siap copy-paste ke GitHub! 🚀
```

---

**Total waktu dari code changes ke PR siap submit:**
- **Dengan GitHub MCP: ~90 detik** ⚡⚡⚡ (FASTEST!)
- **Dengan Git Diff: ~5 menit** ⚡
