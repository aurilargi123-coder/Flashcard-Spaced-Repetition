# Dokumen Spesifikasi Kebutuhan Perangkat Lunak (SKPL)
## Proyek: FlashMind — Flashcard & Spaced Repetition untuk Belajar Mahasiswa
- **Pengembang**: Auriel Argi (NIM: 240104002)
- **Mata Kuliah**: Rekayasa Perangkat Lunak (D3 Teknik Komputer 24A1)

### 1. Model SDLC: Agile Iterative
Model Agile Iterative dipilih karena pengembangan berfokus pada fitur inti (Core SM-2 & Flashcard CRUD) yang dapat segera diuji dan disempurnakan berdasarkan umpan balik pengguna secara berkelanjutan.

### 2. Kebutuhan Fungsional (FR) & Prioritas MoSCoW
- **FR-01**: Sistem harus menyediakan fitur pembuatan, pengubahan, dan penghapusan dek belajar. (*Must Have*)
- **FR-02**: Sistem harus menyediakan antarmuka flashcard dengan kemampuan membalik kartu (3D flip) untuk melihat pertanyaan dan jawaban. (*Must Have*)
- **FR-03**: Sistem harus menghitung interval pengulangan berkala secara otomatis berdasarkan algoritma SuperMemo-2 (SM-2). (*Must Have*)
- **FR-04**: Sistem harus mencatat riwayat review dan menghasilkan visualisasi statistik retensi belajar (Chart & Heatmap). (*Must Have*)
- **FR-05**: Sistem harus mendukung autentikasi pengguna berbasis email dan isolasi data per akun melalui Row Level Security (RLS). (*Must Have*)
- **FR-06**: Sistem menyediakan mode latihan bebas ketika tidak ada kartu yang jatuh tempo. (*Should Have*)

### 3. Kebutuhan Non-Fungsional (NFR)
- **NFR-01 (Waktu Tanggap)**: Waktu render kartu dan kalkulasi interval SM-2 kurang dari 1 detik.
- **NFR-02 (Keamanan Data)**: Hak akses data dibatasi secara ketat di sisi basis data menggunakan PostgreSQL Row Level Security (RLS).
- **NFR-03 (Kompatibilitas)**: Antarmuka bersifat responsif dan dapat diakses dengan baik pada berbagai peramban modern (Chrome, Edge, Firefox, Safari).
