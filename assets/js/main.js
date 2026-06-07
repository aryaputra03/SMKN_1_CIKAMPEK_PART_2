// ============================================
//  SMKN 1 CIKAMPEK — main.js
//  Animasi, Slider, Counter, Carousel, dll.
// ============================================

document.addEventListener('DOMContentLoaded', () => {

  // ── JAM DIGITAL ────────────────────────────────
  function updateJam() {
    const el = document.getElementById('jam-digital');
    if (!el) return;
    const now = new Date();
    const h = String(now.getHours()).padStart(2,'0');
    const m = String(now.getMinutes()).padStart(2,'0');
    const s = String(now.getSeconds()).padStart(2,'0');
    el.textContent = `${h}:${m}:${s}`;
  }
  updateJam();
  setInterval(updateJam, 1000);

  // ── HEADER SCROLL ───────────────────────────────
  const header = document.querySelector('.header');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 60) {
      header?.classList.add('scrolled');
    } else {
      header?.classList.remove('scrolled');
    }
  });

  // ── MOBILE MENU ─────────────────────────────────
  const hamburger = document.querySelector('.hamburger');
  const mobileMenu = document.querySelector('.mobile-menu');

  hamburger?.addEventListener('click', () => {
    hamburger.classList.toggle('open');
    mobileMenu?.classList.toggle('open');
  });

  // Mobile dropdown toggle
  document.querySelectorAll('.mobile-nav-link[data-toggle]').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-toggle');
      const target = document.getElementById(targetId);
      target?.classList.toggle('open');
      const caret = btn.querySelector('.mob-caret');
      if (caret) {
        caret.style.transform = target?.classList.contains('open')
          ? 'rotate(180deg)' : 'rotate(0deg)';
      }
    });
  });

  // Tutup mobile menu saat link diklik
  document.querySelectorAll('.mobile-dropdown a').forEach(link => {
    link.addEventListener('click', () => {
      hamburger?.classList.remove('open');
      mobileMenu?.classList.remove('open');
    });
  });

  // ── HERO SLIDER ─────────────────────────────────
  const slides = document.querySelectorAll('.hero-slide');
  const dots = document.querySelectorAll('.slider-dot');
  let currentSlide = 0;
  let slideTimer;

  function goToSlide(index) {
    slides[currentSlide]?.classList.remove('active');
    dots[currentSlide]?.classList.remove('active');
    currentSlide = (index + slides.length) % slides.length;
    slides[currentSlide]?.classList.add('active');
    dots[currentSlide]?.classList.add('active');
  }

  function nextSlide() { goToSlide(currentSlide + 1); }
  function prevSlide() { goToSlide(currentSlide - 1); }

  function startAutoSlide() {
    slideTimer = setInterval(nextSlide, 5500);
  }
  function stopAutoSlide() { clearInterval(slideTimer); }

  if (slides.length > 0) {
    startAutoSlide();
    document.querySelector('.slider-arrow.next')?.addEventListener('click', () => {
      stopAutoSlide(); nextSlide(); startAutoSlide();
    });
    document.querySelector('.slider-arrow.prev')?.addEventListener('click', () => {
      stopAutoSlide(); prevSlide(); startAutoSlide();
    });
    dots.forEach((dot, i) => {
      dot.addEventListener('click', () => {
        stopAutoSlide(); goToSlide(i); startAutoSlide();
      });
    });
  }

  // ── COUNTER ANIMASI ─────────────────────────────
  function animateCounter(el, target, suffix = '') {
    let current = 0;
    const duration = 1800;
    const increment = target / (duration / 16);
    const timer = setInterval(() => {
      current += increment;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      el.textContent = Math.floor(current).toLocaleString('id-ID') + suffix;
    }, 16);
  }

  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseInt(el.getAttribute('data-target') || '0');
        const suffix = el.getAttribute('data-suffix') || '';
        animateCounter(el, target, suffix);
        counterObserver.unobserve(el);
      }
    });
  }, { threshold: 0.4 });

  document.querySelectorAll('[data-counter]').forEach(el => {
    counterObserver.observe(el);
  });

  // ── FADE-UP ON SCROLL ───────────────────────────
  const fadeObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        fadeObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

  document.querySelectorAll('.fade-up').forEach(el => {
    fadeObserver.observe(el);
  });

  // ── GURU CAROUSEL ───────────────────────────────
  const guruCarousel = document.querySelector('.guru-carousel');
  const prevBtn = document.querySelector('.carousel-btn.prev');
  const nextBtn = document.querySelector('.carousel-btn.next');

  if (guruCarousel) {
    let carouselPos = 0;
    const cardWidth = 220;
    const visibleCards = 5;

    function getMaxPos() {
      const cards = guruCarousel.querySelectorAll('.guru-card');
      const maxOffset = Math.max(0, cards.length - visibleCards);
      return maxOffset * (cardWidth + 20);
    }

    nextBtn?.addEventListener('click', () => {
      carouselPos = Math.min(carouselPos + (cardWidth + 20), getMaxPos());
      guruCarousel.style.transform = `translateX(-${carouselPos}px)`;
    });

    prevBtn?.addEventListener('click', () => {
      carouselPos = Math.max(0, carouselPos - (cardWidth + 20));
      guruCarousel.style.transform = `translateX(-${carouselPos}px)`;
    });

    // Touch/swipe support
    let touchStartX = 0;
    guruCarousel.addEventListener('touchstart', e => {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    guruCarousel.addEventListener('touchend', e => {
      const delta = touchStartX - e.changedTouches[0].screenX;
      if (Math.abs(delta) > 40) {
        if (delta > 0) {
          carouselPos = Math.min(carouselPos + (cardWidth + 20), getMaxPos());
        } else {
          carouselPos = Math.max(0, carouselPos - (cardWidth + 20));
        }
        guruCarousel.style.transform = `translateX(-${carouselPos}px)`;
      }
    }, { passive: true });
  }

  // ── TICKER DUPLIKAT ─────────────────────────────
  const ticker = document.querySelector('.ticker-content');
  if (ticker) {
    ticker.innerHTML += ticker.innerHTML;
  }

  // ── BACK TO TOP ─────────────────────────────────
  const backBtn = document.querySelector('.back-to-top');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
      backBtn?.classList.add('show');
    } else {
      backBtn?.classList.remove('show');
    }
  });
  backBtn?.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // ── ACTIVE NAV LINK ─────────────────────────────
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-link[href]').forEach(link => {
    const href = link.getAttribute('href').split('/').pop();
    if (href === currentPath || (currentPath === '' && href === 'index.html')) {
      link.classList.add('active');
    }
  });

  // ── SMOOTH SCROLL ANCHORS ───────────────────────
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href === '#') return;
      e.preventDefault();
      const target = document.querySelector(href);
      if (target) {
        const headerHeight = document.querySelector('.header')?.offsetHeight || 80;
        window.scrollTo({
          top: target.offsetTop - headerHeight - 16,
          behavior: 'smooth'
        });
      }
    });
  });

  // ── PRELOADER ───────────────────────────────────
  window.addEventListener('load', () => {
    const preloader = document.getElementById('preloader');
    if (preloader) {
      preloader.style.opacity = '0';
      preloader.style.transition = 'opacity 0.5s';
      setTimeout(() => preloader.remove(), 500);
    }
  });

});