{{-- resources/views/surat_tugas/partials/_form_styles.blade.php --}}
@once
    @push('styles')
        {{-- Vendor CSS --}}
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
        <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
        {{-- Minor tweaks --}}
        <style>
            .select2-container--bootstrap4 {
                width: 100% !important;
                /* pastikan full width kolom */
            }

            .select2-container--bootstrap4 .select2-selection--single {
                height: calc(2.25rem + 2px) !important;
                line-height: calc(2.25rem + 2px) !important;
            }

            .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
                line-height: calc(2.25rem + 2px) !important;
                padding-left: .75rem;
                /* biar teks sejajar dgn input lain */
            }

            .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
                height: calc(2.25rem + 2px) !important;
                top: 0;
                /* center vertikal */
            }

            #penerima-table thead th {
                text-align: center;
                vertical-align: middle
            }

            #penerima-table tbody td:first-child {
                text-align: center
            }

            .ck-editor__editable_inline {
                min-height: 250px
            }

            /* --- TEMBUSAN --- */
            .tembusan-wrap {
                border: 1px solid #e9ecef;
                border-radius: .5rem;
                overflow: hidden
            }

            .tembusan-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: .75rem 1rem;
                background: linear-gradient(90deg, #5c7cfa 0%, #845ef7 100%);
                color: #fff
            }

            .tembusan-body {
                padding: 1rem;
                background: #fff
            }

            .tembusan-preview {
                background: #f8f9ff;
                border: 1px dashed #cdd5ff;
                border-radius: .5rem;
                padding: 1rem
            }

            .tembusan-preview h6 {
                font-weight: 700;
                color: #3b5bdb;
                margin-bottom: .5rem
            }

            .tembusan-tools .btn {
                margin-left: .5rem
            }

            .tagify__tag {
                font-weight: 600
            }

            .tagify__input {
                min-width: 140px
            }

            /* Task preview */
            #task-preview {
                background: #f8f9fa;
                border: 1px dashed #ced4da;
                border-radius: .25rem;
                padding: 1.5rem;
                min-height: 158px;
                display: flex;
                align-items: center;
                justify-content: center
            }

            #task-preview.has-content {
                align-items: flex-start;
                justify-content: flex-start
            }

            #task-preview .placeholder-text {
                color: #6c757d;
                font-style: italic
            }

            #task-preview .preview-title {
                font-weight: 600;
                color: #007bff
            }

            #task-preview .preview-content {
                font-size: 1.1rem
            }

            /* ===============================
            * FIX LAYOUT TEMBUSAN / TAGIFY (REVISI FINAL)
            * ===============================*/
        
            /* Container Utama Tagify */
            .tembusan-body .tagify {
                width: 100%;
                height: auto !important;  /* Wajib auto agar memanjang ke bawah */
                min-height: 44px;
                
                background: #fff;
                border: 1px solid #ced4da;
                border-radius: .6rem;
                padding: 4px 8px; /* Padding di dalam kotak putih */

                /* Layout: Gunakan Flexbox + Wrap */
                display: flex;
                align-items: center; /* Pastikan item sejajar vertikal (tengah) */
                flex-wrap: wrap;     /* Wajib wrap agar turun baris */
            }

            /* Style Tag Individual */
            .tembusan-body .tagify__tag {
                /* Gunakan MARGIN, jangan GAP. Ini lebih stabil untuk Tagify */
                margin: 3px 6px 3px 0 !important; 
            }
            
            /* Area Input (Tempat Mengetik) */
            .tembusan-body .tagify__input {
                margin: 3px 0 !important;
                padding: 0;
                
                /* Penting: Input harus mengisi sisa ruang (flex-grow) 
                dan punya lebar minimal agar tidak gepeng */
                flex-grow: 1; 
                min-width: 150px; 
                
                line-height: 2rem; /* Tinggi baris disesuaikan agar placeholder pas */
                display: inline-block;
            }

            /* Fix untuk Placeholder "Misal: Yth..." agar tidak tertutup */
            .tembusan-body .tagify__input::before {
                line-height: 2rem !important;
                position: static !important; /* Hindari absolute positioning */
                display: inline-block;
                color: #adb5bd; /* Warna text placeholder */
            }

            /* Dropdown suggestions */
            .tembusan-body .tagify__dropdown {
                z-index: 1060;
            }
        </style>
    @endpush
@endonce
