document.addEventListener('DOMContentLoaded', () => {
    const yearEl = document.getElementById('year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    // FAQ accordion
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.faq-item');
            const answer = item.querySelector('.faq-answer');
            const isOpen = item.classList.contains('open');
            document.querySelectorAll('.faq-item').forEach(i => {
                i.classList.remove('open');
                i.querySelector('.faq-answer').style.maxHeight = '0';
                i.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
            });
            if (!isOpen) {
                item.classList.add('open');
                answer.style.maxHeight = answer.scrollHeight + 'px';
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // Ouvrir la première FAQ au chargement
    const firstOpen = document.querySelector('.faq-item.open .faq-answer');
    if (firstOpen) firstOpen.style.maxHeight = firstOpen.scrollHeight + 'px';

    // Smooth scroll pour les ancres
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const href = a.getAttribute('href');
            if (href === '#') return;
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Feedback visuel sur envoi du formulaire (backend à brancher)
    const formSubmit = document.querySelector('.form-submit');
    if (formSubmit) {
        formSubmit.addEventListener('click', () => {
            formSubmit.textContent = '✓ Demande envoyée — réponse sous 2h ouvrées';
            formSubmit.style.background = 'var(--wave)';
            formSubmit.disabled = true;
        });
    }
});
