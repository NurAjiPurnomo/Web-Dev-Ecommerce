<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('articles')->truncate();

        $articles = [
            [
                'title' => 'Promo Spesial Kemerdekaan 17 Agustus: Diskon Merdeka Hingga 79%!',
                'slug' => 'promo-spesial-kemerdekaan-17-agustus',
                'thumbnail' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?q=80&w=2070&auto=format&fit=crop',
                'summary' => 'Rayakan Hari Kemerdekaan RI ke-79 dengan promo gila-gilaan dari kami. Nikmati diskon hingga 79% untuk produk pilihan dan gratis ongkir ke seluruh Indonesia.',
                'content' => '<p>Menyambut Hari Kemerdekaan Republik Indonesia yang ke-79, kami menghadirkan <strong>Promo Merdeka</strong> untuk pelanggan setia kami. Jangan lewatkan kesempatan mendapatkan diskon besar-besaran untuk berbagai kategori produk mulai dari fashion, elektronik, hingga kebutuhan rumah tangga.</p><h3>Syarat & Ketentuan Promo:</h3><ul><li>Berlaku dari tanggal 15 hingga 18 Agustus 2026.</li><li>Gunakan kode voucher <strong>MERDEKA79</strong> saat checkout.</li><li>Gratis ongkir tanpa minimum pembelian untuk pengiriman reguler.</li></ul><p>Tunggu apa lagi? Segera masukkan produk impianmu ke keranjang dan checkout sekarang juga sebelum kehabisan stok!</p>',
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Flash Sale Tengah Malam! Diskon 50% Semua Produk Elektronik',
                'slug' => 'flash-sale-tengah-malam-diskon-50-elektronik',
                'thumbnail' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?q=80&w=2070&auto=format&fit=crop',
                'summary' => 'Malam ini saja! Dapatkan gadget impianmu dengan potongan harga setengah harga di Flash Sale Tengah Malam spesial akhir pekan ini.',
                'content' => '<p>Siapkan alarm kalian malam ini pukul 00:00 WIB, karena kami akan mengadakan Flash Sale gila-gilaan untuk semua produk elektronik.</p><h3>Kategori yang Didiskon:</h3><ul><li>Smartphone & Tablet</li><li>Laptop & Aksesoris Komputer</li><li>Kamera Digital</li></ul><p>Stok sangat terbatas dan siapa cepat dia dapat. Pastikan barang sudah masuk keranjang sebelum jam 12 malam agar kamu bisa langsung checkout!</p>',
                'status' => 'published',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'title' => 'Promo Payday: Gajian Tiba, Waktunya Belanja Bebas Ongkir',
                'slug' => 'promo-payday-gajian-tiba-bebas-ongkir',
                'thumbnail' => 'https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?q=80&w=2071&auto=format&fit=crop',
                'summary' => 'Sambut hari gajian dengan riang gembira. Nikmati voucher cashback hingga Rp 100.000 dan bebas ongkos kirim ke seluruh pelosok negeri.',
                'content' => '<p>Hari gajian sudah tiba! Inilah saat yang tepat untuk self-reward setelah sebulan penuh bekerja keras. Untuk merayakannya, kami membagikan voucher cashback eksklusif untuk setiap pembelanjaan minimal Rp 300.000.</p><p>Gunakan kode voucher <strong>PAYDAYCERIA</strong> dan nikmati juga fasilitas bebas ongkir tanpa minimum belanja. Jangan sampai kelewatan promo yang hanya berlangsung selama 3 hari ini!</p>',
                'status' => 'published',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'title' => 'Cuci Gudang Akhir Musim: Pakaian Branded Banting Harga',
                'slug' => 'cuci-gudang-akhir-musim-pakaian-branded',
                'thumbnail' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop',
                'summary' => 'Obral besar-besaran untuk koleksi fashion musim lalu. Dapatkan pakaian branded berkualitas dengan harga super miring, mulai dari Rp 50.000 saja.',
                'content' => '<p>Bagi kalian para pecinta fashion, saatnya merapat! Kami sedang melakukan cuci gudang untuk menyambut koleksi terbaru bulan depan. Ribuan item fashion pria dan wanita didiskon habis-habisan.</p><p>Promo cuci gudang ini berlaku baik untuk pembelian ecer maupun grosir. Kunjungi halaman kategori Promo Cuci Gudang untuk melihat katalog lengkapnya.</p>',
                'status' => 'published',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
            [
                'title' => 'Kejutan Weekend: Beli 1 Gratis 1 Khusus Produk Kecantikan',
                'slug' => 'kejutan-weekend-beli-1-gratis-1-kecantikan',
                'thumbnail' => 'https://images.unsplash.com/photo-1596462502278-27bf85033e54?q=80&w=2071&auto=format&fit=crop',
                'summary' => 'Tampil cantik maksimal di akhir pekan dengan promo Beli 1 Gratis 1 (Buy 1 Get 1) untuk semua produk makeup dan skincare terpilih.',
                'content' => '<p>Tingkatkan rutinitas perawatan kulitmu dengan produk-produk terbaik dari kami. Akhir pekan ini, setiap pembelian satu produk skincare atau makeup dengan label khusus akan mendapatkan satu produk yang sama secara cuma-cuma!</p><p>Persediaan sangat terbatas. Promo berlaku mulai hari Jumat pukul 18:00 hingga Minggu pukul 23:59.</p>',
                'status' => 'published',
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(15),
            ],
        ];

        DB::table('articles')->insert($articles);
    }
}
