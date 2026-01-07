document.addEventListener('DOMContentLoaded', () => {

    //GESTIONE MENU HAMBURGER
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const menuWrapper = document.getElementById('menuWrapper');

    if (hamburgerBtn && menuWrapper) {
        hamburgerBtn.addEventListener('click', () => {
            menuWrapper.classList.toggle('active');

            hamburgerBtn.classList.toggle('open');
        });
    }

    //GESTIONE DARK MODE
    const themeBtn = document.getElementById('themeBtn');
    const body = document.body;
    const savedTheme = localStorage.getItem('theme');
    
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
    }

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        });
    }
});