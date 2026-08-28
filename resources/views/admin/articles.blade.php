@extends('admin.layout')
@section('title', 'Manajemen Berita')
@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Berita (Blog)</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola artikel berita, promo, dan info terbaru toko Anda.</p>
    </div>
    <button onclick="openAddModal()" class="inline-flex items-center justify-center gap-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all focus:ring-4 focus:ring-blue-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Artikel
    </button>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">Thumbnail</th>
                    <th class="px-6 py-4">Judul Artikel</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Tanggal Dibuat</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($articles as $article)
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-6 py-4">
                        @if($article->thumbnail)
                            <img src="{{ asset($article->thumbnail) }}" alt="{{ $article->title }}" class="w-16 h-12 object-cover rounded-lg shadow-sm border border-slate-200">
                        @else
                            <div class="w-16 h-12 bg-slate-100 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-900">{{ $article->title }}</div>
                        <div class="text-xs text-slate-500 mt-0.5 truncate max-w-xs">{{ $article->summary ?: 'Tidak ada ringkasan' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <form action="{{ route('admin.articles.toggle', $article->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border transition-colors {{ $article->status === 'published' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $article->status === 'published' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $article->status === 'published' ? 'Published' : 'Draft' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-500">
                        {{ $article->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="/berita/{{ $article->slug }}" target="_blank" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <button onclick="editArticle({{ $article->toJson() }})" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form action="{{ route('admin.articles.delete', $article->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini secara permanen?')">
                                @csrf
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <p class="font-medium">Belum ada artikel berita yang dibuat.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form Artikel -->
<div id="modalArticle" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <h3 id="modalTitle" class="text-lg font-bold text-slate-900">Tambah Artikel Baru</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-700 bg-white p-1.5 rounded-lg border border-slate-200 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto flex-1">
                <form id="formArticle" action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div id="methodPut"></div>
                    
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Judul Artikel <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" id="articleTitle" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white" placeholder="Contoh: Promo Akhir Tahun Spesial">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Status Publikasi <span class="text-red-500">*</span></label>
                                    <select name="status" id="articleStatus" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white">
                                        <option value="draft">Draft (Sembunyikan sementara)</option>
                                        <option value="published">Published (Tampilkan di web)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Thumbnail Artikel (Opsional)</label>
                                    <input type="file" name="thumbnail" id="articleThumbnail" accept="image/*" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-2 border bg-white">
                                    <p class="text-xs text-slate-500 mt-1">Muncul di halaman daftar berita. Maks 2MB.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Ringkasan (Opsional)</label>
                                    <textarea name="summary" id="articleSummary" rows="3" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white" placeholder="Teks singkat yang muncul di card depan..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Isi Artikel <span class="text-red-500">*</span></label>
                            <textarea name="content" id="articleContent" rows="15" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-white"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 focus:ring-4 focus:ring-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" form="formArticle" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-700 rounded-xl hover:bg-blue-800 focus:ring-4 focus:ring-blue-100 transition-colors">
                    Simpan Artikel
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.7/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: '#articleContent',
        height: 500,
        menubar: false,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ],
        toolbar: 'undo redo | formatselect | ' +
        'bold italic backcolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'image link | removeformat | fullscreen',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 16px; }',
        images_upload_handler: function (blobInfo, success, failure) {
            success('data:' + blobInfo.blob().type + ';base64,' + blobInfo.base64());
        }
    });

    function openAddModal() {
        document.getElementById('modalArticle').classList.remove('hidden');
        document.getElementById('formArticle').reset();
        document.getElementById('formArticle').action = "{{ route('admin.articles.store') }}";
        document.getElementById('methodPut').innerHTML = '';
        document.getElementById('modalTitle').textContent = 'Tambah Artikel Baru';
        if(tinymce.get('articleContent')) tinymce.get('articleContent').setContent('');
    }

    function closeModal() {
        document.getElementById('modalArticle').classList.add('hidden');
        document.getElementById('formArticle').reset();
        if(tinymce.get('articleContent')) tinymce.get('articleContent').setContent('');
    }

    function editArticle(article) {
        document.getElementById('modalArticle').classList.remove('hidden');
        document.getElementById('modalTitle').textContent = 'Edit Artikel: ' + article.title;
        document.getElementById('formArticle').action = "/admin/articles/" + article.id + "/update";
        document.getElementById('methodPut').innerHTML = '';
        document.getElementById('articleThumbnail').value = '';
        
        document.getElementById('articleTitle').value = article.title;
        document.getElementById('articleStatus').value = article.status;
        document.getElementById('articleSummary').value = article.summary || '';
        
        if(tinymce.get('articleContent')) {
            tinymce.get('articleContent').setContent(article.content || '');
        } else {
            document.getElementById('articleContent').value = article.content || '';
        }
    }
</script>
@endsection
