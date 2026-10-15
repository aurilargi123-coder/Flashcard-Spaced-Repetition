# 🧠 FlashMind — Aplikasi Flashcard & Spaced Repetition

Aplikasi web modern berbasis **Supabase** dan **Algoritma SuperMemo 2 (SM-2)** yang dirancang untuk membantu mahasiswa mengingat materi perkuliahan secara optimal dengan teknik pengulangan berkala (*Spaced Repetition System*).

- **Pengembang**: Auriel Argi (NIM: 240104002 - Versi Stabil & Modul SM-2 Terintegrasi)
- **No. 15**: Aplikasi Flashcard & Spaced Repetition
- **Fitur Kunci**: Pembuatan dek belajar, algoritma pengulangan berkala, statistik retensi
- **Pemanfaatan Fitur Supabase**: PostgreSQL Functions/RPC (perhitungan interval memori), Supabase Auth per pengguna, Row Level Security (RLS)

---

## 🚀 Cara Menjalankan Aplikasi

Aplikasi ini bersifat **portabel dan mandiri (single-page self-contained HTML)**. Anda cukup:
1. Klik dua kali atau buka berkas `Flashcard App.html` di browser apa pun (Google Chrome, Microsoft Edge, Firefox, Brave, Safari).
2. Anda langsung dapat menggunakan **Mode Demo (Tamu)** dengan data sampel perkuliahan yang sudah terisi otomatis (dek mata kuliah, flashcard, dan riwayat belajar 7 hari).
3. Untuk menghubungkan ke database **Supabase** riil, buka menu **⚙️ Setup & Supabase** di dalam aplikasi, masukkan **Project URL** dan **Anon Key**, lalu klik **Simpan & Sambungkan**.

---

## 📋 Fitur Utama & Kesesuaian Kebutuhan UTS

| Requirement UTS | Implementasi di FlashMind | Status |
|---|---|:---:|
| **Pembuatan Dek Belajar** | Buat, edit, hapus dek belajar dengan judul, deskripsi, dan kode warna visual. Manajemen flashcard (pertanyaan & jawaban) di setiap dek. | ✅ Selesai |
| **Algoritma Pengulangan Berkala** | Menggunakan algoritma **SuperMemo 2 (SM-2)** dengan 4 tingkatan respon: *Lagi (Quality 1)*, *Sulit (Quality 3)*, *Baik (Quality 4)*, dan *Mudah (Quality 5)*. Dilengkapi animasi kartu 3D flip dan pintasan keyboard (*Space* untuk membalik, angka *1-4* untuk rating). | ✅ Selesai |
| **Statistik Retensi Memori** | - Persentase tingkat retensi sukses (sukses recall vs gagal)<br>- Pelacak *Study Streak* (hari berturut-turut)<br>- Grafik batang aktivitas review 7 hari (*Chart.js*)<br>- Diagram donat distribusi respon kualitas memori<br>- Status kematangan kartu (*Baru*, *Sedang Dipelajari*, *Sudah Dikuasai*)<br>- Heatmap kalender belajar 365 hari ala GitHub | ✅ Selesai |
| **Supabase Auth** | Autentikasi pengguna berbasis email & password, sesi persisten via Supabase Auth SDK. | ✅ Selesai |
| **PostgreSQL Functions / RPC** | Stored Function `calculate_next_review(p_card_id, p_quality)` yang menghitung interval dan *Ease Factor* langsung di server database (server-side logic). | ✅ Selesai |
| **Row Level Security (RLS)** | Kebijakan akses data ketat di PostgreSQL sehingga pengguna hanya dapat membaca, menambah, dan mengubah dek, kartu, serta riwayat review miliknya sendiri (`auth.uid() = user_id`). | ✅ Selesai |

---

## 🗄️ Skema Database & Stored Function (Supabase)

Salin kode SQL berikut ke **SQL Editor** pada dashboard Supabase Anda:

