document.documentElement.classList.remove('no-js');

document.addEventListener('DOMContentLoaded', function () {
    // Navbar: sombra/compactação ao rolar
    const navbar = document.getElementById('main-nav');
    const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 30);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    // Menu mobile: fecha ao clicar em um link
    const collapseEl = document.getElementById('navbarNav');
    collapseEl.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (collapseEl.classList.contains('show')) {
                bootstrap.Collapse.getOrCreateInstance(collapseEl).hide();
            }
        });
    });

    // Revelação de elementos ao rolar
    const reveals = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        reveals.forEach(el => io.observe(el));
    } else {
        reveals.forEach(el => el.classList.add('is-visible'));
    }

    // Formulário: validação do Bootstrap + estado de envio
    document.querySelectorAll('form.needs-validation').forEach(form => {
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated');
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) firstInvalid.focus();
                return;
            }
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.querySelector('.btn-label')?.classList.add('d-none');
                btn.querySelector('.btn-loading')?.classList.remove('d-none');
            }
        });
    });
});
