document.addEventListener("DOMContentLoaded", function() {
    
    const container = document.getElementById('scrollContainer');
    const btnLeft = document.getElementById('scrollLeftBtn');
    const btnRight = document.getElementById('scrollRightBtn');

    if (container && btnLeft && btnRight) {

        // Fungsi: Cek posisi scroll untuk sembunyikan/munculkan tombol
        const updateButtons = () => {
            // 1. Cek Mentok Kiri
            if (container.scrollLeft <= 1) {
                btnLeft.classList.remove('show');
            } else {
                btnLeft.classList.add('show');
            }

            // 2. Cek Mentok Kanan
            // Rumus: Total Lebar Isi - Lebar Layar yg Terlihat
            const maxScrollLeft = container.scrollWidth - container.clientWidth;
            
            // Gunakan Math.ceil karena zoom browser bisa bikin angka desimal
            if (Math.ceil(container.scrollLeft) >= maxScrollLeft - 1) {
                btnRight.classList.remove('show');
            } else {
                btnRight.classList.add('show');
            }
        };

        // Event Listener Tombol Kiri
        btnLeft.addEventListener('click', () => {
            container.scrollBy({
                left: -340, // Geser ke kiri (lebar kartu + gap)
                behavior: 'smooth'
            });
        });

        // Event Listener Tombol Kanan
        btnRight.addEventListener('click', () => {
            container.scrollBy({
                left: 340, // Geser ke kanan
                behavior: 'smooth'
            });
        });

        // Update tombol saat user scroll manual (touchpad/touchscreen)
        container.addEventListener('scroll', updateButtons);

        // Update tombol saat loading pertama kali
        updateButtons();
        
        // Update tombol saat layar di-resize
        window.addEventListener('resize', updateButtons);
    }
});