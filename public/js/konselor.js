/* SILAKON — Portal Konselor
   Simpan sebagai: public/js/konselor.js */

document.addEventListener('DOMContentLoaded', function () {
    var tombol = document.getElementById('tombolSidebar');
    if (!tombol) return;

    tombol.addEventListener('click', function () {
        var tertutup = document.body.classList.toggle('sidebar-tutup');
        tombol.setAttribute('aria-label', tertutup ? 'Tampilkan menu' : 'Sembunyikan menu');
    });
});