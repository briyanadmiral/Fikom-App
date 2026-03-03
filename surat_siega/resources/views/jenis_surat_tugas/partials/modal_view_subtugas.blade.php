{{-- Modal View Sub Tugas --}}
<div class="modal fade" id="modalViewSubTugas" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1050;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-tasks mr-2"></i>Detail Sub Tugas
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div id="subTugasList"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                <a href="#" id="btnManageSubTugas" class="btn btn-primary btn-sm">
                    <i class="fas fa-cog mr-1"></i>Kelola Sub Tugas
                </a>
            </div>
        </div>
    </div>
</div>
