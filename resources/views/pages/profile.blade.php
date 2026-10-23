@extends('layouts.app')

@section('content')
<!-- Cropper.js CSS & JS CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
function avatarCropHandler() {
    return {
        avatarPreview: '',
        croppedBase64: '',
        showCropModal: false,
        saving: false,
        cropperInstance: null,

        onFileChange(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.showCropModal = true;
                    this.$nextTick(() => {
                        const image = this.$refs.cropImage;
                        image.src = event.target.result;
                        if (this.cropperInstance) {
                            this.cropperInstance.destroy();
                        }
                        this.cropperInstance = new Cropper(image, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.9,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                        });
                    });
                };
                reader.readAsDataURL(file);
            }
        },

        zoomIn() { if (this.cropperInstance) this.cropperInstance.zoom(0.1); },
        zoomOut() { if (this.cropperInstance) this.cropperInstance.zoom(-0.1); },
        rotateLeft() { if (this.cropperInstance) this.cropperInstance.rotate(-90); },

        closeCropModal() {
            if (this.saving) return;
            this.showCropModal = false;
            if (this.cropperInstance) {
                this.cropperInstance.destroy();
                this.cropperInstance = null;
            }
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        async saveCrop() {
            if (this.cropperInstance && !this.saving) {
                this.saving = true;
                const canvas = this.cropperInstance.getCroppedCanvas({
                    width: 400,
                    height: 400,
                });
                const base64 = canvas.toDataURL('image/jpeg', 0.9);
                this.avatarPreview = base64;
                this.croppedBase64 = base64;

                const hiddenAvatarInput = document.getElementById('mainFormAvatarBase64');
                if (hiddenAvatarInput) hiddenAvatarInput.value = base64;

                // Send AJAX upload request so avatar persists in DB immediately
                try {
                    let form = document.getElementById('mainProfileForm');
                    let formData = form ? new FormData(form) : new FormData();
                    formData.set('_token', '{{ csrf_token() }}');
                    formData.set('avatar_base64', base64);

                    let res = await fetch('{{ route("profile.update") }}', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    let data = await res.json();

                    if (data.success || res.ok) {
                        const newAvatarUrl = data.avatar_url || base64;
                        this.avatarPreview = newAvatarUrl;
                        // Update all user avatar images live on current page
                        document.querySelectorAll('.user-avatar-img').forEach(img => {
                            img.src = newAvatarUrl;
                        });

                        alert('✓ Foto profil berhasil disimpan!');
                    } else {
                        alert('Gagal menyimpan foto profil: ' + (data.message || 'Terjadi kesalahan.'));
                    }
                } catch(err) {
                    console.error("Save avatar error", err);
                    alert('Gagal mengirim foto profil. Silakan coba lagi.');
                } finally {
                    this.saving = false;
                    this.closeCropModal();
                }
            }
        }
    };
}
</script>

