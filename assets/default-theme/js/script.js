/**
 * ModusCap - Script Principal
 * Fonctionnalités communes du site
 * Author: Prudence Dieudonné ASSOGBA
 */

(function() {
  'use strict';

  // ============================================
  // NAVIGATION & HEADER
  // ============================================
  
  /**
   * Gestion du header au scroll
   */
  function initHeaderScroll() {
    const header = document.querySelector('.main-header');
    if (!header) return;

    window.addEventListener('scroll', () => {
      if (window.scrollY > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    });
  }

  /**
   * Gestion du menu mobile
   */
  function initMobileMenu() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (!navbarToggler || !navbarCollapse) return;

    // Fermer le menu lors du clic sur un lien
    const navLinks = navbarCollapse.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth < 992) {
          const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
          if (bsCollapse) {
            bsCollapse.hide();
          }
        }
      });
    });
  }

  /**
   * Active link dans la navigation
   */
  function initActiveNavLink() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    navLinks.forEach(link => {
      const linkPath = new URL(link.href).pathname;
      if (currentPath === linkPath || 
          (currentPath === '/' && linkPath.includes('index.html'))) {
        link.classList.add('active');
      }
    });
  }

  // ============================================
  // SMOOTH SCROLL
  // ============================================
  
  /**
   * Smooth scroll pour les ancres
   */
  function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href === '#') return;
        
        e.preventDefault();
        const target = document.querySelector(href);
        
        if (target) {
          const headerOffset = 80;
          const elementPosition = target.getBoundingClientRect().top;
          const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

          window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
          });
        }
      });
    });
  }

  // ============================================
  // LAZY LOADING IMAGES
  // ============================================
  
  /**
   * Lazy loading des images
   */
  function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.classList.add('fade-in');
          observer.unobserve(img);
        }
      });
    });

    images.forEach(img => imageObserver.observe(img));
  }

  // ============================================
  // ANIMATIONS AU SCROLL
  // ============================================
  
  /**
   * Animations d'apparition au scroll
   */
  function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    const animationObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('slide-up');
        }
      });
    }, {
      threshold: 0.1
    });

    animatedElements.forEach(el => animationObserver.observe(el));
  }

  // ============================================
  // VALIDATION FORMULAIRES
  // ============================================
  
  /**
   * Validation des formulaires
   */
  function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
      form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        
        form.classList.add('was-validated');
        
        // Validation personnalisée des champs
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
          validateField(input);
          
          input.addEventListener('blur', () => validateField(input));
          input.addEventListener('input', () => {
            if (input.classList.contains('is-invalid')) {
              validateField(input);
            }
          });
        });
      }, false);
    });
  }

  /**
   * Validation d'un champ individuel
   */
  function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';

    // Validation selon le type
    switch(field.type) {
      case 'email':
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        isValid = emailRegex.test(value);
        errorMessage = 'Veuillez entrer une adresse email valide';
        break;
      
      case 'tel':
        const phoneRegex = /^[0-9\s\+\-\(\)]{10,}$/;
        isValid = phoneRegex.test(value);
        errorMessage = 'Veuillez entrer un numéro de téléphone valide';
        break;
      
      case 'text':
      case 'textarea':
        if (field.hasAttribute('required')) {
          isValid = value.length >= 2;
          errorMessage = 'Ce champ doit contenir au moins 2 caractères';
        }
        break;
    }

    // Application de la validation
    if (field.hasAttribute('required') && !value) {
      isValid = false;
      errorMessage = 'Ce champ est requis';
    }

    if (isValid) {
      field.classList.remove('is-invalid');
      field.classList.add('is-valid');
    } else {
      field.classList.remove('is-valid');
      field.classList.add('is-invalid');
      
      let feedback = field.nextElementSibling;
      if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentNode.insertBefore(feedback, field.nextSibling);
      }
      feedback.textContent = errorMessage;
    }

    return isValid;
  }

  // ============================================
  // MODALES
  // ============================================
  
  /**
   * Initialisation des modales
   */
  function initModals() {
    // Gestion des boutons d'ouverture de modale
    document.querySelectorAll('[data-modal-target]').forEach(button => {
      button.addEventListener('click', function() {
        const modalId = this.dataset.modalTarget;
        const modal = document.querySelector(modalId);
        if (modal) {
          const bsModal = new bootstrap.Modal(modal);
          bsModal.show();
        }
      });
    });
  }

  // ============================================
  // TOOLTIPS & POPOVERS
  // ============================================
  
  /**
   * Initialisation des tooltips Bootstrap
   */
  function initTooltips() {
    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function(tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  /**
   * Initialisation des popovers Bootstrap
   */
  function initPopovers() {
    const popoverTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="popover"]')
    );
    popoverTriggerList.map(function(popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl);
    });
  }

  // ============================================
  // CAROUSEL
  // ============================================
  
  /**
   * Configuration du carousel Bootstrap
   */
  function initCarousel() {
    const carousels = document.querySelectorAll('.carousel');
    carousels.forEach(carousel => {
      new bootstrap.Carousel(carousel, {
        interval: 5000,
        wrap: true,
        keyboard: true
      });
    });
  }

  // ============================================
  // BACK TO TOP BUTTON
  // ============================================
  
  /**
   * Bouton retour en haut
   */
  function initBackToTop() {
    // Créer le bouton s'il n'existe pas
    let backToTopBtn = document.getElementById('back-to-top');
    if (!backToTopBtn) {
      backToTopBtn = document.createElement('button');
      backToTopBtn.id = 'back-to-top';
      backToTopBtn.className = 'btn btn-primary';
      backToTopBtn.innerHTML = '↑';
      backToTopBtn.setAttribute('aria-label', 'Retour en haut');
      backToTopBtn.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: none;
        z-index: 999;
        cursor: pointer;
        font-size: 1.5rem;
        padding: 0;
      `;
      document.body.appendChild(backToTopBtn);
    }

    // Afficher/masquer selon le scroll
    window.addEventListener('scroll', () => {
      if (window.scrollY > 300) {
        backToTopBtn.style.display = 'block';
      } else {
        backToTopBtn.style.display = 'none';
      }
    });

    // Scroll vers le haut au clic
    backToTopBtn.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // ============================================
  // UTILITAIRES
  // ============================================
  
  /**
   * Formatage de prix
   */
  window.formatPrice = function(price) {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR',
      minimumFractionDigits: 0
    }).format(price);
  };

  /**
   * Formatage de nombre
   */
  window.formatNumber = function(num) {
    return new Intl.NumberFormat('fr-FR').format(num);
  };

  /**
   * Debounce function
   */
  window.debounce = function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  };

  /**
   * Afficher un message toast
   */
  window.showToast = function(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
      toast.remove();
    });
  };

  function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
  }

  // ============================================
  // GESTION DU STORAGE
  // ============================================
  
  /**
   * Sauvegarder dans le localStorage
   */
  window.saveToStorage = function(key, data) {
    try {
      localStorage.setItem(key, JSON.stringify(data));
      return true;
    } catch(e) {
      console.error('Erreur de sauvegarde:', e);
      return false;
    }
  };

  /**
   * Récupérer depuis le localStorage
   */
  window.getFromStorage = function(key) {
    try {
      const data = localStorage.getItem(key);
      return data ? JSON.parse(data) : null;
    } catch(e) {
      console.error('Erreur de récupération:', e);
      return null;
    }
  };

  /**
   * Supprimer du localStorage
   */
  window.removeFromStorage = function(key) {
    try {
      localStorage.removeItem(key);
      return true;
    } catch(e) {
      console.error('Erreur de suppression:', e);
      return false;
    }
  };

  // ============================================
  // INITIALISATION
  // ============================================
  
  /**
   * Initialisation au chargement du DOM
   */
  document.addEventListener('DOMContentLoaded', function() {
    console.log('ModusCap Template - Initialisation...');
    
    // Initialiser toutes les fonctionnalités
    initHeaderScroll();
    initMobileMenu();
    initActiveNavLink();
    initSmoothScroll();
    initLazyLoading();
    initScrollAnimations();
    initFormValidation();
    initModals();
    initTooltips();
    initPopovers();
    initCarousel();
    initBackToTop();
    
    console.log('ModusCap Template - Prêt!');
  });

  // ============================================
  // GESTION DES ERREURS GLOBALES
  // ============================================
  
  window.addEventListener('error', function(e) {
    console.error('Erreur détectée:', e.error);
  });

  // ============================================
  // PERFORMANCE MONITORING
  // ============================================
  
  window.addEventListener('load', function() {
    if ('performance' in window) {
      const perfData = window.performance.timing;
      const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
      console.log(`Temps de chargement: ${pageLoadTime}ms`);
    }
  });

})();
