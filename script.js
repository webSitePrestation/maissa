/* =========================================
   MAÎTRESSE MAISSA — script.js
   ========================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ─── Hamburger menu ─── */
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('nav-links');

  hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('open');
    navLinks.classList.toggle('open');
  });

  navLinks.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
      hamburger.classList.remove('open');
      navLinks.classList.remove('open');
    });
  });

  /* ─── Navbar scroll ─── */
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 60);
  }, { passive: true });

  /* ─── Active nav link ─── */
  const sections = document.querySelectorAll('section[id]');
  const allLinks = document.querySelectorAll('.nav-link');

  const observerNav = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        allLinks.forEach(l => l.classList.remove('active'));
        const active = document.querySelector(`.nav-link[href="#${entry.target.id}"]`);
        if (active) active.classList.add('active');
      }
    });
  }, { rootMargin: '-40% 0px -55% 0px' });

  sections.forEach(s => observerNav.observe(s));

  /* ─── Fade-in on scroll ─── */
  const fadeEls = document.querySelectorAll('.fade-in');

  const observerFade = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        // Stagger siblings
        const siblings = entry.target.closest('.pratiques-grid, .tarifs-grid, .avis-grid, .contact-grid');
        if (siblings) {
          const all = siblings.querySelectorAll('.fade-in');
          all.forEach((el, idx) => {
            setTimeout(() => el.classList.add('visible'), idx * 120);
          });
          observerFade.unobserve(entry.target);
        } else {
          entry.target.classList.add('visible');
          observerFade.unobserve(entry.target);
        }
      }
    });
  }, { threshold: 0.12 });

  fadeEls.forEach(el => observerFade.observe(el));

  /* ─── Lightbox ─── */
  const lightbox     = document.getElementById('lightbox');
  const lightboxImg  = document.getElementById('lightbox-img');
  const lightboxClose= document.getElementById('lightbox-close');

  document.querySelectorAll('.galerie-item').forEach(item => {
    item.addEventListener('click', () => {
      const img = item.querySelector('img');
      lightboxImg.src = img.src;
      lightboxImg.alt = img.alt;
      lightbox.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  });

  const closeLightbox = () => {
    lightbox.classList.remove('active');
    document.body.style.overflow = '';
    setTimeout(() => { lightboxImg.src = ''; }, 300);
  };

  lightboxClose.addEventListener('click', closeLightbox);
  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) closeLightbox();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeLightbox();
  });

  /* ─── Contact form — envoi AJAX vers send_contact.php ─── */
  const form      = document.getElementById('contact-form');
  const success   = document.getElementById('form-success');
  const errorBox  = document.getElementById('form-error');
  const submitBtn = document.getElementById('submit-btn');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Reset messages
    success.style.display  = 'none';
    errorBox.style.display = 'none';

    // Validation côté client
    const required = form.querySelectorAll('[required]');
    let valid = true;

    required.forEach(field => {
      if (field.type === 'checkbox' && !field.checked) {
        field.style.outline = '2px solid #c97b8e';
        valid = false;
      } else if (field.type !== 'checkbox' && !field.value.trim()) {
        field.style.borderColor = '#c97b8e';
        valid = false;
      } else {
        field.style.borderColor = '';
        field.style.outline     = '';
      }
    });

    if (!valid) {
      errorBox.textContent    = 'Merci de remplir tous les champs obligatoires.';
      errorBox.style.display  = 'block';
      return;
    }

    // Envoi AJAX
    submitBtn.disabled      = true;
    submitBtn.innerHTML     = 'Envoi en cours… <i class="fa-solid fa-spinner fa-spin"></i>';

    try {
      const formData = new FormData(form);
      const response = await fetch('send_contact.php', {
        method : 'POST',
        body   : formData,
      });

      const data = await response.json();

      if (data.success) {
        success.textContent    = '✓ ' + data.message;
        success.style.display  = 'block';
        form.reset();
      } else {
        errorBox.textContent   = '✗ ' + data.message;
        errorBox.style.display = 'block';
      }

    } catch (err) {
      errorBox.textContent   = '✗ Une erreur réseau est survenue. Réessayez ou contactez-moi sur X.';
      errorBox.style.display = 'block';
    } finally {
      submitBtn.disabled  = false;
      submitBtn.innerHTML = 'Envoyer ma demande <i class="fa-solid fa-arrow-right"></i>';
    }
  });

  // Réinitialise la couleur d'erreur au focus
  form.querySelectorAll('input, select, textarea').forEach(field => {
    field.addEventListener('focus', () => {
      field.style.borderColor = '';
      field.style.outline     = '';
    });
  });

  /* ─── Smooth anchor for CTA buttons ─── */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

});
