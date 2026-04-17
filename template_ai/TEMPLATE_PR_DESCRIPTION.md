# Template Prompt untuk Membuat PR Description

Gunakan template ini sebagai panduan untuk membuat PR description yang lengkap dan terstruktur.

---

## Instruksi Penggunaan

1. **Ganti semua placeholder** yang ditandai dengan `[...]` dengan informasi spesifik dari PR Anda
2. **Hapus section** yang tidak relevan dengan PR Anda
3. **Pastikan checklist** sudah diisi dengan benar
4. **Review kembali** sebelum submit

---

## Template PR Description

```markdown
# Pull Request: [Judul PR yang jelas dan deskriptif]

## Deskripsi

[Jelaskan secara singkat apa yang PR ini lakukan. Tuliskan masalah yang sedang dipecahkan dan solusi yang diberikan dalam 2-3 kalimat]

### Perubahan yang dilakukan:

1. **[Tipe Perubahan]**: [Deskripsi perubahan di file/modul]
2. **[Tipe Perubahan]**: [Deskripsi perubahan di file/modul]
3. **[Tipe Perubahan]**: [Deskripsi perubahan di file/modul]
4. **[Tipe Perubahan]**: [Deskripsi perubahan di file/modul]
5. **[Tipe Perubahan]**: [Deskripsi perubahan di file/modul]

### Alasan perubahan:

- **Poin 1**: [Jelaskan mengapa perubahan ini diperlukan]
- **Poin 2**: [Jelaskan dampak positif dari perubahan]
- **Poin 3**: [Jelaskan bagaimana ini memecahkan masalah]

### Dampak perubahan:

✅ **Aspek 1**: [Deskripsi dampak positif]  
✅ **Aspek 2**: [Deskripsi dampak positif]  
✅ **Aspek 3**: [Deskripsi dampak positif]

## Masalah Terkait (Related Issue)

- Solusi untuk perbaikan terkait issue #[nomor issue]

[Link ke issue di GitHub]

## Langkah untuk mereproduksi (Steps to Reproduce)

### Sebelum perbaikan (masalah):
1. [Langkah 1]
2. [Langkah 2]
3. [Langkah 3]
4. [Langkah 4]
5. ❌ [Hasil yang tidak diinginkan/error]

### Setelah perbaikan (fix):
1. [Langkah 1]
2. [Langkah 2]
3. [Langkah 3]
4. [Langkah 4]
5. ✅ [Hasil yang diinginkan/expected behavior]

### Testing pada fitur lain yang terkait:
- [Fitur 1] ✅ [Status]
- [Fitur 2] ✅ [Status]
- [Fitur 3] ✅ [Status]

## Daftar Periksa (Checklist)
- [ ] Saya telah mematuhi [aturan penulisan script](https://github.com/OpenSID/OpenSID/wiki/Aturan-Penulisan-Script).
- [ ] Saya telah mengikuti [proses review pull request](https://github.com/OpenSID/OpenSID/wiki/proses-review-pull-request).
- [ ] Saya telah membuat [unit test/integration test] untuk memverifikasi perbaikan
- [ ] Testing manual telah dilakukan di environment development
- [ ] Tidak ada console error atau warning
- [ ] Code sudah di-review oleh [minimal 1 orang]

## Teknis Detail

### Penjelasan Teknis
[Jelaskan detail teknis, arsitektur, atau flow yang berubah. Gunakan code snippets jika diperlukan]

### Konfigurasi yang berubah
[Jika ada perubahan di config files, sebutkan di sini]

### Dependencies yang ditambahkan
- [Package 1]: [Versi]
- [Package 2]: [Versi]

[Atau tuliskan "Tidak ada dependencies baru" jika tidak ada]

## Testing

### Manual Testing
- [ ] [Test Case 1]
- [ ] [Test Case 2]
- [ ] [Test Case 3]
- [ ] [Regression Testing - test fitur yang sudah ada tidak rusak]

### Automated Testing
- [ ] [Unit Test - Nama test]
- [ ] [Integration Test - Nama test]
- [ ] [Playwright Test - Nama test]

### Browser Compatibility (jika applicable)
- [ ] Chrome/Edge (Chromium)
- [ ] Firefox
- [ ] Safari

## Screenshots / Video

[Jika ada perubahan UI, tambahkan screenshots atau GIF yang menunjukkan sebelum dan sesudah]

### Sebelum:
[Screenshot atau deskripsi]

### Sesudah:
[Screenshot atau deskripsi]

## Breaking Changes
[Tuliskan "Tidak ada" jika tidak ada breaking changes, atau jelaskan breaking changes jika ada]

## Migration Guide
[Jika diperlukan, jelaskan langkah-langkah migration. Atau tuliskan "Tidak diperlukan" jika tidak perlu]

## References
- [Reference 1]: [Link]
- [Reference 2]: [Link]
- [Documentation]: [Link]

---

**Catatan tambahan:** [Tuliskan catatan penting, pertanyaan untuk reviewer, atau informasi tambahan lainnya]
```

