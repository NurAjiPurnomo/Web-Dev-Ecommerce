@extends('admin.layout')

@section('title', 'Kategori Produk')

@section('content')
<div x-data="categoryManagementApp()" class="space-y-6">

    <!-- 1. HEADER SECTION -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/90 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600 border border-blue-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </span>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Kategori Produk</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Kelola kategori produk toko untuk memudahkan pembeli menemukan barang yang dicari.
            </p>
        </div>

        <button 
            type="button" 
            @click="openAddModal()"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-xs transition-all cursor-pointer shrink-0"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            <span>Tambah Kategori Baru</span>
        </button>
    </div>

    <!-- 2. KPI METRICS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500 block">Total Kategori</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalCategoriesCount }}</div>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Kategori di toko online Anda</span>
        </div>
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500 block">Kategori Aktif</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $activeCategoriesCount }}</div>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Tampil di etalase pembeli</span>
        </div>
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-semibold text-slate-500 block">Total Produk Terkategori</span>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $totalCategorizedProducts }}</div>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Produk aktif yang terkelompok</span>
        </div>
    </div>

    <!-- 3. SEARCH BAR -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs">
        <form action="{{ route('admin.categories') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari nama kategori..." 
                    class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-blue-600 focus:outline-none transition-colors"
                >
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button 
                    type="submit" 
                    class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-2xs transition-colors cursor-pointer"
                >
                    Cari Kategori
                </button>
                @if($search)
                    <a 
                        href="{{ route('admin.categories') }}" 
                        class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-colors"
                        title="Reset Pencarian"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. CATEGORIES GRID -->
    @if($categories->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($categories as $category)
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-sm transition-all overflow-hidden flex flex-col justify-between">
                    <div class="p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                                    @if($category->icon)
                                        <img src="{{ $category->icon }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-base font-black text-slate-400">{{ strtoupper(substr($category->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-slate-900 text-sm sm:text-base tracking-tight truncate">
                                        {{ $category->name }}
                                    </h3>
                                    <span class="text-xs text-slate-500 font-medium block mt-0.5">
                                        {{ $category->products_count }} Produk
                                    </span>
                                </div>
                            </div>

                            <!-- Status Badge & Toggle -->
                            <form action="{{ route('admin.categories.toggle', $category->id) }}" method="POST">
                                @csrf
                                <button 
                                    type="submit"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border transition-colors cursor-pointer {{ $category->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' }}"
                                    title="Klik untuk ubah status aktif/nonaktif"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full {{ $category->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    <span>{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="bg-slate-50/80 border-t border-slate-100 px-5 py-3 flex items-center justify-end gap-2">
                        <button 
                            type="button" 
                            @click="openEditModal(@js($category))"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 hover:text-blue-700 bg-white hover:bg-blue-50 border border-slate-200 hover:border-blue-200 transition-colors cursor-pointer shadow-2xs"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Edit</span>
                        </button>

                        <button 
                            type="button" 
                            @click="openDeleteModal(@js($category))"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 hover:text-red-700 bg-white hover:bg-red-50 border border-slate-200 hover:border-red-200 transition-colors cursor-pointer shadow-2xs"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Hapus</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-2">
            {{ $categories->links() }}
        </div>
    @else
        <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center space-y-3 shadow-2xs">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <h3 class="font-bold text-slate-800 text-sm sm:text-base">Belum Ada Kategori</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                {{ $search ? 'Tidak ada kategori yang cocok dengan pencarian Anda. Silakan reset pencarian.' : 'Klik tombol Tambah Kategori Baru di atas untuk membuat kategori produk pertama Anda.' }}
            </p>
        </div>
    @endif

    <!-- ==================== MODAL TAMBAH KATEGORI (SIMPEL & USER FRIENDLY) ==================== -->
    <div 
        x-show="showAddModal" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div 
            @click.outside="showAddModal = false"
            class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-sm w-full overflow-hidden transition-all"
        >
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    <span>Tambah Kategori Baru</span>
                </h3>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        placeholder="Contoh: ATK, Elektronik, Makanan, Kosmetik..." 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:border-blue-600 focus:outline-none"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Foto / Ikon Kategori <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input 
                        type="file" 
                        name="icon" 
                        accept="image/*"
                        @change="previewAddIcon($event)"
                        class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                    >
                    <template x-if="addIconPreview">
                        <div class="mt-2.5 flex items-center gap-2.5 bg-slate-50 p-2 rounded-xl border border-slate-200">
                            <img :src="addIconPreview" class="w-10 h-10 object-cover rounded-lg border border-slate-200">
                            <span class="text-[11px] text-slate-600 font-medium">Foto siap digunakan</span>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button 
                        type="button" 
                        @click="showAddModal = false" 
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer"
                    >
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL EDIT KATEGORI (SIMPEL & USER FRIENDLY) ==================== -->
    <div 
        x-show="showEditModal" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div 
            @click.outside="showEditModal = false"
            class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-sm w-full overflow-hidden transition-all"
        >
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Edit Kategori</span>
                </h3>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'/admin/categories/' + (editCategoryData.id || '') + '/update'" method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="editCategoryData.name"
                        required 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-slate-900 focus:bg-white focus:border-blue-600 focus:outline-none"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ganti Foto / Ikon <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input 
                        type="file" 
                        name="icon" 
                        accept="image/*"
                        @change="previewEditIcon($event)"
                        class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                    >
                    <div class="mt-2.5 flex items-center gap-2.5 bg-slate-50 p-2 rounded-xl border border-slate-200">
                        <img :src="editIconPreview || editCategoryData.icon" class="w-10 h-10 object-cover rounded-lg border border-slate-200">
                        <span class="text-[11px] text-slate-600 font-medium" x-text="editIconPreview ? 'Foto baru dipilih' : 'Foto saat ini'"></span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button 
                        type="button" 
                        @click="showEditModal = false" 
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer"
                    >
                        Perbarui Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL HAPUS KATEGORI ==================== -->
    <div 
        x-show="showDeleteModal" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div 
            @click.outside="showDeleteModal = false"
            class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-sm w-full p-5 text-center space-y-4"
        >
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <div>
                <h3 class="font-bold text-slate-900 text-base">Hapus Kategori?</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Hapus kategori <strong class="text-slate-800" x-text="deleteCategoryData.name"></strong>?
                </p>
                <p class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-2.5 mt-2.5 text-left leading-relaxed">
                    ⚠️ Produk di kategori ini tidak akan terhapus, melainkan otomatis dialihkan ke kategori <strong>"Umum"</strong>.
                </p>
            </div>

            <form :action="'/admin/categories/' + (deleteCategoryData.id || '') + '/delete'" method="POST" class="flex items-center justify-center gap-2.5 pt-2">
                @csrf
                <button 
                    type="button" 
                    @click="showDeleteModal = false" 
                    class="flex-1 px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="flex-1 px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer"
                >
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>

</div>

<script>
function categoryManagementApp() {
    return {
        showAddModal: false,
        showEditModal: false,
        showDeleteModal: false,
        editCategoryData: {},
        deleteCategoryData: {},
        addIconPreview: null,
        editIconPreview: null,

        openAddModal() {
            this.addIconPreview = null;
            this.showAddModal = true;
        },

        previewAddIcon(event) {
            const file = event.target.files[0];
            if (file) {
                this.addIconPreview = URL.createObjectURL(file);
            }
        },

        openEditModal(category) {
            this.editCategoryData = {
                id: category.id,
                name: category.name,
                icon: category.icon,
            };
            this.editIconPreview = null;
            this.showEditModal = true;
        },

        previewEditIcon(event) {
            const file = event.target.files[0];
            if (file) {
                this.editIconPreview = URL.createObjectURL(file);
            }
        },

        openDeleteModal(category) {
            this.deleteCategoryData = {
                id: category.id,
                name: category.name,
            };
            this.showDeleteModal = true;
        }
    }
}
</script>
@endsection