<div 
    x-data="{ 
        activeTab: '{{ session('active_tab', '') }}' || (window.location.hash ? window.location.hash.replace('#', '') : '') || (localStorage.getItem('profile_active_tab') || 'biodata'),
        init() {
            const validTabs = ['biodata', 'alamat', 'vouchers', 'keamanan'];
            if (!validTabs.includes(this.activeTab)) {
                this.activeTab = 'biodata';
            }
            if (window.location.hash) {
                const hashTab = window.location.hash.replace('#', '');
                if (validTabs.includes(hashTab)) {
                    this.activeTab = hashTab;
                }
            }
            this.$watch('activeTab', value => {
                if (validTabs.includes(value)) {
                    localStorage.setItem('profile_active_tab', value);
                    if (history.pushState) {
                        history.pushState(null, null, '#' + value);
                    } else {
                        location.hash = '#' + value;
                    }
                }
            });
        }
    }" 
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6"
>

    <!-- Slim Header & Breadcrumb -->
    <div class="mb-4 pb-3 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Akun &amp; Profil</h1>
            <p class="text-xs text-slate-500">Kelola biodata, alamat, voucher belanja, dan keamanan akun</p>
        </div>
        <nav class="text-xs text-slate-400 flex items-center gap-1.5">
            <a href="{{ route('home') }}" class="hover:text-blue-700 transition-colors">Beranda</a>
            <span>/</span>
            <span class="text-slate-700 font-medium">Profil</span>
        </nav>
    </div>

    <!-- Flash Alert Messages -->
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2.5 text-xs sm:text-sm text-emerald-800 font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-3.5 space-y-1">
            @foreach($errors->all() as $error)
                <p class="text-xs sm:text-sm text-red-700 font-medium flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $error }}
                </p>
            @endforeach
        </div>
    @endif

    <!-- Main 2-Column Grid (Sleek 3 : 9 Columns Ratio) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- LEFT SIDEBAR (~25% / 3 cols - Compact & Integrated) -->
        <div class="lg:col-span-3 space-y-3" x-data="avatarCropHandler()">
            
            <!-- Unified Profile & Navigation Card (Like Shopee/Tokopedia) -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-3.5 sm:p-4 shadow-2xs space-y-3">
                
                <!-- Compact User Header -->
                <div class="flex items-center gap-3">
                    <div class="relative w-12 h-12 rounded-full ring-2 ring-blue-50 shadow-2xs shrink-0 group">
                        <template x-if="avatarPreview">
                            <img :src="avatarPreview" alt="Foto Profil" class="user-avatar-img w-12 h-12 rounded-full object-cover border border-white">
                        </template>
                        <template x-if="!avatarPreview">
                            <div>
                                @if($user->avatar)
                                    <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="user-avatar-img w-12 h-12 rounded-full object-cover border border-white">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-blue-700 text-white font-bold text-lg flex items-center justify-center border border-white">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                        </template>

                        <!-- Camera upload mini button -->
                        <button 
                            type="button" 
                            @click="$refs.fileInput.click()" 
                            class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-blue-700 hover:bg-blue-800 text-white flex items-center justify-center shadow-xs border border-white transition-all cursor-pointer"
                            title="Ubah Foto Profil"
                        >
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="min-w-0 flex-1">
                        <h2 class="font-bold text-slate-900 text-sm leading-tight truncate">{{ $user->name }}</h2>
                        <button 
                            type="button" 
                            @click="$refs.fileInput.click()" 
                            class="text-[11px] text-blue-600 hover:text-blue-700 font-medium inline-flex items-center gap-1 cursor-pointer mt-0.5"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            <span>Ubah Foto</span>
                        </button>
                    </div>
                </div>

                <input 
                    type="file" 
                    x-ref="fileInput" 
                    @change="onFileChange($event)" 
                    accept="image/png,image/jpeg,image/webp,image/jpg" 
                    class="hidden"
                >

                <!-- Navigation Tabs Sidebar -->
                <div class="border-t border-slate-100 pt-2 space-y-1">
                    <button 
                        type="button" 
                        @click="activeTab = 'biodata'"
                        :class="activeTab === 'biodata' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/80 shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-transparent font-medium'"
                        class="w-full text-left px-3 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center justify-between cursor-pointer group"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                                 :class="activeTab === 'biodata' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-700'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="truncate">Biodata Diri</span>
                        </div>
                        <svg class="w-3.5 h-3.5 shrink-0 transition-transform group-hover:translate-x-0.5" :class="activeTab === 'biodata' ? 'text-blue-700' : 'text-slate-300 group-hover:text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <button 
                        type="button" 
                        @click="activeTab = 'alamat'"
                        :class="activeTab === 'alamat' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/80 shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-transparent font-medium'"
                        class="w-full text-left px-3 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center justify-between cursor-pointer group"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                                 :class="activeTab === 'alamat' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-700'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <span class="truncate">Alamat Pengiriman</span>
                        </div>
                        <svg class="w-3.5 h-3.5 shrink-0 transition-transform group-hover:translate-x-0.5" :class="activeTab === 'alamat' ? 'text-blue-700' : 'text-slate-300 group-hover:text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <button 
                        type="button" 
                        @click="activeTab = 'vouchers'"
                        :class="activeTab === 'vouchers' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/80 shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-transparent font-medium'"
                        class="w-full text-left px-3 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center justify-between cursor-pointer group"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                                 :class="activeTab === 'vouchers' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-700'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                </svg>
                            </div>
                            <span class="truncate">Voucher Saya</span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            @php
                                $activeVCount = $usableVouchersCount ?? (isset($claimedVouchers) ? $claimedVouchers->filter(fn($v) => !($v->pivot->is_used ?? false) && (!($v->expires_at && $v->expires_at->isPast())) && (($v->status ?? 'aktif') === 'aktif'))->count() : 0);
                            @endphp
                            @if($activeVCount > 0)
                                <span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-blue-200">
                                    {{ $activeVCount }}
                                </span>
                            @endif
                            <svg class="w-3.5 h-3.5 shrink-0 transition-transform group-hover:translate-x-0.5" :class="activeTab === 'vouchers' ? 'text-blue-700' : 'text-slate-300 group-hover:text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </button>

                    <button 
                        type="button" 
                        @click="activeTab = 'keamanan'"
                        :class="activeTab === 'keamanan' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/80 shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-transparent font-medium'"
                        class="w-full text-left px-3 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center justify-between cursor-pointer group"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                                 :class="activeTab === 'keamanan' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200/70 group-hover:text-slate-700'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <span class="truncate">Keamanan Akun</span>
                        </div>
                        <svg class="w-3.5 h-3.5 shrink-0 transition-transform group-hover:translate-x-0.5" :class="activeTab === 'keamanan' ? 'text-blue-700' : 'text-slate-300 group-hover:text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

            </div>

            <!-- MODAL CROP FOTO PROFIL (CROPPER.JS) - CLEAN LIGHT THEME -->
            <div 
                x-show="showCropModal" 
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                style="display: none;"
            >
                <div @click.away="closeCropModal()" class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 text-left space-y-0">
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-white border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2 tracking-tight">
                            <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L5 5m0 0l2.879 2.879M5 5L19 19"/>
                            </svg>
                            <span>Potong &amp; Atur Foto Profil</span>
                        </h3>
                        <button 
                            type="button" 
                            @click="closeCropModal()" 
                            class="text-slate-400 hover:text-slate-600 font-bold p-1 rounded-lg transition-colors cursor-pointer text-xl leading-none"
                        >
                            &times;
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4">
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                            Geser dan atur bingkai kotak untuk memotong foto profil Anda secara simetris (1:1):
                        </p>
                        
                        <div class="max-h-72 overflow-hidden bg-slate-100 border border-slate-200/80 rounded-xl flex items-center justify-center p-3">
                            <img x-ref="cropImage" src="" alt="Crop Image" class="max-w-full max-h-64 block rounded-lg">
                        </div>

                        <!-- Modal Controls & Actions -->
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100">
                            <div class="flex items-center gap-1.5 w-full sm:w-auto justify-center">
                                <button 
                                    type="button" 
                                    @click="zoomIn()" 
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700 transition-colors flex items-center gap-1 cursor-pointer shadow-2xs"
                                    title="Perbesar"
                                >
                                    <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                                    </svg>
                                    <span>Perbesar</span>
                                </button>
                                <button 
                                    type="button" 
                                    @click="zoomOut()" 
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700 transition-colors flex items-center gap-1 cursor-pointer shadow-2xs"
                                    title="Perkecil"
                                >
                                    <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/>
                                    </svg>
                                    <span>Perkecil</span>
                                </button>
                                <button 
                                    type="button" 
                                    @click="rotateLeft()" 
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700 transition-colors flex items-center gap-1 cursor-pointer shadow-2xs"
                                    title="Putar Foto"
                                >
                                    <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span>Putar</span>
                                </button>
                            </div>

                            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                                <button 
                                    type="button" 
                                    @click="closeCropModal()" 
                                    :disabled="saving"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer disabled:opacity-50"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="button" 
                                    @click="saveCrop()" 
                                    :disabled="saving"
                                    class="px-5 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold rounded-xl shadow-2xs transition-colors flex items-center gap-2 cursor-pointer disabled:opacity-50"
                                >
                                    <svg x-show="saving" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Gunakan &amp; Simpan Foto</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT CONTENT AREA (~75% / 9 cols - Spatially Roomy) -->
        <div class="lg:col-span-9">

            <form id="mainProfileForm" action="{{ route('profile.update') }}" method="POST" x-show="activeTab === 'biodata' || activeTab === 'alamat'">
                @csrf

                <!-- Hidden Input for Active Tab & Avatar Base64 Image -->
                <input type="hidden" name="active_tab" :value="activeTab" hidden>
                <input type="hidden" name="avatar_base64" id="mainFormAvatarBase64" hidden>

                <!-- TAB 1: BIODATA DIRI -->
                <div x-show="activeTab === 'biodata'" class="bg-white border border-slate-200/90 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Biodata Diri</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola informasi profil akun Anda untuk verifikasi &amp; kemudahan transaksi</p>
                    </div>

                    <div class="space-y-4">

                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    name="name" 
                                    value="{{ old('name', $user->name) }}" 
                                    required 
                                    placeholder="Masukkan nama lengkap Anda..."
                                    class="w-full bg-slate-50/70 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 rounded-xl pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-medium text-slate-900 transition-all placeholder:text-slate-400"
                                >
                            </div>
                        </div>

                        <!-- Email & No HP / WhatsApp -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Alamat Email <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        type="email" 
                                        name="email" 
                                        value="{{ old('email', $user->email) }}" 
                                        required 
                                        placeholder="email@domain.com"
                                        class="w-full bg-slate-50/70 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 rounded-xl pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-medium text-slate-900 transition-all placeholder:text-slate-400"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Nomor WhatsApp / HP <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        name="phone" 
                                        value="{{ old('phone', $user->phone) }}" 
                                        required 
                                        placeholder="081234567890"
                                        class="w-full bg-slate-50/70 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 rounded-xl pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-medium text-slate-900 transition-all placeholder:text-slate-400"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Jenis Kelamin & Tanggal Lahir -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jenis Kelamin</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    </div>
                                    <select 
                                        name="gender" 
                                        class="w-full bg-slate-50/70 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 rounded-xl pl-9 pr-9 py-2.5 text-xs sm:text-sm font-medium text-slate-900 transition-all cursor-pointer appearance-none"
                                    >
                                        <option value="">-- Pilih Jenis Kelamin --</option>
                                        <option value="Laki-laki" {{ old('gender', $user->gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="Perempuan" {{ old('gender', $user->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Lahir</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        type="date" 
                                        name="birth_date" 
                                        value="{{ old('birth_date', $user->birth_date) }}" 
                                        class="w-full bg-slate-50/70 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 rounded-xl pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-medium text-slate-900 transition-all"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Informasi & Privasi Akun Note -->
                        <div class="rounded-xl p-3 bg-slate-50 border border-slate-200/80 flex items-start gap-2.5 shadow-2xs mt-1">
                            <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div class="space-y-0.5 text-xs text-slate-600">
                                <h4 class="font-bold text-slate-900 text-xs">Keamanan &amp; Kerahasiaan Data Pribadi</h4>
                                <p class="leading-relaxed text-slate-500 text-[11px]">
                                    Alamat email dan nomor WhatsApp Anda digunakan untuk notifikasi status pesanan, resi pengiriman real-time, dan verifikasi akun. Data Anda terlindungi dengan standar enkripsi terpercaya.
                                </p>
                            </div>
                        </div>

                    </div>

                    <div class="pt-2 flex items-center justify-between flex-wrap gap-2.5">
                        <button 
                            type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm px-5 py-2.5 rounded-xl transition-all shadow-xs active:scale-[0.99] flex items-center gap-2 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Simpan Perubahan Biodata</span>
                        </button>
                        <span class="text-[11px] text-slate-400">Terakhir diperbarui secara berkala</span>
                    </div>
                </div>

                <!-- TAB 2: ALAMAT TUJUAN PENGIRIMAN UTAMA -->
                <div x-show="activeTab === 'alamat'" class="bg-white border border-slate-200/90 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Alamat Pengiriman Utama</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola informasi alamat tujuan lengkap untuk kemudahan checkout otomatis</p>
                    </div>

                    <div 
                        class="space-y-4" 
                        x-data="{ 
                            activeLabel: '{{ old('address_label', $user->address_label ?: 'Rumah') }}',
                            recipientName: '{{ old('recipient_name', $user->recipient_name ?: $user->name) }}',
                            recipientPhone: '{{ old('recipient_phone', $user->recipient_phone ?: $user->phone) }}',
                            addressDetail: `{{ old('address', $user->address && $user->address !== 'denpasar' ? $user->address : '') }}`,
                            searchQuery: '{{ old('city', $user->city) ? (($user->district ? $user->district . ', ' : '') . $user->city . ', ' . ($user->province ?? '') . ($user->postal_code ? '. ' . $user->postal_code : '')) : '' }}',
                            selectedArea: {
                                id: '{{ old('biteship_area_id', $user->biteship_area_id ?: $user->city_id) }}',
                                district: '{{ old('district', $user->district) }}',
                                village: '{{ old('village', $user->village) }}',
                                city: '{{ old('city', $user->city) }}',
                                province: '{{ old('province', $user->province) }}',
                                postal_code: '{{ old('postal_code', $user->postal_code) }}'
                            },
                            suggestions: [],
                            showDropdown: false,
                            loading: false,
                            showMapModal: false,
                            mapInstance: null,
                            markerInstance: null,
                            mapSearchQuery: '',
                            mapSearchResults: [],
                            isSearchingMap: false,
                            isGeocoding: false,
                            isLocatingGPS: false,
                            gpsAccuracyNote: '',
                            detectedBiteshipArea: null,
                            detectedAddress: {
                                display_name: '',
                                road: '',
                                village: '',
                                district: '',
                                city: '',
                                province: '',
                                postal_code: '',
                                lat: null,
                                lng: null,
                                formatted_summary: ''
                            },

                            async searchArea() {
                                if (this.searchQuery.length < 3) {
                                    this.suggestions = [];
                                    return;
                                }
                                this.loading = true;
                                try {
                                    const [biteshipRes, osmRes] = await Promise.all([
                                        fetch('/shipping/biteship/areas?query=' + encodeURIComponent(this.searchQuery)).then(r => r.json()).catch(() => ({ status: 'error' })),
                                        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(this.searchQuery) + '&countrycodes=id&limit=5').then(r => r.json()).catch(() => ([]))
                                    ]);

                                    let list = [];
                                    if (biteshipRes.status === 'success' && biteshipRes.data) {
                                        biteshipRes.data.forEach(item => {
                                            list.push({
                                                type: 'biteship',
                                                id: item.id,
                                                name: item.name,
                                                subtext: (item.administrative_division_level_3_name || '') + ', ' + (item.administrative_division_level_2_name || '') + ', ' + (item.administrative_division_level_1_name || '') + ' (' + (item.postal_code || '') + ')',
                                                district: item.administrative_division_level_3_name || '',
                                                village: item.name.split(',')[0] || '',
                                                city: item.administrative_division_level_2_name || '',
                                                province: item.administrative_division_level_1_name || '',
                                                postal_code: item.postal_code || '',
                                                badge: 'Biteship Area',
                                                badgeClass: 'bg-blue-100 text-blue-800'
                                            });
                                        });
                                    }

                                    if (Array.isArray(osmRes)) {
                                        osmRes.forEach(item => {
                                            list.push({
                                                type: 'osm',
                                                id: 'osm_' + item.place_id,
                                                name: item.display_name,
                                                subtext: 'Hasil Pencarian Peta (Kecamatan/Jalan/Gedung)',
                                                lat: item.lat,
                                                lon: item.lon,
                                                badge: 'Titik Peta',
                                                badgeClass: 'bg-emerald-100 text-emerald-800'
                                            });
                                        });
                                    }

                                    this.suggestions = list;
                                    this.showDropdown = list.length > 0;
                                } catch (e) {
                                    console.error('Error searching area:', e);
                                } finally {
                                    this.loading = false;
                                }
                            },

                            selectArea(item) {
                                if (item.type === 'osm') {
                                    this.addressDetail = item.name;
                                    const fullAddressTextarea = document.querySelector('textarea[name=address]');
                                    if (fullAddressTextarea) {
                                        fullAddressTextarea.value = item.name;
                                    }
                                    this.reverseGeocode(item.lat, item.lon);
                                    this.searchQuery = item.name;
                                    this.showDropdown = false;
                                    return;
                                }

                                this.selectedArea.id = item.id;
                                this.selectedArea.district = item.district || '';
                                this.selectedArea.village = item.village || '';
                                this.selectedArea.city = item.city || '';
                                this.selectedArea.province = item.province || '';
                                this.selectedArea.postal_code = item.postal_code || '';
                                
                                this.searchQuery = item.name;
                                this.showDropdown = false;
                                
                                const postalInput = document.querySelector('input[name=postal_code]');
                                if (postalInput && item.postal_code) {
                                    postalInput.value = item.postal_code;
                                }
                            },

                            openMapPicker() {
                                this.showMapModal = true;
                                this.mapSearchResults = [];
                                this.$nextTick(() => {
                                    if (!this.mapInstance) {
                                        this.initMap();
                                    } else {
                                        setTimeout(() => { 
                                            this.mapInstance.invalidateSize(); 
                                        }, 250);
                                    }
                                });
                            },

                            initMap() {
                                // Default coordinate Jawa Tengah / Tegal
                                let defaultLat = -6.8694;
                                let defaultLng = 109.1402;

                                this.mapInstance = L.map('leafletMapContainer', {
                                    zoomControl: true
                                }).setView([defaultLat, defaultLng], 14);

                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '© OpenStreetMap contributors',
                                    maxZoom: 19
                                }).addTo(this.mapInstance);

                                this.markerInstance = L.marker([defaultLat, defaultLng], { 
                                    draggable: true 
                                }).addTo(this.mapInstance);

                                this.markerInstance.on('dragstart', () => {
                                    this.isGeocoding = true;
                                });

                                this.markerInstance.on('dragend', (event) => {
                                    const position = event.target.getLatLng();
                                    this.reverseGeocode(position.lat, position.lng);
                                });

                                this.mapInstance.on('click', (e) => {
                                    this.markerInstance.setLatLng(e.latlng);
                                    this.reverseGeocode(e.latlng.lat, e.latlng.lng);
                                });

                                // Jika user sudah punya kota/kecamatan, cari dan arahkan peta ke lokasi tersebut
                                if (this.selectedArea.district || this.selectedArea.city) {
                                    const initialQuery = (this.selectedArea.district ? this.selectedArea.district + ' ' : '') + (this.selectedArea.city || '');
                                    this.searchLocationInMap(initialQuery, true);
                                } else {
                                    this.detectGPSForMap();
                                }
                            },

                            detectGPSForMap() {
                                if (!navigator.geolocation) {
                                    this.gpsAccuracyNote = 'Browser Anda tidak mendukung deteksi lokasi otomatis.';
                                    return;
                                }
                                this.isLocatingGPS = true;
                                this.gpsAccuracyNote = 'Mendeteksi titik koordinat GPS akurat...';

                                navigator.geolocation.getCurrentPosition(
                                    (pos) => {
                                        this.isLocatingGPS = false;
                                        const lat = pos.coords.latitude;
                                        const lng = pos.coords.longitude;
                                        const acc = pos.coords.accuracy || 0;

                                        if (acc > 3000) {
                                            this.gpsAccuracyNote = 'Titik terdeteksi via jaringan internet (toleransi ±' + Math.round(acc/1000) + ' km). Gunakan kotak cari alamat jika lokasi kurang presisi.';
                                        } else {
                                            this.gpsAccuracyNote = 'GPS akurat berhasil terdeteksi (akurasi ±' + Math.round(acc) + ' meter)';
                                        }

                                        if (this.mapInstance && this.markerInstance) {
                                            this.mapInstance.flyTo([lat, lng], 17);
                                            this.markerInstance.setLatLng([lat, lng]);
                                            this.reverseGeocode(lat, lng);
                                        }
                                    },
                                    (err) => {
                                        this.isLocatingGPS = false;
                                        let msg = 'Tidak dapat mengakses lokasi perangkat.';
                                        if (err.code === 1) msg = 'Izin lokasi belum diberikan. Silakan cari nama jalan di kotak pencarian.';
                                        else if (err.code === 2) msg = 'Sinyal lokasi tidak tersedia.';
                                        else if (err.code === 3) msg = 'Waktu pencarian lokasi GPS habis.';
                                        this.gpsAccuracyNote = msg;
                                    },
                                    {
                                        enableHighAccuracy: true,
                                        timeout: 10000,
                                        maximumAge: 0
                                    }
                                );
                            },

                            async searchLocationInMap(queryOverride = null, isInitial = false) {
                                const q = queryOverride || this.mapSearchQuery;
                                if (!q || q.trim().length < 3) return;

                                this.isSearchingMap = true;
                                try {
                                    const res = await fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q) + '&countrycodes=id&limit=5&addressdetails=1');
                                    const list = await res.json();
                                    if (Array.isArray(list) && list.length > 0) {
                                        if (isInitial) {
                                            const item = list[0];
                                            const lat = parseFloat(item.lat);
                                            const lon = parseFloat(item.lon);
                                            if (this.mapInstance && this.markerInstance) {
                                                this.mapInstance.setView([lat, lon], 16);
                                                this.markerInstance.setLatLng([lat, lon]);
                                                this.reverseGeocode(lat, lon);
                                            }
                                        } else {
                                            this.mapSearchResults = list;
                                        }
                                    } else {
                                        this.mapSearchResults = [];
                                    }
                                } catch (e) {
                                    console.error('Error search in map:', e);
                                } finally {
                                    this.isSearchingMap = false;
                                }
                            },

                            selectMapSearchResult(item) {
                                const lat = parseFloat(item.lat);
                                const lon = parseFloat(item.lon);
                                this.mapSearchResults = [];
                                this.mapSearchQuery = item.display_name.split(',')[0];
                                if (this.mapInstance && this.markerInstance) {
                                    this.mapInstance.flyTo([lat, lon], 17);
                                    this.markerInstance.setLatLng([lat, lon]);
                                    this.reverseGeocode(lat, lon);
                                }
                            },

                            async reverseGeocode(lat, lng) {
                                this.isGeocoding = true;
                                try {
                                    // Tier 1: Detail mikro (Jalan, Bangunan, Desa)
                                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`);
                                    const data = await res.json();
                                    if (data && data.address) {
                                        const addr = data.address;
                                        let road = addr.road || addr.pedestrian || addr.street || addr.residential || addr.building || addr.amenity || addr.neighbourhood || addr.suburb || addr.hamlet || '';
                                        let village = addr.village || addr.suburb || addr.neighbourhood || addr.hamlet || '';
                                        let district = addr.subdistrict || addr.city_district || addr.district || addr.municipality || '';
                                        let city = addr.city || addr.town || addr.county || '';
                                        let province = addr.state || addr.province || addr.region || '';
                                        let postcode = addr.postcode || '';

                                        // Tier 2: Jika Kecamatan atau Kode Pos belum terdeteksi dari node desa mikro,
                                        // lakukan resolusi tingkat wilayah kecamatan (Zoom 12)
                                        if (!district || !postcode) {
                                            try {
                                                const resZ12 = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=12&addressdetails=1`);
                                                const dataZ12 = await resZ12.json();
                                                if (dataZ12 && dataZ12.address) {
                                                    const zAddr = dataZ12.address;
                                                    if (!district) {
                                                        district = zAddr.town || zAddr.subdistrict || zAddr.district || zAddr.city_district || zAddr.municipality || '';
                                                    }
                                                    if (!postcode && zAddr.postcode) {
                                                        postcode = zAddr.postcode;
                                                    }
                                                }
                                            } catch (errZ12) {
                                                console.warn('Resolusi zoom12 kecamatan error:', errZ12);
                                            }
                                        }

                                        // Fallback cerdas untuk nama jalan di pedesaan/pemukiman tanpa plang jalan OSM
                                        if (!road) {
                                            road = village ? ('Area Lingkungan Desa ' + village) : 'Jalan Lingkungan / Pemukiman';
                                        }

                                        this.detectedAddress = {
                                            display_name: data.display_name || '',
                                            road: road,
                                            village: village,
                                            district: district,
                                            city: city,
                                            province: province,
                                            postal_code: postcode,
                                            lat: Number(lat).toFixed(6),
                                            lng: Number(lng).toFixed(6),
                                            formatted_summary: [road, village, district, city, province].filter(Boolean).join(', ')
                                        };

                                        // Pre-match Biteship area secara realtime
                                        const bQuery = (district ? district + ' ' : '') + city;
                                        if (bQuery.trim().length >= 3) {
                                            fetch('/shipping/biteship/areas?query=' + encodeURIComponent(bQuery))
                                                .then(r => r.json())
                                                .then(bRes => {
                                                    if (bRes.status === 'success' && bRes.data && bRes.data.length > 0) {
                                                        this.detectedBiteshipArea = bRes.data[0];
                                                        if (!this.detectedAddress.district && this.detectedBiteshipArea.administrative_division_level_3_name) {
                                                            this.detectedAddress.district = this.detectedBiteshipArea.administrative_division_level_3_name;
                                                        }
                                                        if (!this.detectedAddress.postal_code && this.detectedBiteshipArea.postal_code) {
                                                            this.detectedAddress.postal_code = this.detectedBiteshipArea.postal_code;
                                                        }
                                                    }
                                                }).catch(() => {});
                                        }
                                    }
                                } catch (e) {
                                    console.error('Reverse geocode error:', e);
                                } finally {
                                    this.isGeocoding = false;
                                }
                            },

                            applyMapLocation() {
                                if (!this.detectedAddress.lat) {
                                    this.showMapModal = false;
                                    return;
                                }

                                // 1. Susun nama jalan / patokan detail untuk textarea alamat
                                let detailText = '';
                                if (this.detectedAddress.road) {
                                    detailText = this.detectedAddress.road;
                                    if (this.detectedAddress.village && this.detectedAddress.village !== this.detectedAddress.road) {
                                        detailText += ', ' + this.detectedAddress.village;
                                    }
                                } else if (this.detectedAddress.display_name) {
                                    detailText = this.detectedAddress.display_name;
                                }

                                if (detailText) {
                                    if (!this.addressDetail || this.addressDetail.trim() === '') {
                                        this.addressDetail = detailText;
                                    } else {
                                        if (!this.addressDetail.toLowerCase().includes(this.detectedAddress.road.toLowerCase())) {
                                            this.addressDetail = detailText + ' - ' + this.addressDetail;
                                        }
                                    }
                                    const fullAddressTextarea = document.querySelector('textarea[name=address]');
                                    if (fullAddressTextarea) fullAddressTextarea.value = this.addressDetail;
                                }

                                // 2. Isi detail wilayah
                                if (this.detectedAddress.district) this.selectedArea.district = this.detectedAddress.district;
                                if (this.detectedAddress.village) this.selectedArea.village = this.detectedAddress.village;
                                if (this.detectedAddress.city) this.selectedArea.city = this.detectedAddress.city;
                                if (this.detectedAddress.province) this.selectedArea.province = this.detectedAddress.province;
                                if (this.detectedAddress.postal_code) {
                                    this.selectedArea.postal_code = this.detectedAddress.postal_code;
                                    const postalInput = document.querySelector('input[name=postal_code]');
                                    if (postalInput) postalInput.value = this.detectedAddress.postal_code;
                                }

                                // 3. Tentukan Biteship area & format searchQuery
                                if (this.detectedBiteshipArea) {
                                    this.selectedArea.id = this.detectedBiteshipArea.id;
                                    this.searchQuery = this.detectedBiteshipArea.name;
                                } else {
                                    const parts = [
                                        this.detectedAddress.district ? 'Kec. ' + this.detectedAddress.district : '',
                                        this.detectedAddress.city,
                                        this.detectedAddress.province
                                    ].filter(Boolean);
                                    this.searchQuery = parts.join(', ') + (this.detectedAddress.postal_code ? ' (' + this.detectedAddress.postal_code + ')' : '');
                                }

                                this.showMapModal = false;
                            }
                        }"
                    >

                        <!-- 1. Input Nama Penerima & Nomor HP Penerima -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Nama Penerima Paket <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        name="recipient_name" 
                                        x-model="recipientName"
                                        required 
                                        placeholder="Contoh: Budi Santoso"
                                        class="w-full bg-slate-50/70 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 rounded-xl pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-medium text-slate-900 transition-all placeholder:text-slate-400"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Nomor HP Penerima Paket <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        name="recipient_phone" 
                                        x-model="recipientPhone"
                                        required 
                                        placeholder="081234567890"
                                        class="w-full bg-slate-50/70 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 rounded-xl pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-medium text-slate-900 transition-all placeholder:text-slate-400"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- 2. Label Alamat (Rumah, Kantor, Apartemen, Lainnya) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Label Alamat Pengiriman <span class="text-red-500">*</span>
                            </label>
                            <input type="hidden" name="address_label" :value="activeLabel">
                            <div class="flex flex-wrap items-center gap-2">
                                <button 
                                    type="button" 
                                    @click="activeLabel = 'Rumah'"
                                    :class="activeLabel === 'Rumah' 
                                        ? 'bg-blue-600 text-white shadow-2xs border-blue-600' 
                                        : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900'"
                                    class="px-3.5 py-1.5 text-xs font-semibold rounded-lg border transition-all cursor-pointer flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5 shrink-0 transition-colors" :class="activeLabel === 'Rumah' ? 'text-white' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    <span>Rumah</span>
                                </button>
                                <button 
                                    type="button" 
                                    @click="activeLabel = 'Kantor'"
                                    :class="activeLabel === 'Kantor' 
                                        ? 'bg-blue-600 text-white shadow-2xs border-blue-600' 
                                        : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900'"
                                    class="px-3.5 py-1.5 text-xs font-semibold rounded-lg border transition-all cursor-pointer flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5 shrink-0 transition-colors" :class="activeLabel === 'Kantor' ? 'text-white' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span>Kantor</span>
                                </button>
                                <button 
                                    type="button" 
                                    @click="activeLabel = 'Apartemen'"
                                    :class="activeLabel === 'Apartemen' 
                                        ? 'bg-blue-600 text-white shadow-2xs border-blue-600' 
                                        : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900'"
                                    class="px-3.5 py-1.5 text-xs font-semibold rounded-lg border transition-all cursor-pointer flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5 shrink-0 transition-colors" :class="activeLabel === 'Apartemen' ? 'text-white' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v.01M12 14v.01M16 14v.01M8 10v.01M12 10v.01M16 10v.01M8 6v.01M12 6v.01M16 6v.01M12 18v.01M8 18v.01M16 18v.01M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16"/>
                                    </svg>
                                    <span>Apartemen</span>
                                </button>
                                <button 
                                    type="button" 
                                    @click="activeLabel = 'Lainnya'"
                                    :class="activeLabel === 'Lainnya' 
                                        ? 'bg-blue-600 text-white shadow-2xs border-blue-600' 
                                        : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900'"
                                    class="px-3.5 py-1.5 text-xs font-semibold rounded-lg border transition-all cursor-pointer flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5 shrink-0 transition-colors" :class="activeLabel === 'Lainnya' ? 'text-white' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    <span>Lainnya</span>
                                </button>
                            </div>
                        </div>

                        <!-- 3. Alamat Lengkap & Patokan Rumah/Gedung -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Alamat Lengkap &amp; Patokan Rumah / Gedung <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="user_address_field"
                                name="address" 
                                x-model="addressDetail"
                                rows="3" 
                                required
                                placeholder="Contoh: Jl. Ahmad Yani No. 12, RT 03/RW 05, Blok C2 (Patokan: Pagar warna hitam di samping Mesjid Al-Ikhlas)"
                                class="w-full bg-slate-50/70 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 rounded-xl p-3 text-xs sm:text-sm font-medium text-slate-900 transition-all placeholder:text-slate-400 leading-relaxed"
                            ></textarea>

                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-[11px]">Tuliskan detail <strong>Nomor Rumah, RT/RW, Nama Jalan, serta Patokan Lokasi/Gedung</strong> spesifik.</span>
                            </p>
                        </div>

                        <!-- 4. Wilayah & Kode Pos -->
                        <div class="space-y-3.5">
                            <!-- Leaflet CSS and Script Include -->
                            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-start">
                                <!-- Search Box for Area (8 cols) -->
                                <div class="md:col-span-8 relative">
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                        Kecamatan, Kota/Kabupaten &amp; Provinsi <span class="text-red-500">*</span>
                                    </label>
                                    
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                        </div>
                                        <input 
                                            type="text" 
                                            x-model="searchQuery" 
                                            @input.debounce.300ms="searchArea()" 
                                            @focus="if(suggestions.length > 0) showDropdown = true" 
                                            placeholder="Ketik nama Kecamatan, Kelurahan, Kota, atau Kode Pos..." 
                                            class="w-full bg-slate-50/70 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 rounded-xl pl-9 pr-9 py-2.5 text-xs sm:text-sm font-medium text-slate-900 transition-all placeholder:text-slate-400 leading-normal"
                                        >
                                        <div x-show="loading" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <div class="w-3.5 h-3.5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                                        </div>
                                    </div>

                                    <!-- Hidden Inputs Submitted to Server -->
                                    <input type="hidden" name="biteship_area_id" :value="selectedArea.id">
                                    <input type="hidden" name="city_id" :value="selectedArea.id">
                                    <input type="hidden" name="district" :value="selectedArea.district">
                                    <input type="hidden" name="village" :value="selectedArea.village">
                                    <input type="hidden" name="city" :value="selectedArea.city">
                                    <input type="hidden" name="province" :value="selectedArea.province">

                                    <!-- Live Autocomplete Suggestions Dropdown -->
                                    <div 
                                        x-show="showDropdown && suggestions.length > 0" 
                                        @click.away="showDropdown = false" 
                                        class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-56 overflow-y-auto divide-y divide-slate-100 text-xs"
                                    >
                                        <template x-for="item in suggestions" :key="item.id">
                                            <button 
                                                type="button" 
                                                @click="selectArea(item)" 
                                                class="w-full text-left p-2.5 hover:bg-blue-50 transition-colors flex items-center justify-between cursor-pointer gap-2"
                                            >
                                                <div class="min-w-0 flex-1">
                                                    <div class="font-bold text-slate-900 leading-snug truncate" x-text="item.name"></div>
                                                    <div class="text-[11px] text-slate-500 truncate" x-text="item.subtext"></div>
                                                </div>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0" :class="item.badgeClass || 'bg-blue-100 text-blue-800'" x-text="item.badge"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Kode Pos (4 cols) -->
                                <div class="md:col-span-4">
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                        Kode Pos <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                            </svg>
                                        </div>
                                        <input 
                                            type="text" 
                                            name="postal_code" 
                                            x-model="selectedArea.postal_code"
                                            placeholder="56196"
                                            class="w-full bg-slate-50/70 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 rounded-xl pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-medium text-slate-900 transition-all placeholder:text-slate-400 leading-normal"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Pinpoint Titik Alamat di Peta (Tokopedia/Shopee Style) -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 p-3 bg-slate-50/80 hover:bg-slate-100/80 border border-slate-200 rounded-xl transition-all">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-semibold text-slate-900">Pinpoint Titik Alamat di Peta</div>
                                        <div class="text-[11px] text-slate-500">Tandai posisi rumah/kantor di peta agar kurir pengantar tidak tersasar</div>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    @click="openMapPicker()" 
                                    class="inline-flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-white hover:bg-blue-50 hover:border-blue-300 border border-slate-200 rounded-lg transition-all cursor-pointer shadow-2xs shrink-0 self-start sm:self-auto"
                                >
                                    <span>Pilih Titik di Peta</span>
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- MODAL PETA INTERAKTIF PINPOINT REAL-TIME (LEAFLET MAP PICKER) -->
                            <div 
                                x-show="showMapModal" 
                                x-transition
                                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs"
                                style="display: none;"
                            >
                                <div @click.away="showMapModal = false" class="bg-white rounded-2xl max-w-2xl w-full overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
                                    <!-- Modal Header -->
                                    <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-sm">Pinpoint Lokasi Pengiriman Real-Time</h3>
                                                <p class="text-[11px] text-slate-300">Cari nama jalan atau geser pin tepat di titik rumah/kantor Anda</p>
                                            </div>
                                        </div>
                                        <button type="button" @click="showMapModal = false" class="text-slate-400 hover:text-white font-bold text-lg p-1">&times;</button>
                                    </div>

                                    <div class="p-4 space-y-3">
                                        <!-- 1. Search Box di Dalam Peta -->
                                        <div class="relative">
                                            <div class="flex items-center gap-2">
                                                <div class="relative flex-1">
                                                    <input 
                                                        type="text" 
                                                        x-model="mapSearchQuery" 
                                                        @keydown.enter.prevent="searchLocationInMap()"
                                                        placeholder="Ketik nama jalan, perumahan, kelurahan, atau kecamatan Anda..."
                                                        class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-9 pr-4 py-2 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors"
                                                    >
                                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                    </svg>
                                                </div>
                                                <button 
                                                    type="button" 
                                                    @click="searchLocationInMap()"
                                                    class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition-colors cursor-pointer shrink-0 shadow-xs"
                                                >
                                                    <span x-show="!isSearchingMap">Cari</span>
                                                    <span x-show="isSearchingMap" class="animate-pulse">Mencari...</span>
                                                </button>
                                                <button 
                                                    type="button" 
                                                    @click="detectGPSForMap()" 
                                                    class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 rounded-xl text-xs font-semibold transition-colors cursor-pointer shrink-0 flex items-center gap-1 shadow-xs"
                                                    title="Deteksi Ulang Lokasi GPS Akurat"
                                                >
                                                    <svg class="w-3.5 h-3.5" :class="isLocatingGPS ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <span>GPS Akurat</span>
                                                </button>
                                            </div>

                                            <!-- Suggestions Dropdown di Peta -->
                                            <div 
                                                x-show="mapSearchResults.length > 0" 
                                                class="absolute z-30 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-48 overflow-y-auto divide-y divide-slate-100 text-xs"
                                            >
                                                <template x-for="item in mapSearchResults" :key="item.place_id">
                                                    <button 
                                                        type="button" 
                                                        @click="selectMapSearchResult(item)" 
                                                        class="w-full text-left p-2.5 hover:bg-blue-50 transition-colors flex items-center gap-2 cursor-pointer"
                                                    >
                                                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                                        <span class="truncate font-medium text-slate-800" x-text="item.display_name"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- 2. Notifikasi Akurasi GPS (Jika dari IP jaringan) -->
                                        <template x-if="gpsAccuracyNote">
                                            <div class="px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-lg text-[11px] text-amber-800 flex items-center justify-between gap-2">
                                                <span x-text="gpsAccuracyNote"></span>
                                                <button type="button" @click="gpsAccuracyNote = ''" class="text-amber-600 font-bold hover:text-amber-900">&times;</button>
                                            </div>
                                        </template>

                                        <!-- 3. Leaflet Map Container -->
                                        <div id="leafletMapContainer" class="w-full h-64 sm:h-72 rounded-xl border border-slate-300 z-10 shadow-inner"></div>

                                        <!-- 4. Live Detailed Address Card (Real-Time Card) -->
                                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-1.5 text-xs font-bold text-slate-900">
                                                    <span class="w-2 h-2 rounded-full" :class="isGeocoding ? 'bg-amber-500 animate-ping' : 'bg-emerald-500'"></span>
                                                    <span x-text="isGeocoding ? 'Mendeteksi Alamat Secara Real-Time...' : 'Detail Alamat Terdeteksi:'"></span>
                                                </div>
                                                <span class="text-[10px] font-mono text-slate-500" x-show="detectedAddress.lat" x-text="'Lat: ' + detectedAddress.lat + ', Lng: ' + detectedAddress.lng"></span>
                                            </div>

                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px]">
                                                <div class="bg-white p-2 rounded-lg border border-slate-200 focus-within:border-blue-600 transition-colors">
                                                    <div class="flex items-center justify-between mb-0.5">
                                                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Jalan / Tempat:</span>
                                                        <span class="text-[9px] text-blue-600 font-medium">Bisa diedit</span>
                                                    </div>
                                                    <input 
                                                        type="text" 
                                                        x-model="detectedAddress.road" 
                                                        placeholder="Nama jalan / RT RW..."
                                                        class="w-full text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded px-1.5 py-1 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-600 transition-colors"
                                                    >
                                                </div>
                                                <div class="bg-white p-2 rounded-lg border border-slate-200 focus-within:border-blue-600 transition-colors">
                                                    <div class="flex items-center justify-between mb-0.5">
                                                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Kelurahan / Desa:</span>
                                                    </div>
                                                    <input 
                                                        type="text" 
                                                        x-model="detectedAddress.village" 
                                                        placeholder="Nama desa..."
                                                        class="w-full text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded px-1.5 py-1 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-600 transition-colors"
                                                    >
                                                </div>
                                                <div class="bg-white p-2 rounded-lg border border-slate-200 focus-within:border-blue-600 transition-colors">
                                                    <div class="flex items-center justify-between mb-0.5">
                                                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Kecamatan:</span>
                                                        <span class="text-[9px] text-blue-600 font-medium">Bisa diedit</span>
                                                    </div>
                                                    <input 
                                                        type="text" 
                                                        x-model="detectedAddress.district" 
                                                        placeholder="Nama kecamatan..."
                                                        class="w-full text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded px-1.5 py-1 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-600 transition-colors"
                                                    >
                                                </div>
                                                <div class="bg-white p-2 rounded-lg border border-slate-200 focus-within:border-blue-600 transition-colors">
                                                    <div class="flex items-center justify-between mb-0.5">
                                                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Kota / Kab:</span>
                                                    </div>
                                                    <input 
                                                        type="text" 
                                                        x-model="detectedAddress.city" 
                                                        placeholder="Nama kota..."
                                                        class="w-full text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded px-1.5 py-1 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-600 transition-colors"
                                                    >
                                                </div>
                                            </div>

                                            <div class="text-[11px] text-slate-600 bg-white p-2 rounded-lg border border-slate-200 leading-snug">
                                                <span class="text-slate-400 font-semibold block text-[10px] uppercase">Alamat Lengkap Sistem:</span>
                                                <span x-text="detectedAddress.display_name || 'Geser atau klik pin pada titik rumah/kantor Anda di peta untuk mendeteksi alamat.'"></span>
                                            </div>
                                        </div>

                                        <!-- 5. Modal Footer Action Buttons -->
                                        <div class="flex items-center justify-between pt-1">
                                            <span class="text-[11px] text-slate-400 hidden sm:inline">Geser pin untuk menyesuaikan titik presisi.</span>
                                            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                                                <button 
                                                    type="button" 
                                                    @click="showMapModal = false" 
                                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors cursor-pointer"
                                                >
                                                    Batal
                                                </button>
                                                <button 
                                                    type="button" 
                                                    @click="applyMapLocation()" 
                                                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors cursor-pointer shadow-xs flex items-center gap-1.5"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Gunakan Lokasi Ini</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- 5. Alihkan "Jadikan Alamat Utama/Default" Toggle Switch -->
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        name="is_primary_address" 
                                        value="1" 
                                        {{ old('is_primary_address', $user->is_primary_address ?? true) ? 'checked' : '' }}
                                        class="sr-only peer"
                                    >
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block">Jadikan Alamat Utama / Default</span>
                                    <span class="text-[11px] text-slate-500">Alamat ini akan otomatis terpilih secara bawaan saat checkout.</span>
                                </div>
                            </div>
                        </div>

                        <!-- LIVE FULL ADDRESS PREVIEW BOX (Clean & Compact) -->
                        <div class="p-3.5 bg-slate-50 border border-slate-200/90 rounded-xl space-y-2.5 mt-2 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                                    <span class="text-[11px] font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        <span>Pratinjau Label Pengiriman</span>
                                    </span>
                                </div>
                                <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-md" x-text="activeLabel"></span>
                            </div>
                            
                            <div class="bg-white border border-slate-200/80 rounded-lg p-3 space-y-1.5 text-xs text-slate-800 shadow-2xs">
                                <div class="flex items-center justify-between font-bold border-b border-slate-100 pb-2 flex-wrap gap-2">
                                    <span class="text-slate-900 flex items-center gap-1.5 text-xs sm:text-sm">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span x-text="recipientName || 'Nama Penerima Paket'"></span>
                                    </span>
                                    <span class="text-blue-700 font-mono text-[11px] font-semibold bg-blue-50 px-2 py-0.5 rounded border border-blue-100" x-text="recipientPhone || 'No. HP Penerima'"></span>
                                </div>
                                <div class="text-slate-700 leading-relaxed pt-0.5 space-y-1">
                                    <span class="font-medium block text-slate-900 text-xs" x-text="addressDetail || 'Detail Alamat Lengkap & Patokan Rumah/Gedung...'"></span>
                                    <span class="text-slate-500 text-[11px] flex items-center gap-1">
                                        <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span x-text="searchQuery || 'Wilayah Kecamatan, Kota/Kabupaten, Provinsi & Kode Pos'"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="pt-2 flex items-center justify-between flex-wrap gap-2.5">
                        <button 
                            type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm px-5 py-2.5 rounded-xl transition-all shadow-xs active:scale-[0.99] flex items-center gap-2 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Simpan Alamat Utama Pengiriman</span>
                        </button>
                        <span class="text-[11px] text-slate-400">Otomatis sinkron saat checkout pesanan</span>
                    </div>
                </div>
            </form>

            <!-- TAB 3: VOUCHER SAYA -->
            <div x-show="activeTab === 'vouchers'" class="bg-white border border-slate-200/90 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-4" x-data="{ vFilter: 'semua', copiedCode: '' }">
                
                @php
                    $countSemua = isset($claimedVouchers) ? $claimedVouchers->count() : 0;
                    $countSiap = isset($claimedVouchers) ? $claimedVouchers->filter(fn($v) => !($v->pivot->is_used ?? false) && (!$v->expires_at || !$v->expires_at->isPast()) && ($v->status ?? 'aktif') === 'aktif')->count() : 0;
                    $countTerpakai = isset($claimedVouchers) ? $claimedVouchers->filter(fn($v) => (bool)($v->pivot->is_used ?? false))->count() : 0;
                    $countExpired = isset($claimedVouchers) ? $claimedVouchers->filter(fn($v) => !($v->pivot->is_used ?? false) && (($v->expires_at && $v->expires_at->isPast()) || ($v->status ?? 'aktif') !== 'aktif'))->count() : 0;
                @endphp

                <!-- Slim Header & Inline Filter Sub-Tabs -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Voucher Saya</h2>
                        <p class="text-xs text-slate-500">Voucher diskon dan promo belanja yang siap digunakan</p>
                    </div>

                    <!-- Filter Sub-Tabs (Semua / Siap Dipakai / Terpakai / Expired) -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 text-xs shrink-0">
                        <button 
                            type="button" 
                            @click="vFilter = 'semua'" 
                            :class="vFilter === 'semua' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium'" 
                            class="px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer whitespace-nowrap flex items-center gap-1.5 text-xs"
                        >
                            <span>Semua</span>
                            <span :class="vFilter === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'" class="text-[10px] px-1.5 py-0.2 rounded-full font-bold">{{ $countSemua }}</span>
                        </button>
                        <button 
                            type="button" 
                            @click="vFilter = 'siap'" 
                            :class="vFilter === 'siap' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium'" 
                            class="px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer whitespace-nowrap flex items-center gap-1.5 text-xs"
                        >
                            <span>Siap Dipakai</span>
                            <span :class="vFilter === 'siap' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'" class="text-[10px] px-1.5 py-0.2 rounded-full font-bold">{{ $countSiap }}</span>
                        </button>
                        <button 
                            type="button" 
                            @click="vFilter = 'terpakai'" 
                            :class="vFilter === 'terpakai' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium'" 
                            class="px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer whitespace-nowrap flex items-center gap-1.5 text-xs"
                        >
                            <span>Terpakai</span>
                            <span :class="vFilter === 'terpakai' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'" class="text-[10px] px-1.5 py-0.2 rounded-full font-bold">{{ $countTerpakai }}</span>
                        </button>
                        <button 
                            type="button" 
                            @click="vFilter = 'expired'" 
                            :class="vFilter === 'expired' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium'" 
                            class="px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer whitespace-nowrap flex items-center gap-1.5 text-xs"
                        >
                            <span>Kedaluwarsa</span>
                            <span :class="vFilter === 'expired' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'" class="text-[10px] px-1.5 py-0.2 rounded-full font-bold">{{ $countExpired }}</span>
                        </button>
                    </div>
                </div>

                <!-- Voucher Cards List (Compact & Space-Efficient) -->
                @if(isset($claimedVouchers) && $claimedVouchers->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($claimedVouchers as $v)
                            @php
                                \Carbon\Carbon::setLocale('id');
                                $isUsed = (bool) ($v->pivot->is_used ?? false);
                                $isExpired = $v->expires_at && $v->expires_at->isPast();
                                $isInactive = ($v->status ?? 'aktif') !== 'aktif';

                                $claimedDate = $v->pivot->created_at ? \Carbon\Carbon::parse($v->pivot->created_at) : ($v->created_at ? \Carbon\Carbon::parse($v->created_at) : null);
                                $expiryDate = $v->expires_at ? \Carbon\Carbon::parse($v->expires_at) : null;
                                $usedDate = $v->pivot->used_at ? \Carbon\Carbon::parse($v->pivot->used_at) : null;
                                
                                if ($isUsed) {
                                    $vStatusKey = 'terpakai';
                                    $iconBoxClass = 'bg-slate-100 text-slate-400';
                                    $tagClass = 'bg-slate-100 text-slate-600 border border-slate-200';
                                    $tagText = $v->type === 'gratis_ongkir' ? 'Gratis Ongkir' : ($v->type === 'cashback' ? 'Cashback' : 'Diskon Toko');
                                } elseif ($isExpired || $isInactive) {
                                    $vStatusKey = 'expired';
                                    $iconBoxClass = 'bg-rose-50 text-rose-400';
                                    $tagClass = 'bg-slate-100 text-slate-600 border border-slate-200';
                                    $tagText = $v->type === 'gratis_ongkir' ? 'Gratis Ongkir' : ($v->type === 'cashback' ? 'Cashback' : 'Diskon Toko');
                                } else {
                                    $vStatusKey = 'siap';
                                    if ($v->type === 'gratis_ongkir') {
                                        $iconBoxClass = 'bg-emerald-50 text-emerald-600';
                                        $tagClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                        $tagText = 'Gratis Ongkir';
                                    } elseif ($v->type === 'cashback') {
                                        $iconBoxClass = 'bg-amber-50 text-amber-600';
                                        $tagClass = 'bg-amber-50 text-amber-700 border border-amber-200';
                                        $tagText = 'Cashback Koin';
                                    } elseif (($v->target ?? 'semua') === 'pelanggan') {
                                        $iconBoxClass = 'bg-indigo-50 text-indigo-600';
                                        $tagClass = 'bg-indigo-50 text-indigo-700 border border-indigo-200';
                                        $tagText = 'Eksklusif VIP';
                                    } else {
                                        $iconBoxClass = 'bg-blue-50 text-blue-600';
                                        $tagClass = 'bg-blue-50 text-blue-700 border border-blue-200';
                                        $tagText = 'Diskon Produk';
                                    }
                                }

                                $diffDays = $expiryDate ? (int) now()->diffInDays($expiryDate, false) : null;
                            @endphp

                            <div 
                                x-show="vFilter === 'semua' || vFilter === '{{ $vStatusKey }}'"
                                x-data="{ showTerms: false }"
                                class="bg-white border rounded-xl p-3 sm:p-3.5 transition-all flex flex-col justify-between {{ $vStatusKey === 'siap' ? 'border-slate-200 hover:border-blue-400 hover:shadow-xs' : 'border-slate-200/70 bg-slate-50/40 opacity-70' }}"
                            >
                                <div class="flex items-start gap-3">
                                    <!-- Compact Icon (42x42) -->
                                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-lg shrink-0 flex items-center justify-center {{ $iconBoxClass }} border border-black/5">
                                        @if($v->type === 'gratis_ongkir')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                        @elseif($v->type === 'cashback')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @elseif(($v->target ?? 'semua') === 'pelanggan')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        @endif
                                    </div>

                                    <!-- Main Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold text-slate-900 text-sm sm:text-base leading-tight">{{ $v->formatted_discount }}</span>
                                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded {{ $tagClass }}">{{ $tagText }}</span>
                                        </div>

                                        <p class="text-xs text-slate-500 mt-0.5 truncate">
                                            Min. blj {{ $v->formatted_min_spend }}
                                            @if($v->max_discount && $v->type === 'diskon_persen')
                                                • Maks. Rp {{ number_format($v->max_discount, 0, ',', '.') }}
                                            @endif
                                        </p>

                                        <!-- Date & S&K row -->
                                        <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-1.5 flex-wrap">
                                            @if($vStatusKey === 'siap')
                                                <span class="flex items-center gap-1 text-slate-600">
                                                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>s.d. {{ $expiryDate ? $expiryDate->format('d/m/Y') : '-' }}</span>
                                                </span>
                                                @if($diffDays !== null && $diffDays >= 0 && $diffDays <= 7)
                                                    <span class="text-[10px] text-amber-800 bg-amber-50 border border-amber-200 px-1 py-0.2 rounded font-medium">
                                                        {{ $diffDays > 0 ? "{$diffDays} hr lagi" : "Hari ini!" }}
                                                    </span>
                                                @endif
                                            @elseif($vStatusKey === 'terpakai')
                                                <span class="text-slate-500 flex items-center gap-1">
                                                    <span class="text-emerald-600 font-bold">✓</span> Dipakai {{ $usedDate ? $usedDate->format('d/m/Y') : '-' }}
                                                </span>
                                            @else
                                                <span class="text-rose-500">Kedaluwarsa</span>
                                            @endif

                                            <span>•</span>
                                            <button type="button" @click="showTerms = !showTerms" class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer font-medium">
                                                <span x-text="showTerms ? 'Tutup S&K' : 'S&K'"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Right Column: Action & Code -->
                                    <div class="shrink-0 flex flex-col items-end justify-between gap-2 self-stretch">
                                        @if($vStatusKey === 'siap')
                                            <a 
                                                href="{{ route('catalog') }}" 
                                                class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors shadow-2xs"
                                            >
                                                Pakai
                                            </a>
                                        @elseif($vStatusKey === 'terpakai')
                                            <span class="text-[11px] font-medium text-slate-400 bg-slate-100 px-2 py-1 rounded-md select-none">
                                                Terpakai
                                            </span>
                                        @else
                                            <span class="text-[11px] font-medium text-rose-400 bg-rose-50 px-2 py-1 rounded-md select-none">
                                                Hangus
                                            </span>
                                        @endif

                                        <!-- Compact Code Pill -->
                                        <button 
                                            type="button" 
                                            @click="navigator.clipboard.writeText('{{ $v->code }}'); copiedCode = '{{ $v->code }}'; setTimeout(() => copiedCode = '', 2000)" 
                                            class="text-[10px] font-mono text-slate-500 hover:text-slate-800 bg-slate-50 hover:bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded cursor-pointer transition-colors"
                                            title="Salin Kode"
                                        >
                                            <span x-text="copiedCode === '{{ $v->code }}' ? '✓ Disalin' : '{{ $v->code }}'"></span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Expandable S&K -->
                                <div x-show="showTerms" x-transition class="mt-2.5 pt-2 border-t border-slate-100 text-[11px] text-slate-500 space-y-0.5">
                                    <p>• Minimal belanja <strong>{{ $v->formatted_min_spend }}</strong></p>
                                    @if($v->type === 'gratis_ongkir')
                                        <p>• Berlaku khusus untuk potongan ongkos kirim</p>
                                    @elseif($v->type === 'diskon_persen')
                                        <p>• Diskon {{ $v->discount_value }}% (maks. {{ $v->max_discount ? 'Rp ' . number_format($v->max_discount, 0, ',', '.') : 'tanpa batas' }})</p>
                                    @else
                                        <p>• Potongan senilai {{ $v->formatted_discount }}</p>
                                    @endif
                                    <p>• Berlaku s.d. {{ $expiryDate ? $expiryDate->translatedFormat('d M Y') : 'Seterusnya' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Empty Voucher State -->
                    <div class="text-center py-12 px-4 bg-slate-50/80 rounded-3xl border border-dashed border-slate-300 space-y-4">
                        <div class="w-16 h-16 bg-blue-100 text-blue-700 rounded-3xl flex items-center justify-center text-3xl mx-auto shadow-2xs">
                            🎟️
                        </div>
                        <div class="space-y-1">
                            <h3 class="font-extrabold text-slate-900 text-base">Belum Ada Voucher Terklaim</h3>
                            <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                                Anda belum mengklaim voucher diskon atau gratis ongkir. Kunjungi halaman promo dan klaim voucher spesial hari ini!
                            </p>
                        </div>
                        <div class="pt-1">
                            <a href="{{ route('promo') }}" class="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-2xl transition-all shadow-sm shadow-blue-700/25 cursor-pointer">
                                <span>Lihat Promo &amp; Klaim Voucher</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- TAB 4: KEAMANAN & KATA SANDI -->
            <div x-show="activeTab === 'keamanan'" class="bg-white border border-slate-200/90 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-4" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Keamanan &amp; Kata Sandi</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Perbarui kata sandi akun Anda secara berkala untuk menjaga keamanan data dan transaksi</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                    
                    <!-- Left Column: Form Fields (7 cols) -->
                    <div class="lg:col-span-7">
                        <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="active_tab" value="keamanan">

                            <!-- Kata Sandi Saat Ini -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Kata Sandi Saat Ini <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        :type="showCurrent ? 'text' : 'password'" 
                                        name="current_password" 
                                        required 
                                        placeholder="Masukkan kata sandi lama Anda..."
                                        class="w-full bg-slate-50/70 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 rounded-xl pl-9 pr-10 py-2.5 text-xs sm:text-sm font-medium text-slate-900 transition-all placeholder:text-slate-400"
                                    >
                                    <button 
                                        type="button" 
                                        @click="showCurrent = !showCurrent" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                                        tabindex="-1"
                                    >
                                        <svg x-show="!showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Kata Sandi Baru -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Kata Sandi Baru (Min. 6 Karakter) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        :type="showNew ? 'text' : 'password'" 
                                        name="password" 
                                        required 
                                        placeholder="Ketik kata sandi baru yang kuat..."
                                        class="w-full bg-slate-50/70 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 rounded-xl pl-9 pr-10 py-2.5 text-xs sm:text-sm font-medium text-slate-900 transition-all placeholder:text-slate-400"
                                    >
                                    <button 
                                        type="button" 
                                        @click="showNew = !showNew" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                                        tabindex="-1"
                                    >
                                        <svg x-show="!showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Konfirmasi Kata Sandi Baru -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        :type="showConfirm ? 'text' : 'password'" 
                                        name="password_confirmation" 
                                        required 
                                        placeholder="Ketik ulang kata sandi baru..."
                                        class="w-full bg-slate-50/70 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 rounded-xl pl-9 pr-10 py-2.5 text-xs sm:text-sm font-medium text-slate-900 transition-all placeholder:text-slate-400"
                                    >
                                    <button 
                                        type="button" 
                                        @click="showConfirm = !showConfirm" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                                        tabindex="-1"
                                    >
                                        <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="pt-1">
                                <button 
                                    type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm px-5 py-2.5 rounded-xl transition-all shadow-xs active:scale-[0.99] flex items-center gap-2 cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Perbarui Kata Sandi</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Right Column: Tips Keamanan Akun (5 cols) -->
                    <div class="lg:col-span-5 space-y-3">
                        <div class="rounded-xl p-3.5 bg-slate-50 border border-slate-200/90 space-y-3 shadow-2xs">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-slate-900 text-xs sm:text-sm">Panduan Kata Sandi Aman</h3>
                            </div>

                            <ul class="space-y-2 text-xs text-slate-600">
                                <li class="flex items-start gap-1.5">
                                    <span class="text-blue-600 font-bold shrink-0">✓</span>
                                    <span>Minimal <strong>6 karakter</strong> (disarankan 8+ karakter).</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="text-blue-600 font-bold shrink-0">✓</span>
                                    <span>Kombinasikan huruf kapital, kecil, dan angka.</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="text-blue-600 font-bold shrink-0">✓</span>
                                    <span>Hindari tanggal lahir, nama pribadi, atau no HP.</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <span class="text-blue-600 font-bold shrink-0">✓</span>
                                    <span>Ganti kata sandi berkala setiap beberapa bulan.</span>
                                </li>
                            </ul>

                            <div class="pt-2.5 border-t border-slate-200/80 text-[11px] text-slate-500 leading-snug flex items-start gap-1.5">
                                <span class="text-amber-500 text-xs shrink-0">⚠️</span>
                                <span>Toko Online tidak pernah meminta kata sandi atau kode OTP Anda.</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>

</div>
@endsection
