/**
 * ModusCap - Gestion de la Page Produits
 * Filtrage, recherche et tri des produits
 * Author: Prudence Dieudonné ASSOGBA
 */

(function() {
  'use strict';

  // ============================================
  // DONNÉES PRODUITS
  // ============================================
  
  const products = [
    {
      id: 1,
      name: 'Modèle Urban 60',
      price: 75000,
      surface: 60,
      bedrooms: 2,
      bathrooms: 1,
      style: 'moderne',
      description: 'Maison moderne et compacte, idéale pour un couple ou une petite famille.',
      image: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&h=600&fit=crop',
      features: ['Terrasse', 'Isolation renforcée', 'Double vitrage']
    },
    {
      id: 2,
      name: 'Modèle Family 90',
      price: 125000,
      surface: 90,
      bedrooms: 3,
      bathrooms: 2,
      style: 'contemporain',
      description: 'Espace généreux pour toute la famille avec chambres spacieuses.',
      image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop',
      features: ['Garage', 'Jardin', 'Cuisine équipée', 'Buanderie']
    },
    {
      id: 3,
      name: 'Modèle Prestige 120',
      price: 180000,
      surface: 120,
      bedrooms: 4,
      bathrooms: 2,
      style: 'moderne',
      description: 'Maison haut de gamme avec finitions premium et espaces luxueux.',
      image: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop',
      features: ['Suite parentale', 'Dressing', 'Double garage', 'Terrasse panoramique']
    },
    {
      id: 4,
      name: 'Modèle Eco 50',
      price: 60000,
      surface: 50,
      bedrooms: 1,
      bathrooms: 1,
      style: 'moderne',
      description: 'Petit mais efficace, parfait pour un starter ou investissement locatif.',
      image: 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=800&h=600&fit=crop',
      features: ['Panneaux solaires', 'Récupération eau de pluie', 'Isolation écologique']
    },
    {
      id: 5,
      name: 'Modèle Villa 150',
      price: 250000,
      surface: 150,
      bedrooms: 5,
      bathrooms: 3,
      style: 'contemporain',
      description: 'Grande villa familiale avec tout le confort moderne.',
      image: 'https://images.unsplash.com/photo-1600607687644-c7171b42498b?w=800&h=600&fit=crop',
      features: ['Piscine', 'Jardin paysager', 'Bureau', 'Salle de jeux', 'Cave à vin']
    },
    {
      id: 6,
      name: 'Modèle Cottage 80',
      price: 95000,
      surface: 80,
      bedrooms: 3,
      bathrooms: 1,
      style: 'traditionnel',
      description: 'Charme traditionnel avec tout le confort moderne.',
      image: 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&h=600&fit=crop',
      features: ['Cheminée', 'Bardage bois', 'Véranda', 'Cellier']
    },
    {
      id: 7,
      name: 'Modèle Loft 70',
      price: 85000,
      surface: 70,
      bedrooms: 2,
      bathrooms: 1,
      style: 'moderne',
      description: 'Design épuré avec grands espaces ouverts.',
      image: 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=800&h=600&fit=crop',
      features: ['Cuisine ouverte', 'Hauteur sous plafond 3m', 'Baies vitrées']
    },
    {
      id: 8,
      name: 'Modèle Nature 100',
      price: 135000,
      surface: 100,
      bedrooms: 3,
      bathrooms: 2,
      style: 'contemporain',
      description: 'En harmonie avec la nature, matériaux écologiques.',
      image: 'https://images.unsplash.com/photo-1600607686527-6fb886090705?w=800&h=600&fit=crop',
      features: ['Ossature bois', 'Toiture végétalisée', 'Triple vitrage', 'Pompe à chaleur']
    },
    {
      id: 9,
      name: 'Modèle Studio 35',
      price: 45000,
      surface: 35,
      bedrooms: 1,
      bathrooms: 1,
      style: 'moderne',
      description: 'Compact et fonctionnel, parfait pour célibataire.',
      image: 'https://images.unsplash.com/photo-1600566752355-35792bedcfea?w=800&h=600&fit=crop',
      features: ['Studio', 'Kitchenette', 'Salle d\'eau', 'Mezzanine']
    }
  ];

  // ============================================
  // ÉTAT DE L'APPLICATION
  // ============================================
  
  let currentFilters = {
    search: '',
    priceMin: 0,
    priceMax: 300000,
    surfaceMin: 0,
    surfaceMax: 200,
    bedrooms: '',
    style: ''
  };

  let currentSort = 'popularity';
  let filteredProducts = [...products];

  // ============================================
  // RENDU DES PRODUITS
  // ============================================
  
  /**
   * Rendu de la grille de produits
   */
  function renderProducts() {
    const container = document.getElementById('products-grid');
    if (!container) return;

    // Afficher le loader
    container.innerHTML = '<div class="col-12"><div class="loading-spinner"></div></div>';

    // Simuler un léger délai pour l'UX
    setTimeout(() => {
      if (filteredProducts.length === 0) {
        container.innerHTML = `
          <div class="col-12">
            <div class="alert alert-info text-center">
              <h4>Aucun produit trouvé</h4>
              <p>Essayez de modifier vos critères de recherche.</p>
              <button class="btn btn-primary" onclick="resetFilters()">Réinitialiser les filtres</button>
            </div>
          </div>
        `;
        return;
      }

      container.innerHTML = filteredProducts.map(product => `
        <div class="col-md-6 col-lg-4 mb-4 animate-on-scroll">
          <div class="product-card">
            <div style="overflow: hidden;">
              <img src="${product.image}" alt="${product.name}" class="product-card-img">
            </div>
            <div class="product-card-body">
              <h3 class="product-card-title">${product.name}</h3>
              <div class="product-card-specs">
                <span>
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
                  </svg>
                  ${product.surface}m²
                </span>
                <span>
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                  </svg>
                  ${product.bedrooms} ch.
                </span>
                <span>
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0zM9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                  </svg>
                  ${product.bathrooms} sdb
                </span>
              </div>
              <p class="text-secondary">${product.description}</p>
              <div class="product-card-price">
                ${formatPrice(product.price)}
                <small>à partir de</small>
              </div>
              <div class="product-card-actions">
                <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="showQuickPreview(${product.id})" title="Aperçu rapide">
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                  </svg>
                  Aperçu
                </button>
                <button class="btn btn-outline-primary btn-sm flex-fill" onclick="viewProductDetails(${product.id})" title="Voir tous les détails">
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
                    <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                  </svg>
                  Voir détails
                </button>
                <button class="btn btn-success btn-sm flex-fill" onclick="requestQuote(${product.id})" title="Demander un devis">
                  <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                  </svg>
                  Devis
                </button>
              </div>
            </div>
          </div>
        </div>
      `).join('');

      // Réinitialiser les animations
      initScrollAnimations();
      
      // Mettre à jour le compteur
      updateResultsCount();
    }, 300);
  }

  /**
   * Mise à jour du compteur de résultats
   */
  function updateResultsCount() {
    const counter = document.getElementById('results-count');
    if (counter) {
      const count = filteredProducts.length;
      counter.textContent = `${count} modèle${count > 1 ? 's' : ''} disponible${count > 1 ? 's' : ''}`;
    }
  }

  // ============================================
  // FILTRAGE
  // ============================================
  
  /**
   * Application des filtres
   */
  function applyFilters() {
    filteredProducts = products.filter(product => {
      // Recherche textuelle
      if (currentFilters.search) {
        const searchLower = currentFilters.search.toLowerCase();
        const matchName = product.name.toLowerCase().includes(searchLower);
        const matchDescription = product.description.toLowerCase().includes(searchLower);
        const matchStyle = product.style.toLowerCase().includes(searchLower);
        
        if (!matchName && !matchDescription && !matchStyle) {
          return false;
        }
      }

      // Filtre prix
      if (product.price < currentFilters.priceMin || product.price > currentFilters.priceMax) {
        return false;
      }

      // Filtre surface
      if (product.surface < currentFilters.surfaceMin || product.surface > currentFilters.surfaceMax) {
        return false;
      }

      // Filtre chambres
      if (currentFilters.bedrooms && product.bedrooms !== parseInt(currentFilters.bedrooms)) {
        return false;
      }

      // Filtre style
      if (currentFilters.style && product.style !== currentFilters.style) {
        return false;
      }

      return true;
    });

    // Appliquer le tri
    applySorting();
    
    // Sauvegarder les filtres
    saveFiltersToStorage();
    
    // Mettre à jour l'URL
    updateURL();
    
    // Rendu
    renderProducts();
  }

  /**
   * Tri des produits
   */
  function applySorting() {
    switch(currentSort) {
      case 'price-asc':
        filteredProducts.sort((a, b) => a.price - b.price);
        break;
      case 'price-desc':
        filteredProducts.sort((a, b) => b.price - a.price);
        break;
      case 'surface-asc':
        filteredProducts.sort((a, b) => a.surface - b.surface);
        break;
      case 'surface-desc':
        filteredProducts.sort((a, b) => b.surface - a.surface);
        break;
      case 'name':
        filteredProducts.sort((a, b) => a.name.localeCompare(b.name));
        break;
      default: // popularity
        // Garder l'ordre par défaut (par ID)
        filteredProducts.sort((a, b) => a.id - b.id);
    }
  }

  /**
   * Réinitialisation des filtres
   */
  window.resetFilters = function() {
    currentFilters = {
      search: '',
      priceMin: 0,
      priceMax: 300000,
      surfaceMin: 0,
      surfaceMax: 200,
      bedrooms: '',
      style: ''
    };

    // Réinitialiser les inputs
    const searchInput = document.getElementById('search-input');
    if (searchInput) searchInput.value = '';

    const priceMinInput = document.getElementById('price-min');
    if (priceMinInput) priceMinInput.value = 0;

    const priceMaxInput = document.getElementById('price-max');
    if (priceMaxInput) priceMaxInput.value = 300000;

    const surfaceMinInput = document.getElementById('surface-min');
    if (surfaceMinInput) surfaceMinInput.value = 0;

    const surfaceMaxInput = document.getElementById('surface-max');
    if (surfaceMaxInput) surfaceMaxInput.value = 200;

    const bedroomsSelect = document.getElementById('bedrooms-filter');
    if (bedroomsSelect) bedroomsSelect.value = '';

    const styleSelect = document.getElementById('style-filter');
    if (styleSelect) styleSelect.value = '';

    applyFilters();
  };

  // ============================================
  // ÉVÉNEMENTS FILTRES
  // ============================================
  
  /**
   * Initialisation des événements de filtrage
   */
  function initFilterEvents() {
    // Recherche textuelle
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
      searchInput.addEventListener('input', debounce(function(e) {
        currentFilters.search = e.target.value;
        applyFilters();
      }, 500));
    }

    // Prix min/max
    const priceMinInput = document.getElementById('price-min');
    const priceMaxInput = document.getElementById('price-max');
    
    if (priceMinInput) {
      priceMinInput.addEventListener('input', function(e) {
        currentFilters.priceMin = parseInt(e.target.value) || 0;
        updatePriceDisplay();
      });
      priceMinInput.addEventListener('change', applyFilters);
    }

    if (priceMaxInput) {
      priceMaxInput.addEventListener('input', function(e) {
        currentFilters.priceMax = parseInt(e.target.value) || 300000;
        updatePriceDisplay();
      });
      priceMaxInput.addEventListener('change', applyFilters);
    }

    // Surface min/max
    const surfaceMinInput = document.getElementById('surface-min');
    const surfaceMaxInput = document.getElementById('surface-max');
    
    if (surfaceMinInput) {
      surfaceMinInput.addEventListener('input', function(e) {
        currentFilters.surfaceMin = parseInt(e.target.value) || 0;
        updateSurfaceDisplay();
      });
      surfaceMinInput.addEventListener('change', applyFilters);
    }

    if (surfaceMaxInput) {
      surfaceMaxInput.addEventListener('input', function(e) {
        currentFilters.surfaceMax = parseInt(e.target.value) || 200;
        updateSurfaceDisplay();
      });
      surfaceMaxInput.addEventListener('change', applyFilters);
    }

    // Chambres
    const bedroomsSelect = document.getElementById('bedrooms-filter');
    if (bedroomsSelect) {
      bedroomsSelect.addEventListener('change', function(e) {
        currentFilters.bedrooms = e.target.value;
        applyFilters();
      });
    }

    // Style
    const styleSelect = document.getElementById('style-filter');
    if (styleSelect) {
      styleSelect.addEventListener('change', function(e) {
        currentFilters.style = e.target.value;
        applyFilters();
      });
    }

    // Tri
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
      sortSelect.addEventListener('change', function(e) {
        currentSort = e.target.value;
        applyFilters();
      });
    }

    // Bouton reset
    const resetBtn = document.getElementById('reset-filters');
    if (resetBtn) {
      resetBtn.addEventListener('click', resetFilters);
    }
  }

  /**
   * Mise à jour de l'affichage des prix
   */
  function updatePriceDisplay() {
    const display = document.getElementById('price-display');
    if (display) {
      display.textContent = `${formatPrice(currentFilters.priceMin)} - ${formatPrice(currentFilters.priceMax)}`;
    }
  }

  /**
   * Mise à jour de l'affichage des surfaces
   */
  function updateSurfaceDisplay() {
    const display = document.getElementById('surface-display');
    if (display) {
      display.textContent = `${currentFilters.surfaceMin}m² - ${currentFilters.surfaceMax}m²`;
    }
  }

  // ============================================
  // STOCKAGE & URL
  // ============================================
  
  /**
   * Sauvegarder les filtres dans le localStorage
   */
  function saveFiltersToStorage() {
    saveToStorage('product-filters', currentFilters);
    saveToStorage('product-sort', currentSort);
  }

  /**
   * Charger les filtres depuis le localStorage
   */
  function loadFiltersFromStorage() {
    const savedFilters = getFromStorage('product-filters');
    const savedSort = getFromStorage('product-sort');
    
    if (savedFilters) {
      currentFilters = savedFilters;
    }
    
    if (savedSort) {
      currentSort = savedSort;
    }
  }

  /**
   * Mettre à jour l'URL avec les filtres
   */
  function updateURL() {
    const params = new URLSearchParams();
    
    if (currentFilters.search) params.set('q', currentFilters.search);
    if (currentFilters.priceMin > 0) params.set('prix_min', currentFilters.priceMin);
    if (currentFilters.priceMax < 300000) params.set('prix_max', currentFilters.priceMax);
    if (currentFilters.surfaceMin > 0) params.set('surface_min', currentFilters.surfaceMin);
    if (currentFilters.surfaceMax < 200) params.set('surface_max', currentFilters.surfaceMax);
    if (currentFilters.bedrooms) params.set('chambres', currentFilters.bedrooms);
    if (currentFilters.style) params.set('style', currentFilters.style);
    if (currentSort !== 'popularity') params.set('tri', currentSort);
    
    const newURL = params.toString() ? `?${params.toString()}` : window.location.pathname;
    window.history.replaceState({}, '', newURL);
  }

  /**
   * Charger les filtres depuis l'URL
   */
  function loadFiltersFromURL() {
    const params = new URLSearchParams(window.location.search);
    
    if (params.has('q')) currentFilters.search = params.get('q');
    if (params.has('prix_min')) currentFilters.priceMin = parseInt(params.get('prix_min'));
    if (params.has('prix_max')) currentFilters.priceMax = parseInt(params.get('prix_max'));
    if (params.has('surface_min')) currentFilters.surfaceMin = parseInt(params.get('surface_min'));
    if (params.has('surface_max')) currentFilters.surfaceMax = parseInt(params.get('surface_max'));
    if (params.has('chambres')) currentFilters.bedrooms = params.get('chambres');
    if (params.has('style')) currentFilters.style = params.get('style');
    if (params.has('tri')) currentSort = params.get('tri');
  }

  // ============================================
  // ACTIONS PRODUITS
  // ============================================
  
  /**
   * Voir les détails d'un produit (redirige vers la page détail)
   */
  window.viewProductDetails = function(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;
    
    // Sauvegarder le produit sélectionné
    saveToStorage('selected-product', product);
    
    // Rediriger vers la page de détails
    window.location.href = 'detail-produit.html?id=' + productId;
  };

  /**
   * Afficher un aperçu rapide du produit (modale)
   */
  window.showQuickPreview = function(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;
    
    // Afficher dans une modale de preview
    showProductModal(product);
  };

  /**
   * Demander un devis pour un produit
   */
  window.requestQuote = function(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;
    
    // Sauvegarder le produit pour le formulaire de devis
    saveToStorage('quote-product', product);
    
    // Rediriger vers la page de devis
    window.location.href = 'devis.html?produit=' + productId;
  };

  /**
   * Afficher la modale d'aperçu rapide du produit
   */
  function showProductModal(product) {
    const modalHTML = `
      <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-0">
              <h5 class="modal-title fw-bold" id="productModalLabel">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                </svg>
                Aperçu Rapide - ${product.name}
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-0">
              <div class="row g-0">
                <!-- Image du produit -->
                <div class="col-md-6">
                  <img src="${product.image}" alt="${product.name}" class="img-fluid w-100 h-100" style="object-fit: cover; max-height: 400px;">
                </div>
                
                <!-- Informations du produit -->
                <div class="col-md-6 p-4">
                  <div class="mb-3">
                    <span class="badge bg-primary mb-2">${product.style}</span>
                    <h4 class="fw-bold mb-3">${product.name}</h4>
                    <p class="text-muted">${product.description}</p>
                  </div>
                  
                  <!-- Caractéristiques principales -->
                  <div class="mb-3">
                    <h6 class="fw-bold mb-3">Caractéristiques principales</h6>
                    <div class="row g-2">
                      <div class="col-6">
                        <div class="d-flex align-items-center p-2 bg-light rounded">
                          <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16" class="me-2 text-primary">
                            <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
                          </svg>
                          <div>
                            <small class="text-muted d-block">Surface</small>
                            <strong>${product.surface}m²</strong>
                          </div>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="d-flex align-items-center p-2 bg-light rounded">
                          <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16" class="me-2 text-primary">
                            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                          </svg>
                          <div>
                            <small class="text-muted d-block">Chambres</small>
                            <strong>${product.bedrooms}</strong>
                          </div>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="d-flex align-items-center p-2 bg-light rounded">
                          <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16" class="me-2 text-primary">
                            <path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0zM9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                          </svg>
                          <div>
                            <small class="text-muted d-block">Salles de bain</small>
                            <strong>${product.bathrooms}</strong>
                          </div>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="d-flex align-items-center p-2 bg-light rounded">
                          <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16" class="me-2 text-primary">
                            <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                          </svg>
                          <div>
                            <small class="text-muted d-block">Style</small>
                            <strong class="text-capitalize">${product.style}</strong>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Équipements -->
                  <div class="mb-3">
                    <h6 class="fw-bold mb-2">Équipements inclus</h6>
                    <div class="d-flex flex-wrap gap-2">
                      ${product.features.map(f => `
                        <span class="badge bg-success bg-opacity-10 text-success border border-success">
                          <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                          </svg>
                          ${f}
                        </span>
                      `).join('')}
                    </div>
                  </div>
                  
                  <!-- Prix -->
                  <div class="alert alert-primary d-flex align-items-center justify-content-between mb-3">
                    <div>
                      <small class="text-muted d-block">Prix à partir de</small>
                      <h4 class="fw-bold mb-0 text-primary">${formatPrice(product.price)}</h4>
                    </div>
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 16 16" class="text-primary opacity-25">
                      <path d="M0 3a2 2 0 0 1 2-2h13.5a.5.5 0 0 1 0 1H15v2a1 1 0 0 1 1 1v8.5a1.5 1.5 0 0 1-1.5 1.5h-12A2.5 2.5 0 0 1 0 12.5V3zm1 1.732V12.5A1.5 1.5 0 0 0 2.5 14h12a.5.5 0 0 0 .5-.5V5H2a1.99 1.99 0 0 1-1-.268zM1 3a1 1 0 0 0 1 1h12V2H2a1 1 0 0 0-1 1z"/>
                    </svg>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer bg-light border-0">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                  <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                </svg>
                Fermer
              </button>
              <button type="button" class="btn btn-primary" onclick="viewProductDetails(${product.id}); bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                  <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
                  <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                </svg>
                Voir tous les détails
              </button>
              <button type="button" class="btn btn-success" onclick="requestQuote(${product.id}); bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Demander un devis
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
    
    // Retirer l'ancienne modale si elle existe
    const oldModal = document.getElementById('productModal');
    if (oldModal) oldModal.remove();
    
    // Ajouter la nouvelle modale
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Afficher la modale
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    modal.show();
  }

  // ============================================
  // INITIALISATION
  // ============================================
  
  /**
   * Initialisation de la page produits
   */
  function init() {
    // Vérifier si on est sur la page produits
    if (!document.getElementById('products-grid')) return;

    console.log('Initialisation de la page produits...');

    // Charger les filtres depuis l'URL en priorité
    loadFiltersFromURL();
    
    // Si pas de filtres URL, charger depuis le storage
    if (!window.location.search) {
      loadFiltersFromStorage();
    }

    // Initialiser les événements
    initFilterEvents();

    // Afficher initial
    updatePriceDisplay();
    updateSurfaceDisplay();
    applyFilters();

    console.log('Page produits prête!');
  }

  // Initialiser au chargement du DOM
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
