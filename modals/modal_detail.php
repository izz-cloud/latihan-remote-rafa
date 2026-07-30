<?php

/**
 * modals/modal_detail.php
 * Modal: Detail informasi barang
 */
?>
<!-- ===========================
     MODAL DETAIL BARANG
     =========================== -->
<div class="modal-overlay" id="modal-detail">
    <div class="modal">

        <!-- Header Modal -->
        <div class="modal-header">
            <div class="modal-title">Detail Barang</div>
            <button class="modal-close" onclick="closeModal('modal-detail')">✕</button>
        </div>

        <!-- Body Modal: diisi oleh JavaScript (lihatDetail) -->
        <div class="modal-body" id="modal-detail-body"></div>

        <!-- Footer Modal -->
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-detail')">Tutup</button>
        </div>

    </div>
</div>