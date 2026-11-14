/**
 * Main JavaScript
 * Sistem Informasi Fasilitas STMIK IKMI Cirebon
 */

document.addEventListener('DOMContentLoaded', () => {
    // Smooth scrolling untuk link anchor pada navbar
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const targetId = link.getAttribute('href');
            if (targetId && targetId !== '#') {
                const targetEl = document.querySelector(targetId);
                if (targetEl) {
                    e.preventDefault();
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });

    console.log("Sistem Informasi Fasilitas STMIK IKMI Cirebon siap digunakan.");
});
