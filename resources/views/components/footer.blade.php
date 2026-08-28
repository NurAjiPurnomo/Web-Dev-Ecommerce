<footer class="bg-blue-50 border-t border-slate-200 text-slate-700 pt-12 pb-8 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 mb-10">
            
            <!-- Column 1: Brand (Spans 2 columns on large screens) -->
            <div class="space-y-4 lg:col-span-2 pr-0 lg:pr-8">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-700 text-white flex items-center justify-center font-bold text-base shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-slate-900 tracking-tight">
                        Toko<span class="text-blue-700">Online</span>
                    </span>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Toko online resmi yang menyediakan produk fashion, gadget, aksesoris, dan peralatan rumah tangga berkualitas premium dengan harga terbaik. Belanja aman, mudah, dan terpercaya.
                </p>
                <div class="text-xs text-slate-500 mt-4 space-y-1">
                    <p><strong>Email:</strong> support@tokoonline.com</p>
                    <p><strong>Telepon:</strong> (021) 1234-5678</p>
                    <p><strong>Alamat:</strong> Jl. Jend. Sudirman No.Kav 21, RT.10/RW.1, Kuningan, Karet, Kecamatan Setiabudi, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12920</p>
                </div>
            </div>

            <!-- Column 2: MENU UTAMA -->
            <div class="lg:col-span-1">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 block">
                    Menu Utama
                </h3>
                <ul class="space-y-3 text-xs">
                    <li><a href="{{ route('catalog') }}" class="text-slate-600 hover:text-blue-700 transition-colors">Katalog Produk</a></li>
                    <li><a href="{{ route('promo') }}" class="text-slate-600 hover:text-blue-700 transition-colors">Promo Spesial</a></li>
                    <li><a href="{{ route('articles.index') }}" class="text-slate-600 hover:text-blue-700 transition-colors">Berita & Informasi</a></li>
                    <li><a href="{{ route('orders') }}" class="text-slate-600 hover:text-blue-700 transition-colors">Lacak Pesanan</a></li>
                </ul>
            </div>

            <!-- Column 3: LAYANAN & KEBIJAKAN -->
            <div class="lg:col-span-1">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 block">
                    Bantuan & Legal
                </h3>
                <ul class="space-y-3 text-xs">
                    <li><a href="#" class="text-slate-600 hover:text-blue-700 transition-colors">Hubungi CS (WhatsApp)</a></li>
                    @if(isset($footerPages))
                        @foreach($footerPages as $fp)
                            <li>
                                <a href="{{ route('page.show', $fp->slug) }}" class="text-slate-600 hover:text-blue-700 transition-colors">{{ $fp->title }}</a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>

            <!-- Column 4: PEMBAYARAN, LOGISTIK & IKUTI KAMI -->
            <div class="lg:col-span-1">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3 block">
                    Ikuti Kami
                </h3>
                <div class="flex gap-2 mb-6">
                    <a href="#" class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-blue-50 hover:text-blue-700 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                    </a>
                </div>
                
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3 block">
                    Pembayaran & Logistik
                </h3>
                <div class="grid grid-cols-4 gap-2 mb-2">
                    <div class="bg-white border border-slate-200 rounded-lg p-1.5 flex items-center justify-center shadow-2xs"><img src="{{ asset('assets/Bca.png') }}" alt="BCA" class="h-3 object-contain"></div>
                    <div class="bg-white border border-slate-200 rounded-lg p-1.5 flex items-center justify-center shadow-2xs"><img src="{{ asset('assets/Visa.png') }}" alt="VISA" class="h-2.5 object-contain"></div>
                    <div class="bg-white border border-slate-200 rounded-lg p-1.5 flex items-center justify-center shadow-2xs"><img src="{{ asset('assets/Mandiri.png') }}" alt="MANDIRI" class="h-3 object-contain"></div>
                    <div class="bg-white border border-slate-200 rounded-lg p-1.5 flex items-center justify-center shadow-2xs"><img src="{{ asset('assets/Qris.png') }}" alt="QRIS" class="h-3 object-contain"></div>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <div class="bg-white border border-slate-200 rounded-lg p-1 flex items-center justify-center shadow-2xs"><img src="{{ asset('assets/Jne.png') }}" alt="JNE" class="h-3.5 object-contain"></div>
                    <div class="bg-white border border-slate-200 rounded-lg p-1 flex items-center justify-center shadow-2xs"><img src="{{ asset('assets/J&T.png') }}" alt="J&T" class="h-3.5 object-contain"></div>
                    <div class="bg-white border border-slate-200 rounded-lg p-1 flex items-center justify-center shadow-2xs"><img src="{{ asset('assets/Sicepat.png') }}" alt="SiCepat" class="h-3.5 object-contain"></div>
                    <div class="bg-white border border-slate-200 rounded-lg p-1 flex items-center justify-center shadow-2xs"><img src="{{ asset('assets/Gosend.png') }}" alt="GoSend" class="h-3.5 object-contain"></div>
                </div>
            </div>

        </div>

        <div class="border-t border-slate-200 pt-6 mt-4 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} TokoOnline. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="#" class="hover:text-blue-700 transition-colors">Kebijakan Privasi</a>
                <a href="#" class="hover:text-blue-700 transition-colors">Syarat & Ketentuan</a>
            </div>
        </div>
    </div>
</footer>
