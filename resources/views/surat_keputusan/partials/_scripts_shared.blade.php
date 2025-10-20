<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){
  const table = $('#{{ $tableId ?? "table-sk" }}').DataTable({
    responsive: true,
    autoWidth: false,
    language: {
      url: "/assets/datatables/i18n/id.json",
      emptyTable: "{{ $emptyMsg ?? 'Tidak ada data.' }}"
    },
    columnDefs: [{ targets: [-1], orderable: false, searchable: false }]
  });

  $('#globalSearch').on('keyup', function(){ table.search(this.value).draw(); });
  $('#statusFilter').on('change', function(){
    const v = this.value;
    table.column(5).search(v ? '^'+v+'$' : '', true, false).draw();
  });
  $('#resetFilters').on('click', function(){
    $('#globalSearch').val(''); $('#statusFilter').val('');
    table.search('').columns().search('').draw();
  });

  @if(session('success'))
    Swal.fire({ icon:'success', title:'Berhasil!', text:"{{ session('success') }}", timer:2500, showConfirmButton:false });
  @endif
});
</script>
