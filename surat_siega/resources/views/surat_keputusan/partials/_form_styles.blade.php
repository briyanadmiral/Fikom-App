@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
  body{background:#f7faff}
  .surat-header{background:#f3f6fa;padding:1.3rem 2.2rem 1.3rem 1.8rem;border-radius:1.1rem;margin-bottom:2.2rem;border:1px solid #e0e6ed;display:flex;align-items:center;gap:1.3rem}
  .surat-header .icon{background:linear-gradient(135deg,#1498ff 0,#1fc8ff 100%);width:54px;height:54px;display:flex;align-items:center;justify-content:center;border-radius:50%;box-shadow:0 1px 10px #1498ff30;font-size:2rem}
  .surat-header-title{font-weight:bold;color:#0056b3;font-size:1.85rem;margin-bottom:.13rem;letter-spacing:-1px}
  .surat-header-desc{color:#636e7b;font-size:1.03rem}
  .stat-wrapper{display:flex;justify-content:flex-start;gap:1.2rem;margin-bottom:2.1rem;flex-wrap:wrap}
  .stat-card{width:170px;border-radius:.85rem;border:none;background:#fff}
  .stat-card .card-body{text-align:center;padding:1.15rem 1rem}
  .stat-card .icon{font-size:2.3rem;margin-bottom:.5rem}
  .stat-card .label{color:#6c757d;font-size:.83rem;margin-bottom:.25rem;font-weight:600;text-transform:uppercase;letter-spacing:1px}
  .stat-card .value{font-size:2.1rem;font-weight:700;line-height:1.1}
  .card.filter-card{margin-bottom:2.2rem;border-radius:1rem}
  .card.filter-card .card-header{background:#f8fafc;border-radius:1rem 1rem 0 0;border:none}
  .card.filter-card .card-body{padding-bottom:.7rem}
  .card.data-card{border-radius:1rem}
  .card.data-card .card-body{padding-top:1.2rem}
  .table th,.table td{vertical-align:middle!important}
  .table{background:#fff}

  /* Dropdown warna */
  .dropdown-menu .dropdown-item{cursor:pointer;padding:.5rem 1rem;transition:.2s;display:flex;align-items:center}
  .dropdown-menu .dropdown-item i{width:20px;text-align:center;margin-right:8px}
  .dropdown-item.text-info{color:#17a2b8!important}
  .dropdown-item.text-warning{color:#ffc107!important}
  .dropdown-item.text-success{color:#28a745!important}
  .dropdown-item.text-danger{color:#dc3545!important}
  .dropdown-item.text-primary{color:#007bff!important}
  .dropdown-item.text-dark{color:#343a40!important}
  .dropdown-item.text-secondary{color:#6c757d!important}
  .dropdown-item.text-info:hover{background-color:#17a2b8!important;color:#fff!important}
  .dropdown-item.text-warning:hover{background-color:#ffc107!important;color:#212529!important}
  .dropdown-item.text-success:hover{background-color:#28a745!important;color:#fff!important}
  .dropdown-item.text-danger:hover{background-color:#dc3545!important;color:#fff!important}
  .dropdown-item.text-primary:hover{background-color:#007bff!important;color:#fff!important}
  .dropdown-item.text-dark:hover{background-color:#343a40!important;color:#fff!important}
  .dropdown-item.text-secondary:hover{background-color:#6c757d!important;color:#fff!important}
  .dropdown-item.text-warning:hover i{color:#212529!important}
  .dropdown-item:hover i{color:inherit!important}

  .badge-pill{
    padding:.45rem .85rem;
    font-size:.85rem;
    font-weight:600;
    letter-spacing:.3px;
  }

  /* ===============================
   *  FIX LAYOUT TEMBUSAN / TAGIFY
   * ===============================*/
  .tembusan-wrap{
      background:#f8f9ff;
      border-radius:1rem;
      border:1px solid #e0e6ed;
      padding:1.25rem 1.5rem;
  }
  .tembusan-head{
      display:flex;
      align-items:center;
      justify-content:space-between;
      margin-bottom:.75rem;
  }
  .tembusan-tools .btn{
      padding:.25rem .5rem;
      border-radius:.5rem;
  }
  /* Container Tagify full width & multi-row rapi */
  /* Container Tagify */
  .tembusan-body .tagify {
      width: 100%;
      height: auto !important; /* Paksa tinggi menyesuaikan isi */
      min-height: 44px;
      border-radius: .6rem;
      border: 1px solid #ced4da;
      background: #fff;
      
      /* FIX NABRAK: Gunakan flexbox agar tag turun ke bawah dengan rapi */
      display: flex;
      align-items: flex-start;
      flex-wrap: wrap;
      gap: 5px; /* Jarak antar elemen */
      padding: 5px 8px;
  }

  /* Style Tag Individual */
  .tembusan-body .tagify__tag {
      margin: 0 !important; /* Reset margin karena sudah pakai gap */
  }
  
  /* Input area pengetikan agar tidak sempit */
  .tembusan-body .tagify__input {
      margin: 0 !important;
      min-width: 150px; /* Beri ruang minimal untuk mengetik */
      line-height: 24px; /* Sesuaikan tinggi baris */
  }

  /* Dropdown suggestions */
  .tembusan-body .tagify__dropdown {
      z-index: 1060;
  }
  /* Preview di-box sendiri */
  .tembusan-preview{
      margin-top:1rem;
      padding:.75rem 1rem;
      border-radius:.75rem;
      border:1px dashed #d0d7e2;
      background:#fff;
      font-size:.9rem;
  }
  .tembusan-preview ol{
      margin-bottom:0;
      padding-left:1.1rem;
  }

  @media (max-width:767.98px){
    .surat-header{flex-direction:column;align-items:flex-start;padding:1.2rem 1rem;gap:.7rem}
    .stat-wrapper{flex-direction:column;gap:.8rem}
    .stat-card{width:100%}
    .surat-header-title{font-size:1.18rem}
    .surat-header-desc{font-size:.99rem}
    .card.filter-card,.card.data-card{border-radius:.6rem}
    .tembusan-wrap{padding:1rem}
  }
</style>
@endpush
