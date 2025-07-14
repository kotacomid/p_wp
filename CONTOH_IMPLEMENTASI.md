# Contoh Implementasi Plugin Kotacom AI

## 1. Setup API Rotator

### Langkah-langkah Setup:

1. **Akses API Rotator**:
   - Login ke WordPress Admin
   - Ke menu: `Kotacom AI > API Rotator`

2. **Tambah Multiple API Keys**:
   ```
   Provider: Google AI
   API Key 1: AIzaSyAbc123...
   API Key 2: AIzaSyDef456...
   API Key 3: AIzaSyGhi789...
   ```

3. **Monitor Status**:
   - Lihat statistics rotasi
   - Monitor API keys dalam cooldown
   - Track usage per provider

## 2. Template dengan Custom HTML + Shortcode

### Template Artikel Blog:

```html
<!-- Template: Artikel SEO-Friendly -->
<article class="seo-article">
    <!-- Header Section -->
    <header class="article-header">
        <h1 class="main-title">{keyword}: Panduan Lengkap 2024</h1>
        <div class="article-meta">
            <span class="publish-date">📅 Dipublish: {date}</span>
            <span class="author">✍️ Oleh: Tim {site_name}</span>
        </div>
    </header>

    <!-- Intro Section -->
    <section class="intro-section">
        <div class="intro-highlight">
            💡 <strong>Yang Akan Anda Pelajari:</strong>
        </div>
        [ai_list type="numbered" prompt="Buat 5 poin yang akan dipelajari pembaca tentang {keyword}" items="5"]
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Pendahuluan -->
        <section class="content-block">
            <h2>Apa itu {keyword}?</h2>
            [ai_content type="paragraph" prompt="Jelaskan definisi dan pentingnya {keyword} untuk pemula" length="250" tone="friendly"]
        </section>

        <!-- FAQ Box -->
        <aside class="faq-box">
            <h3>🤔 Pertanyaan Umum:</h3>
            [ai_content type="paragraph" prompt="Buat 3 FAQ singkat tentang {keyword} dengan jawaban" length="200" tone="informative"]
        </aside>

        <!-- Step by Step -->
        <section class="content-block">
            <h2>Langkah-langkah {keyword}</h2>
            [ai_list type="numbered" prompt="Buat tutorial step-by-step untuk {keyword}" items="7"]
        </section>

        <!-- Visual Content -->
        <section class="visual-section">
            [ai_image prompt="Infografik tentang {keyword}, modern style" size="large"]
            <p class="image-caption">Infografik: Panduan {keyword}</p>
        </section>

        <!-- Tips Section -->
        <section class="tips-section">
            <div class="tips-header">
                <h2>💡 Tips Pro untuk {keyword}</h2>
            </div>
            [ai_content type="paragraph" prompt="Berikan tips ahli dan trik tersembunyi untuk {keyword}" length="300" tone="expert"]
        </section>

        <!-- Common Mistakes -->
        <section class="warning-section">
            <div class="warning-box">
                <h3>⚠️ Kesalahan yang Harus Dihindari</h3>
                [ai_list type="bullet" prompt="Buat daftar kesalahan umum dalam {keyword} dan cara menghindarinya" items="5"]
            </div>
        </section>
    </main>

    <!-- Conclusion -->
    <footer class="article-footer">
        <section class="conclusion">
            <h2>Kesimpulan</h2>
            [ai_content type="paragraph" prompt="Buat kesimpulan yang memotivasi tentang {keyword}" length="150" tone="encouraging"]
        </section>

        <!-- Call to Action -->
        <div class="cta-section">
            <h3>🎯 Langkah Selanjutnya</h3>
            <p>Mulai praktikkan {keyword} hari ini dan lihat hasilnya!</p>
            <button class="cta-button">Mulai Sekarang</button>
        </div>

        <!-- Related Topics -->
        <div class="related-section">
            <h3>📚 Topik Terkait:</h3>
            [ai_list type="bullet" prompt="Buat daftar topik yang berkaitan dengan {keyword}" items="4"]
        </div>
    </footer>
</article>

<!-- Custom CSS untuk styling -->
<style>
.seo-article {
    max-width: 800px;
    margin: 0 auto;
    font-family: 'Arial', sans-serif;
    line-height: 1.6;
}

.article-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
}

.intro-highlight {
    background: #f8f9fa;
    padding: 15px;
    border-left: 4px solid #28a745;
    margin: 20px 0;
    border-radius: 5px;
}

.faq-box {
    background: #e3f2fd;
    padding: 20px;
    border-radius: 8px;
    margin: 25px 0;
    border: 1px solid #2196f3;
}

.tips-section {
    background: #fff3e0;
    padding: 20px;
    border-radius: 8px;
    margin: 25px 0;
    border: 1px solid #ff9800;
}

.warning-box {
    background: #ffebee;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #f44336;
}

.cta-section {
    text-align: center;
    background: #f1f8e9;
    padding: 30px;
    border-radius: 10px;
    margin: 30px 0;
}

.cta-button {
    background: #4caf50;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 25px;
    font-size: 16px;
    cursor: pointer;
    transition: transform 0.3s;
}

.cta-button:hover {
    transform: scale(1.05);
}
</style>
```

### Template Review Produk:

