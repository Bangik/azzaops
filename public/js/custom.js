$(document).ready(function () {

    // ========== CSRF for all AJAX ==========
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ========== Sidebar toggle (mobile) ==========
    $('#sidebarToggle').on('click', function () {
        $('#sidebar').addClass('show');
        $('#sidebarOverlay').addClass('show');
    });

    $('#sidebarOverlay').on('click', function () {
        $('#sidebar').removeClass('show');
        $(this).removeClass('show');
    });

    // Close sidebar on nav click (mobile)
    $('#sidebar .nav-link').on('click', function () {
        if (window.innerWidth < 768) {
            $('#sidebar').removeClass('show');
            $('#sidebarOverlay').removeClass('show');
        }
    });

    // ========== Flatpickr locale (Indonesian) ==========
    if (typeof flatpickr !== 'undefined') {
        if (flatpickr.l10ns && flatpickr.l10ns.id) {
            flatpickr.localize(flatpickr.l10ns.id);
        }
        $('.datepicker').flatpickr({
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
        });
    }

});

// ========== Delete confirmation ==========
function confirmDelete(message) {
    return confirm(message || 'Yakin ingin menghapus data ini?');
}
