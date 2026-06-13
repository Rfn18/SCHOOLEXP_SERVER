<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>API Tester - SCHOOLEXP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .doc-small {
            width: 150px;
            height: 150px;
        }

        .doc-medium {
            width: 250px;
            height: 250px;
        }

        .doc-large {
            width: 400px;
            height: 400px;
        }

        .doc-card img {
            object-fit: cover;
            width: 100%;
            height: 100%;
        }

        .doc-card {
            transition: transform 0.2s;
        }

        .doc-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .preview-img {
            max-height: 200px;
            object-fit: cover;
        }

        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }

        .loading-spinner {
            display: none;
        }

        .loading-spinner.active {
            display: inline-block;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-gear-fill"></i> API Tester Dashboard</h4>
                        <div>
                            <input type="text" id="apiBaseUrl" class="form-control form-control-sm d-inline-block"
                                style="width: 250px;" value="/api" placeholder="Base URL API">
                            <input type="text" id="authToken"
                                class="form-control form-control-sm d-inline-block ms-2" style="width: 300px;"
                                placeholder="JWT Token (optional)">
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-pills mb-4" id="apiTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="events-tab" data-bs-toggle="pill"
                                    data-bs-target="#events" type="button">
                                    <i class="bi bi-calendar-event"></i> Events
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="galleries-tab" data-bs-toggle="pill"
                                    data-bs-target="#galleries" type="button">
                                    <i class="bi bi-images"></i> Doc Galleries
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="docs-tab" data-bs-toggle="pill" data-bs-target="#docs"
                                    type="button">
                                    <i class="bi bi-camera"></i> Documentations
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="apiTabsContent">

                            <!-- ============== EVENTS TAB ============== -->
                            <div class="tab-pane fade show active" id="events" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="card border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <i class="bi bi-plus-circle"></i> Create Event
                                            </div>
                                            <div class="card-body">
                                                <form id="eventForm">
                                                    <div class="mb-2">
                                                        <label class="form-label">Title *</label>
                                                        <input type="text" name="title" class="form-control"
                                                            required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Slug *</label>
                                                        <input type="text" name="slug" class="form-control"
                                                            required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Description *</label>
                                                        <textarea name="description" class="form-control" rows="2" required></textarea>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Location *</label>
                                                        <input type="text" name="location" class="form-control"
                                                            required>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-6">
                                                            <label class="form-label">Start Date *</label>
                                                            <input type="date" name="start_date" class="form-control"
                                                                required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label">End Date *</label>
                                                            <input type="date" name="end_date" class="form-control"
                                                                required>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-6">
                                                            <label class="form-label">Start Time *</label>
                                                            <input type="time" name="start_time"
                                                                class="form-control" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label">End Time *</label>
                                                            <input type="time" name="end_time"
                                                                class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Event Category ID *</label>
                                                        <input type="number" name="event_category_id"
                                                            class="form-control" value="1" required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">User ID *</label>
                                                        <input type="number" name="user_id" class="form-control"
                                                            value="99" required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Status</label>
                                                        <select name="status" class="form-select">
                                                            <option value="upcoming">Upcoming</option>
                                                            <option value="ongoing">Ongoing</option>
                                                            <option value="completed">Completed</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Cover Image</label>
                                                        <input type="file" name="cover_image" class="form-control"
                                                            accept="image/*">
                                                        <img id="eventPreview" class="preview-img mt-2 d-none rounded"
                                                            alt="preview">
                                                    </div>
                                                    <button type="submit" class="btn btn-primary w-100">
                                                        <i class="bi bi-send"></i> Create Event
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="card">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <span><i class="bi bi-list"></i> List Events</span>
                                                <button class="btn btn-sm btn-outline-primary" onclick="loadEvents()">
                                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                                </button>
                                            </div>
                                            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                                <div id="eventsList" class="row g-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ============== GALLERIES TAB ============== -->
                            <div class="tab-pane fade" id="galleries" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="card border-success">
                                            <div class="card-header bg-success text-white">
                                                <i class="bi bi-plus-circle"></i> Create Doc Gallery
                                            </div>
                                            <div class="card-body">
                                                <form id="galleryForm">
                                                    <div class="mb-3">
                                                        <label class="form-label">Select Event *</label>
                                                        <select name="event_id" id="galleryEventSelect"
                                                            class="form-select" required>
                                                            <option value="">-- Pilih Event --</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Select Doc Category *</label>
                                                        <select name="doc_category_id" id="galleryDocCategorySelect"
                                                            class="form-select" required>
                                                            <option value="">-- Pilih Doc Category --</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Soft Order</label>
                                                        <input type="number" name="soft_order" class="form-control">
                                                    </div>
                                                    <button type="submit" class="btn btn-success w-100">
                                                        <i class="bi bi-folder-plus"></i> Create Gallery
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="card">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <span><i class="bi bi-images"></i> List Galleries</span>
                                                <button class="btn btn-sm btn-outline-success"
                                                    onclick="loadGalleries()">
                                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                                </button>
                                            </div>
                                            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                                <div class="mb-3">
                                                    <label class="form-label">Filter by Event:</label>
                                                    <select id="filterEventId" class="form-select form-select-sm"
                                                        onchange="loadGalleries()">
                                                        <option value="">-- Semua Event --</option>
                                                    </select>
                                                </div>
                                                <div id="galleriesList" class="list-group"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ============== DOCUMENTATIONS TAB ============== -->
                            <div class="tab-pane fade" id="docs" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card border-warning">
                                            <div class="card-header bg-warning">
                                                <i class="bi bi-cloud-upload"></i> Upload Documentation
                                            </div>
                                            <div class="card-body">
                                                <form id="docForm">
                                                    <div class="mb-3">
                                                        <label class="form-label">Select Gallery *</label>
                                                        <select name="gallery_id" id="docGallerySelect"
                                                            class="form-select" required>
                                                            <option value="">-- Pilih Gallery --</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Image *</label>
                                                        <input type="file" name="image" id="docImage"
                                                            class="form-control" accept="image/*" required>
                                                        <img id="docPreview" class="preview-img mt-2 d-none rounded"
                                                            alt="preview">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Alt Text</label>
                                                        <input type="text" name="alt_text" class="form-control">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Type (Size) *</label>
                                                        <select name="type" class="form-select" required>
                                                            <option value="small">Small (150x150)</option>
                                                            <option value="medium" selected>Medium (250x250)</option>
                                                            <option value="large">Large (400x400)</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Soft Order</label>
                                                        <input type="number" name="soft_order" class="form-control"
                                                            value="0">
                                                    </div>
                                                    <button type="submit" class="btn btn-warning w-100">
                                                        <i class="bi bi-upload"></i> Upload
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <span><i class="bi bi-grid-3x3-gap"></i> Gallery Preview</span>
                                                <button class="btn btn-sm btn-outline-warning"
                                                    onclick="loadDocumentations()">
                                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Pilih Gallery untuk Preview:</label>
                                                    <select id="previewGalleryId" class="form-select form-select-sm"
                                                        onchange="loadDocumentations()">
                                                        <option value="">-- Pilih Gallery --</option>
                                                    </select>
                                                </div>
                                                <div id="docsList" class="d-flex flex-wrap gap-3"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
        <div id="toast" class="toast" role="alert">
            <div class="toast-header">
                <strong class="me-auto" id="toastTitle">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastBody"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ============ UTILITIES ============
        const API_BASE = () => document.getElementById('apiBaseUrl').value;
        const AUTH_TOKEN = () => document.getElementById('authToken').value;

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const title = document.getElementById('toastTitle');
            const body = document.getElementById('toastBody');
            title.textContent = type === 'success' ? '✓ Success' : '✗ Error';
            title.className = 'me-auto ' + (type === 'success' ? 'text-success' : 'text-danger');
            body.textContent = message;
            toast.classList.add(type === 'success' ? 'border-success' : 'border-danger');
            new bootstrap.Toast(toast).show();
        }

        async function apiRequest(endpoint, options = {}) {
            const url = API_BASE() + endpoint;

            // 🚨 PERBAIKAN: Header 'Accept' WAJIB selalu dikirim agar Laravel tahu ini request API
            const headers = {
                'Accept': 'application/json',
                ...(options.headers || {})
            };

            if (AUTH_TOKEN()) {
                headers['Authorization'] = `Bearer ${AUTH_TOKEN()}`;
            }

            // Jangan set Content-Type jika menggunakan FormData (biarkan browser yang handle multipart/form-data)
            if (!(options.body instanceof FormData)) {
                headers['Content-Type'] = 'application/json';
            }

            try {
                const response = await fetch(url, {
                    ...options,
                    headers
                });

                // Parse response (berhati-hati jika response kosong)
                let data = {};
                const text = await response.text();
                try {
                    data = text ? JSON.parse(text) : {};
                } catch (e) {
                    data = {
                        message: text || 'Invalid JSON response'
                    };
                }

                if (!response.ok) {
                    // Jika 422 (Validation Error), tampilkan detail field yang salah
                    if (response.status === 422 && data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join('\n');
                        throw new Error(`Validation Error:\n${errorMessages}`);
                    }
                    throw new Error(data.message || `HTTP ${response.status}`);
                }

                return data;
            } catch (err) {
                showToast(err.message, 'error');
                throw err;
            }
        }

        // ============ EVENTS ============
        document.getElementById('eventForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            // 🛠️ FIX 1: Append ":00" untuk seconds pada time fields
            //   const startTime = formData.get('start_time');
            //   const endTime = formData.get('end_time');
            //   if (startTime && startTime.length === 5) {
            //       formData.set('start_time', startTime + ':00');
            //   }
            //   if (endTime && endTime.length === 5) {
            //       formData.set('end_time', endTime + ':00');
            //   }

            // 🛠️ FIX 2: Gabungkan date + time jika backend butuh datetime penuh
            // Uncomment baris di bawah jika error masih muncul:

            const startDate = formData.get('start_date');
            const startTime = formData.get('start_time');
            const endDate = formData.get('end_date');
            const endTime = formData.get('end_time');
            if (startDate && startTime) {
                formData.set('start_time', `${startDate} ${startTime}:00`);
            }
            if (endDate && endTime) {
                formData.set('end_time', `${endDate} ${endTime}:00`);
            }


            try {
                await apiRequest('/events', {
                    method: 'POST',
                    body: formData
                });
                showToast('Event berhasil dibuat!');
                e.target.reset();
                document.getElementById('eventPreview').classList.add('d-none');
                loadEvents();
            } catch (err) {}
        });

        document.querySelector('input[name="cover_image"]').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const preview = document.getElementById('eventPreview');
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
            }
        });

        async function loadEvents() {
            const list = document.getElementById('eventsList');
            list.innerHTML = '<div class="text-center w-100"><div class="spinner-border"></div></div>';
            try {
                const res = await apiRequest('/events');
                const events = res.data.data;
                list.innerHTML = '';
                if (Array.isArray(events) && events.length === 0) {
                    list.innerHTML = '<div class="text-center text-muted w-100">Belum ada event</div>';
                    return;
                }
                events.forEach(ev => {
                    const col = document.createElement('div');
                    col.className = 'col-md-6';
                    col.innerHTML = `
                <div class="card h-100">
                    <img src="${ev.cover_image || 'https://via.placeholder.com/300x150?text=No+Image'}" 
                         class="card-img-top" style="height: 120px; object-fit: cover;" alt="cover">
                    <div class="card-body p-2">
                        <h6 class="card-title mb-1">${ev.title}</h6>
                        <small class="text-muted d-block">${ev.location}</small>
                        <small class="text-muted">${ev.start_date} - ${ev.end_date}</small>
                        <div class="mt-2">
                            <span class="badge bg-info">${ev.status || 'upcoming'}</span>
                            <span class="badge bg-secondary">ID: ${ev.id}</span>
                        </div>
                        <button class="btn btn-sm btn-outline-danger mt-2 w-100" onclick="deleteEvent(${ev.id})">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `;
                    list.appendChild(col);
                });
                populateEventSelects(events);
            } catch (err) {
                list.innerHTML = '<div class="text-center text-danger w-100">Gagal memuat data</div>';
            }
        }

        async function deleteEvent(id) {
            if (!confirm('Hapus event ini?')) return;
            try {
                await apiRequest(`/events/${id}`, {
                    method: 'DELETE'
                });
                showToast('Event dihapus!');
                loadEvents();
            } catch (err) {}
        }

        // ============ LOAD CATEGORIES ============
        async function loadEventCategories() {
            try {
                const res = await apiRequest('/event-categories');
                const categories = res.data.data;

                const categorySelect = document.querySelector('input[name="event_category_id"]');

                // Ganti input number menjadi select
                const select = document.createElement('select');
                select.name = 'event_category_id';
                select.className = 'form-select';
                select.required = true;
                select.innerHTML = '<option value="">-- Pilih Kategori --</option>';

                console.log(categories)
                if (Array.isArray(categories)) {
                    categories.forEach(cat => {
                        select.innerHTML +=
                            `<option value="${cat.id}">${cat.name} (ID: ${cat.id})</option>`;
                    });
                }

                categorySelect.replaceWith(select);
            } catch (err) {
                console.log('Gagal load categories, gunakan input manual');
            }
        }

        // Panggil saat halaman load
        window.addEventListener('DOMContentLoaded', () => {
            loadEvents();
            loadDocCategories();
            loadGalleries()
            loadEventCategories(); // ← Tambahkan ini
            setTimeout(() => loadGalleries(), 500);
        });

        function populateEventSelects(events) {
            const gallerySelect = document.getElementById('galleryEventSelect');
            const filterSelect = document.getElementById('filterEventId');
            const currentGalleryVal = gallerySelect.value;
            const currentFilterVal = filterSelect.value;

            [gallerySelect, filterSelect].forEach((select, idx) => {
                const defaultOpt = idx === 0 ? '<option value="">-- Pilih Event --</option>' :
                    '<option value="">-- Semua Event --</option>';
                select.innerHTML = defaultOpt;
                events.forEach(ev => {
                    select.innerHTML += `<option value="${ev.id}">${ev.title} (ID: ${ev.id})</option>`;
                });
            });
            gallerySelect.value = currentGalleryVal;
            filterSelect.value = currentFilterVal;
        }

        // ============ GALLERIES ============
        document.getElementById('galleryForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                await apiRequest('/doc-galleries', {
                    method: 'POST',
                    body: formData
                });
                showToast('Gallery berhasil dibuat!');
                e.target.reset();
                loadGalleries();
            } catch (err) {}
        });

        async function loadGalleries() {
            const list = document.getElementById('galleriesList');
            const filterEventId = document.getElementById('filterEventId').value;
            list.innerHTML = '<div class="text-center"><div class="spinner-border"></div></div>';
            try {
                let url = '/doc-galleries';
                if (filterEventId) url += `?event_id=${filterEventId}`;
                const res = await apiRequest(url);
                const galleries = res.data.data;
                console.log(galleries)
                list.innerHTML = '';
                if (galleries && galleries.length === 0) {
                    list.innerHTML = '<div class="text-center text-muted p-3">Belum ada gallery</div>';
                    return;
                }
                galleries.forEach(g => {
                    const item = document.createElement('div');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center';
                    item.innerHTML = `
                <div>
                    <strong>${ g.doc_category.name || 'Untitled'}</strong>
                    <br><small class="text-muted">Event ID: ${g.event_id} | Gallery ID: ${g.id}</small>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-primary" onclick="selectGalleryForDocs(${g.id}, '${(g.doc_category.name)}')>
                        <i class="bi bi-eye"></i> View
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteGallery(${g.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
                    list.appendChild(item);
                });
                populateGallerySelects(galleries);
            } catch (err) {
                list.innerHTML = '<div class="text-center text-danger p-3">Gagal memuat data</div>';
            }
        }

        // ============ LOAD CATEGORIES ============
        async function loadDocCategories() {
            try {
                const res = await apiRequest('/doc-categories');
                const categories = res.data.data;

                const categorySelect = document.getElementById('galleryDocCategorySelect');

                // Ganti input number menjadi select
                const select = document.createElement('select');
                select.name = 'doc_category_id';
                select.id = 'galleryDocCategorySelect';
                select.className = 'form-select';
                select.required = true;
                select.innerHTML = '<option value="">-- Pilih Kategori --</option>';

                console.log(categories)
                if (Array.isArray(categories)) {
                    categories.forEach(cat => {
                        select.innerHTML +=
                            `<option value="${cat.id}">${cat.name} (ID: ${cat.id})</option>`;
                    });
                }

                categorySelect.replaceWith(select);
            } catch (err) {
                console.log('Gagal load categories, gunakan input manual');
            }
        }


        async function deleteGallery(id) {
            if (!confirm('Hapus gallery ini?')) return;
            try {
                await apiRequest(`/doc-galleries/${id}`, {
                    method: 'DELETE'
                });
                showToast('Gallery dihapus!');
                loadGalleries();
            } catch (err) {}
        }


        function populateGallerySelects(galleries) {
            const docSelect = document.getElementById('docGallerySelect');
            const previewSelect = document.getElementById('previewGalleryId');
            const currentDocVal = docSelect.value;
            const currentPreviewVal = previewSelect.value;

            [docSelect, previewSelect].forEach((select, idx) => {
                const defaultOpt = '<option value="">-- Pilih Gallery --</option>';
                select.innerHTML = defaultOpt;
                galleries.forEach(g => {
                    select.innerHTML +=
                        `<option value="${g.id}">${g.doc_category.name} (ID: ${g.id})</option>`;
                });
            });
            docSelect.value = currentDocVal;
            previewSelect.value = currentPreviewVal;
        }

        function selectGalleryForDocs(id, name) {
            document.getElementById('previewGalleryId').value = id;
            document.querySelector('button[data-bs-target="#docs"]').click();
            loadDocumentations();
        }

        // ============ DOCUMENTATIONS ============
        document.getElementById('docForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            console.log(formData)
            const btn = e.target.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading...';
            try {
                await apiRequest('/documentations', {
                    method: 'POST',
                    body: formData
                });
                showToast('Foto berhasil diupload!');
                e.target.reset();
                document.getElementById('docPreview').classList.add('d-none');
                loadDocumentations();
            } catch (err) {} finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-upload"></i> Upload';
            }
        });

        document.getElementById('docImage').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const preview = document.getElementById('docPreview');
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
            }
        });

        async function loadDocumentations() {
            const list = document.getElementById('docsList');
            const galleryId = document.getElementById('previewGalleryId').value;
            if (!galleryId) {
                list.innerHTML = '<div class="text-center text-muted w-100">Pilih gallery untuk melihat preview</div>';
                return;
            }
            list.innerHTML = '<div class="text-center w-100"><div class="spinner-border"></div></div>';
            try {
                const res = await apiRequest(`/documentations?gallery_id=${galleryId}`);
                const docs = res.data;
                console.log(docs)
                list.innerHTML = '';
                if (Array.isArray(docs) && docs.length === 0) {
                    list.innerHTML = '<div class="text-center text-muted w-100">Belum ada foto di gallery ini</div>';
                    return;
                }
                docs.forEach(d => {
                    const card = document.createElement('div');
                    card.className = 'doc-card';
                    const sizeClass = `doc-${d.type || 'medium'}`;
                    card.innerHTML = `
                <div class="card ${sizeClass}">
                    <img src="${d.url || d.file_path}" class="card-img-top" alt="${d.alt_text || ''}">
                    <div class="card-body p-1">
                        <small class="d-block text-truncate" title="${d.alt_text || ''}">${d.alt_text || 'No alt'}</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-${d.type === 'small' ? 'secondary' : d.type === 'medium' ? 'primary' : 'success'}">${d.type}</span>
                            <button class="btn btn-sm btn-link text-danger p-0" onclick="deleteDoc(${d.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
                    list.appendChild(card);
                });
            } catch (err) {
                list.innerHTML = '<div class="text-center text-danger w-100">Gagal memuat data</div>';
            }
        }

        async function deleteDoc(id) {
            if (!confirm('Hapus foto ini?')) return;
            try {
                await apiRequest(`/documentations/${id}`, {
                    method: 'DELETE'
                });
                showToast('Foto dihapus!');
                loadDocumentations();
            } catch (err) {}
        }

        // ============ INIT ============
        window.addEventListener('DOMContentLoaded', () => {
            loadEvents();
            setTimeout(() => loadGalleries(), 500);
        });
    </script>

</body>

</html>