```html
<!-- Template: Review Produk -->
<div class="product-review">
    <!-- Header -->
    <div class="review-header">
        <h1>Review {keyword}: Layak Beli atau Tidak?</h1>
        <div class="rating-preview">
            <span class="stars">⭐⭐⭐⭐⭐</span>
            <span class="rating-text">Rating akan diisi setelah review</span>
        </div>
    </div>

    <!-- Quick Summary -->
    <div class="quick-summary">
        <h2>📋 Ringkasan Singkat</h2>
        [ai_content type="paragraph" prompt="Buat ringkasan singkat dan menarik tentang {keyword}" length="100" tone="engaging"]
    </div>

    <!-- Pros and Cons -->
    <div class="pros-cons-section">
        <div class="pros">
            <h3>✅ Kelebihan {keyword}</h3>
            [ai_list type="bullet" prompt="Buat daftar kelebihan {keyword} berdasarkan review dan fitur" items="5"]
        </div>
        
        <div class="cons">
            <h3>❌ Kekurangan {keyword}</h3>
            [ai_list type="bullet" prompt="Buat daftar kekurangan atau keterbatasan {keyword}" items="3"]
        </div>
    </div>

    <!-- Detailed Review -->
    <div class="detailed-review">
        <h2>🔍 Review Mendalam</h2>
        [ai_content type="paragraph" prompt="Buat review detail tentang {keyword} termasuk fitur, performa, dan pengalaman pengguna" length="400" tone="analytical"]
    </div>

    <!-- Image Gallery -->
    <div class="image-gallery">
        [ai_image prompt="Foto produk {keyword}, high quality, professional" size="medium"]
        [ai_image prompt="Screenshot interface {keyword}" size="medium"]
    </div>

    <!-- Pricing -->
    <div class="pricing-section">
        <h2>💰 Harga dan Value</h2>
        [ai_content type="paragraph" prompt="Analisis harga {keyword} dan apakah worth it untuk dibeli" length="200" tone="honest"]
    </div>

    <!-- Who Should Buy -->
    <div class="recommendation">
        <h2>🎯 Siapa yang Cocok Menggunakan {keyword}?</h2>
        [ai_list type="numbered" prompt="Buat daftar tipe pengguna yang cocok dengan {keyword}" items="4"]
    </div>

    <!-- Alternatives -->
    <div class="alternatives">
        <h2>🔄 Alternatif Lain</h2>
        [ai_list type="bullet" prompt="Buat daftar alternatif {keyword} dengan penjelasan singkat" items="3"]
    </div>

    <!-- Final Verdict -->
    <div class="final-verdict">
        <h2>⚖️ Verdict Akhir</h2>
        [ai_content type="paragraph" prompt="Buat kesimpulan final apakah {keyword} direkomendasikan atau tidak dengan alasan" length="150" tone="conclusive"]
        
        <div class="verdict-box">
            <p><strong>Rekomendasi:</strong> <span class="verdict-text">[YA/TIDAK]</span></p>
            <p><strong>Rating:</strong> <span class="final-rating">X/5 ⭐</span></p>
        </div>
    </div>
</div>
```

## 3. Penggunaan Shortcode Lanjutan

### Shortcode dengan Parameter Custom:

```html
<!-- Artikel dengan tone berbeda per section -->

<!-- Section formal -->
[ai_content type="paragraph" prompt="Jelaskan {keyword} secara profesional" tone="formal" length="200"]

<!-- Section casual -->
[ai_content type="paragraph" prompt="Ceritakan pengalaman menggunakan {keyword}" tone="casual" length="150"]

<!-- Section technical -->
[ai_content type="paragraph" prompt="Jelaskan aspek teknis {keyword}" tone="technical" length="250"]

<!-- Conditional content -->
[ai_conditional condition="beginner"]
    <div class="beginner-note">
        <h3>👶 Untuk Pemula:</h3>
        [ai_content type="paragraph" prompt="Jelaskan {keyword} untuk pemula" tone="simple" length="150"]
    </div>
[/ai_conditional]

<!-- Dynamic list dengan parameter -->
[ai_list type="numbered" prompt="Langkah install {keyword}" items="6" format="detailed"]
```

## 4. Monitoring API Rotator

### Cara Monitoring:

1. **Dashboard API Rotator**: `wp-admin/admin.php?page=kotacom-ai-api-rotator`
2. **Check Statistics**: 
   - Total rotations
   - Rotations dalam 24 jam
   - Keys dalam cooldown
3. **Test All Keys**: Button untuk test semua API keys sekaligus

### Best Practices:

1. **Minimum 3 API Keys per Provider**: Untuk redundancy yang baik
2. **Monitor Cooldown**: Jangan gunakan key yang sedang cooldown
3. **Diversifikasi Provider**: Gunakan multiple provider (Google AI + Groq + OpenAI)
4. **Setup Alerts**: Monitor error rates dan rotation frequency

## 5. Variables Template Tambahan

### Variables yang Bisa Digunakan:

```html
{keyword}     - Keyword utama
{date}        - Tanggal sekarang
{site_name}   - Nama website
{author}      - Nama author
{category}    - Kategori post
{year}        - Tahun sekarang
{month}       - Bulan sekarang
```

### Custom Variables dalam Template:

```html
<!-- Set custom variables -->
<div data-template-vars='{"industry":"teknologi","target":"developer","level":"advanced"}'>
    
    <!-- Use variables in shortcodes -->
    [ai_content prompt="Jelaskan {keyword} untuk {target} level {level} di industri {industry}" length="300"]
    
</div>
```

Plugin Anda sudah sangat lengkap dan powerful. Dengan contoh-contoh di atas, Anda bisa membuat template yang sangat fleksibel dan menarik!