```sql
-- 1. Enable UUID Extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 2. Tabel Dek Belajar (Decks)
CREATE TABLE IF NOT EXISTS decks (
  id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
  user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
  title TEXT NOT NULL,
  description TEXT,
  color TEXT DEFAULT '#6366f1',
  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 3. Tabel Kartu Flashcard (Cards)
CREATE TABLE IF NOT EXISTS cards (
  id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
  deck_id UUID REFERENCES decks(id) ON DELETE CASCADE,
  front TEXT NOT NULL,
  back TEXT NOT NULL,
  ease_factor FLOAT DEFAULT 2.5,
  interval INTEGER DEFAULT 0,
  repetitions INTEGER DEFAULT 0,
  next_review TIMESTAMPTZ DEFAULT NOW(),
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 4. Tabel Riwayat Review (Review History)
CREATE TABLE IF NOT EXISTS review_history (
  id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
  card_id UUID REFERENCES cards(id) ON DELETE CASCADE,
  user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
  quality INTEGER NOT NULL CHECK (quality >= 0 AND quality <= 5),
  reviewed_at TIMESTAMPTZ DEFAULT NOW()
);

-- 5. Aktifkan Row Level Security (RLS)
ALTER TABLE decks ENABLE ROW LEVEL SECURITY;
ALTER TABLE cards ENABLE ROW LEVEL SECURITY;
ALTER TABLE review_history ENABLE ROW LEVEL SECURITY;

-- 6. Kebijakan RLS (Security Policies)
CREATE POLICY "Users can CRUD own decks"
  ON decks FOR ALL
  USING (auth.uid() = user_id);

CREATE POLICY "Users can CRUD cards in own decks"
  ON cards FOR ALL
  USING (deck_id IN (SELECT id FROM decks WHERE user_id = auth.uid()));

CREATE POLICY "Users can CRUD own reviews"
  ON review_history FOR ALL
  USING (auth.uid() = user_id);

-- 7. PostgreSQL Function / RPC: Algoritma Spaced Repetition SM-2
CREATE OR REPLACE FUNCTION calculate_next_review(
  p_card_id UUID,
  p_quality INTEGER
) RETURNS JSON AS $$
DECLARE
  v_card cards%ROWTYPE;
  v_ease FLOAT;
  v_interval INTEGER;
  v_reps INTEGER;
BEGIN
  SELECT * INTO v_card FROM cards WHERE id = p_card_id;
  
  v_ease := v_card.ease_factor;
  v_interval := v_card.interval;
  v_reps := v_card.repetitions;
  
  -- Algoritma SuperMemo 2 (SM-2)
  IF p_quality >= 3 THEN
    IF v_reps = 0 THEN
      v_interval := 1;
    ELSIF v_reps = 1 THEN
      v_interval := 6;
    ELSE
      v_interval := ROUND(v_interval * v_ease);
    END IF;
    v_reps := v_reps + 1;
  ELSE
    v_reps := 0;
    v_interval := 1;
  END IF;
  
  -- Penyesuaian Ease Factor
  v_ease := v_ease + (0.1 - (5 - p_quality) * (0.08 + (5 - p_quality) * 0.02));
  IF v_ease < 1.3 THEN v_ease := 1.3; END IF;
  
  -- Perbarui status kartu di database
  UPDATE cards SET
    ease_factor = v_ease,
    interval = v_interval,
    repetitions = v_reps,
    next_review = NOW() + (v_interval || ' days')::INTERVAL
  WHERE id = p_card_id;
  
  -- Catat riwayat review untuk statistik retensi
  INSERT INTO review_history (card_id, user_id, quality)
  VALUES (p_card_id, (SELECT user_id FROM decks WHERE id = v_card.deck_id), p_quality);
  
  RETURN json_build_object(
    'ease_factor', v_ease,
    'interval', v_interval,
    'repetitions', v_reps,
    'next_review', NOW() + (v_interval || ' days')::INTERVAL
  );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;
```

---

## 📂 Struktur Berkas

- `Flashcard App.html` — Aplikasi lengkap (HTML, CSS modern tema gelap/terang, JavaScript, integrasi Supabase JS SDK & Chart.js).
- `README.md` — Dokumentasi lengkap aplikasi, skema basis data, dan panduan penggunaan.
