<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Tentang Kami',
                'slug' => 'tentang-kami',
                'content' => '<h2>Sejarah Toko Kami</h2><p>Toko Online resmi kami telah berdiri sejak 2020. Kami berdedikasi memberikan produk terbaik dengan harga terjangkau.</p>',
                'show_in_navbar' => true,
                'footer_column' => 'Perusahaan',
                'status' => 'aktif',
            ],
            [
                'title' => 'Pusat Bantuan',
                'slug' => 'bantuan',
                'content' => '<h2>Pusat Bantuan</h2><p>Jika Anda mengalami masalah, silakan hubungi layanan pelanggan kami 24/7 di nomor telepon atau email yang tertera.</p>',
                'show_in_navbar' => false,
                'footer_column' => 'Bantuan',
                'status' => 'aktif',
            ],
            [
                'title' => 'Kebijakan Privasi',
                'slug' => 'kebijakan-privasi',
                'content' => '<h2>Kebijakan Privasi</h2><p>Kami sangat menghargai privasi pengguna kami. Semua data dilindungi dengan enkripsi standar industri.</p>',
                'show_in_navbar' => false,
                'footer_column' => 'Bantuan',
                'status' => 'aktif',
            ],
            [
                'title' => 'Syarat & Ketentuan',
                'slug' => 'syarat-ketentuan',
                'content' => '<h2>Syarat & Ketentuan</h2><p>Berikut adalah syarat dan ketentuan berbelanja di Toko Online kami. Pastikan Anda membaca dengan seksama sebelum melakukan transaksi.</p>',
                'show_in_navbar' => false,
                'footer_column' => 'Perusahaan',
                'status' => 'aktif',
            ],
        ];

        foreach ($pages as $page) {
            \App\Models\Page::firstOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
