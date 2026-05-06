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

    // Soumission AJAX du formulaire de contact
    const form = document.getElementById('contact-form');
    if (form) {
        const status = form.querySelector('#form-status');
        const submitBtn = form.querySelector('.form-submit');
        const initialBtnText = submitBtn ? submitBtn.textContent : '';

        const setStatus = (msg, type) => {
            if (!status) return;
            status.textContent = msg;
            status.classList.remove('is-success', 'is-error');
            if (type) status.classList.add(`is-${type}`);
        };

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            setStatus('', null);

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Envoi en cours…';

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json().catch(() => ({}));

                if (res.ok && data.ok) {
                    form.reset();
                    submitBtn.textContent = '✓ Demande envoyée';
                    submitBtn.style.background = 'var(--wave)';
                    setStatus('Merci ! Nous vous répondons sous 2h ouvrées.', 'success');
                } else {
                    submitBtn.disabled = false;
                    submitBtn.textContent = initialBtnText;
                    setStatus(data.error || 'Erreur lors de l\'envoi. Merci de réessayer.', 'error');
                }
            } catch (err) {
                submitBtn.disabled = false;
                submitBtn.textContent = initialBtnText;
                setStatus('Connexion impossible. Merci de réessayer ou de nous appeler.', 'error');
            }
        });
    }
});