---

## Panduan Penulisan PR Description yang Baik

### ✅ DO's (Hal yang harus dilakukan)

1. **Judul yang jelas**
   - Gunakan format: `[Fix|Feature|Refactor|Docs]: Deskripsi singkat`
   - Contoh: `Fix: Permissions Policy untuk fitur kamera di halaman admin`

2. **Deskripsi ringkas**
   - Jelaskan "apa" dan "mengapa", bukan "bagaimana"
   - Gunakan 2-3 kalimat untuk overview

3. **Perubahan terstruktur**
   - Gunakan bullet points dengan nomor
   - Jelaskan setiap perubahan dengan singkat

4. **Alasan yang jelas**
   - Jelaskan masalah yang sedang dipecahkan
   - Jelaskan mengapa solusi ini dipilih
   - Jelaskan dampak perubahan

5. **Steps to Reproduce**
   - Gunakan untuk fitur baru atau bug fix
   - Jelaskan sebelum dan sesudah perbaikan
   - Gunakan emoji (❌ untuk error, ✅ untuk success)

6. **File changes dengan context**
   - Tampilkan diff yang relevan
   - Gunakan minimal 3 baris context sebelum-sesudah
   - Jelaskan alasan setiap perubahan file

7. **Testing checklist**
   - Pastikan semua test case tercakup
   - Jelaskan jenis testing yang dilakukan
   - Sebutkan browser/environment yang di-test

### ❌ DON'Ts (Hal yang tidak boleh dilakukan)

1. ❌ Judul yang terlalu panjang atau ambigu
2. ❌ Deskripsi yang terlalu teknis tanpa context
3. ❌ Menampilkan diff tanpa penjelasan
4. ❌ Lupa mencantumkan issue link yang terkait
5. ❌ Tidak menjalankan testing sebelum submit
6. ❌ Menuliskan commit message yang tidak informatif
7. ❌ PR tanpa checklist yang lengkap

---

## Tips Tambahan

### 1. Link ke Issue
```markdown
Closes #10716
Fixes #10716
Related to #10716
```

### 2. Referensi External
```markdown
- [Permissions Policy MDN](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Permissions-Policy)
- [Video Tutorial](https://...)
- [Documentation](https://...)
```

### 3. Code Block Syntax
```markdown
# Untuk PHP
\`\`\`php
// Kode PHP di sini
\`\`\`

# Untuk TypeScript
\`\`\`typescript
// Kode TypeScript di sini
\`\`\`

# Untuk Diff
\`\`\`diff
- Baris yang dihapus
+ Baris yang ditambah
  Baris yang tidak berubah
\`\`\`
```

### 4. Emoji untuk Status
- ✅ Berhasil / Done
- ❌ Error / Failed
- ⚠️ Warning / Perhatian
- 📝 Catatan / Note
- 🔗 Link
- 🎯 Target / Goal

---

## Contoh Struktur Minimal untuk PR Kecil

Untuk PR yang relatif sederhana, gunakan struktur minimal ini:

```markdown
# Pull Request: [Judul]

## Deskripsi
[Jelaskan singkat apa yang dilakukan]

## Perubahan
- [Perubahan 1]
- [Perubahan 2]

## Testing
- [ ] [Test 1]
- [ ] [Test 2]

## Checklist
- [x] Code sesuai aturan penulisan
- [x] Testing manual dilakukan
- [x] Tidak ada breaking changes
```

---

## Template untuk Issue-Specific PR's

### Untuk Bug Fix
```markdown
# Pull Request: Fix [Deskripsi Bug]

## Deskripsi
[Jelaskan bug dan cara memperbaikinya]

## Related Issue
Closes #[nomor]

## Steps to Reproduce
[Langkah-langkah untuk melihat bug]

## Solution
[Penjelasan solusi]

## Testing
- [ ] Bug sudah diperbaiki
- [ ] Tidak ada regression
```

### Untuk Feature
```markdown
# Pull Request: Feature [Nama Fitur]

## Deskripsi
[Jelaskan fitur baru]

## Scope
- [x] Feature 1
- [x] Feature 2
- [ ] Feature 3 (untuk PR berikutnya)

## Testing
- [ ] Feature berfungsi sesuai requirement
- [ ] UI/UX sudah di-review
```

### Untuk Refactor
```markdown
# Pull Request: Refactor [Modul/Class]

## Tujuan Refactor
[Jelaskan mengapa perlu refactor]

## Perubahan
- [Perubahan 1]
- [Perubahan 2]

## Impact
- ✅ Code lebih maintainable
- ✅ Performance [meningkat/tetap sama]

## Testing
- [ ] Existing test masih passing
- [ ] Tidak ada breaking changes
```

---

**Ingat:** PR description yang baik membantu reviewer memahami konteks dengan cepat dan mempercepat proses review! 🚀
