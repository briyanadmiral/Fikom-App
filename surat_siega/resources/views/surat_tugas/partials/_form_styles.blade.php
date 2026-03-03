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

            /* ===============================
             * TEMPLATE SELECTOR COMPONENT
             * ===============================*/
            .template-selector-section {
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(0,0,0,0.08);
                border: 1px solid rgba(0,0,0,0.05);
            }
            .template-selector-header {
                background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%);
                padding: 1rem 1.25rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .template-icon-box {
                width: 45px;
                height: 45px;
                background: rgba(255,255,255,0.2);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
                color: #fff;
            }
            .template-selector-body {
                background: #fff;
                padding: 1.25rem;
            }
            .template-preview-card {
                background: linear-gradient(135deg, #f8f9fc 0%, #fff 100%);
                border: 1px solid #e9ecef;
                border-radius: 10px;
                padding: 1rem;
                height: 100%;
            }
            .template-preview-title {
                font-weight: 700;
                color: #2d3436;
                font-size: 1rem;
            }
            .template-preview-desc {
                color: #6c757d;
                font-size: 0.85rem;
            }
            .template-preview-badges .badge {
                font-size: 0.75rem;
                font-weight: 600;
                padding: 0.4rem 0.6rem;
            }
            .template-preview-empty {
                background: #f8f9fc;
                border: 2px dashed #dee2e6;
                border-radius: 10px;
                padding: 2rem;
                text-align: center;
                height: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 120px;
            }
        </style>
    @endpush
@endonce
