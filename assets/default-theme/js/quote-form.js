/**
 * ModusCap - Formulaire de Devis Multi-Étapes
 * Wizard intelligent avec calcul en temps réel
 * Author: Prudence Dieudonné ASSOGBA
 */

(function() {
  'use strict';

  // ============================================
  // DONNÉES & CONFIGURATION
  // ============================================
  
  const PRICING = {
    basePricePerSqm: 1200, // Prix de base par m²
    
    bedrooms: {
      1: 5000,
      2: 8000,
      3: 12000,
      4: 16000,
      5: 20000
    },
    
    bathrooms: {
      1: 8000,
      2: 14000,
      3: 20000
    },
    
    specialRooms: {
      office: 5000,
      pantry: 3000,
      laundry: 4000,
      garage: 15000,
      pool: 50000
    },
    
    styles: {
      moderne: 1.0,
      contemporain: 1.1,
      traditionnel: 1.05,
      ecologique: 1.15
    },
    
    materials: {
      standard: 1.0,
      premium: 1.25,
      luxe: 1.5
    },
    
    finishings: {
      basic: 1.0,
      intermediate: 1.15,
      highEnd: 1.35
    },
    
    ecoOptions: {
      solarPanels: 15000,
      heatPump: 12000,
      rainwater: 5000,
      greenRoof: 8000,
      tripleGlazing: 6000
    }
  };

  // État du formulaire
  let formData = {
    // Étape 1
    houseType: '',
    surface: 80,
    budget: 150000,
    deadline: '',
    
    // Étape 2
    bedrooms: 3,
    bathrooms: 2,
    specialRooms: [],
    style: 'moderne',
    
    // Étape 3
    materials: 'standard',
    interiorFinish: 'intermediate',
    exteriorFinish: 'intermediate',
    ecoOptions: [],
    
    // Étape 4
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    familyStatus: '',
    landType: '',
    landAddress: '',
    constraints: '',
    
    // Métadonnées
    timestamp: null,
    estimatedPrice: 0
  };

  let currentStep = 1;
  const totalSteps = 5;

  // ============================================
  // NAVIGATION WIZARD
  // ============================================
  
  /**
   * Afficher une étape spécifique
   */
  function showStep(stepNumber) {
    // Cacher toutes les étapes
    document.querySelectorAll('.wizard-step-content').forEach(step => {
      step.classList.remove('active');
    });

    // Afficher l'étape demandée
    const targetStep = document.getElementById(`step-${stepNumber}`);
    if (targetStep) {
      targetStep.classList.add('active');
    }

    // Mettre à jour les indicateurs
    updateStepIndicators(stepNumber);

    // Gérer les boutons de navigation
    updateNavigationButtons(stepNumber);

    // Scroll en haut
    window.scrollTo({ top: 0, behavior: 'smooth' });

    currentStep = stepNumber;
    
    // Sauvegarder la progression
    saveProgress();
  }

  /**
   * Mettre à jour les indicateurs d'étape
   */
  function updateStepIndicators(activeStep) {
    document.querySelectorAll('.wizard-step').forEach((step, index) => {
      const stepNum = index + 1;
      
      step.classList.remove('active', 'completed');
      
      if (stepNum === activeStep) {
        step.classList.add('active');
      } else if (stepNum < activeStep) {
        step.classList.add('completed');
        const circle = step.querySelector('.wizard-step-circle');
        if (circle) {
          circle.innerHTML = '✓';
        }
      } else {
        const circle = step.querySelector('.wizard-step-circle');
        if (circle) {
          circle.textContent = stepNum;
        }
      }
    });
  }

  /**
   * Mettre à jour les boutons de navigation
   */
  function updateNavigationButtons(stepNumber) {
    const prevBtn = document.getElementById('prev-step');
    const nextBtn = document.getElementById('next-step');
    const submitBtn = document.getElementById('submit-quote');

    if (prevBtn) {
      prevBtn.style.display = stepNumber === 1 ? 'none' : 'inline-flex';
    }

    if (nextBtn && submitBtn) {
      if (stepNumber === totalSteps) {
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'inline-flex';
      } else {
        nextBtn.style.display = 'inline-flex';
        submitBtn.style.display = 'none';
      }
    }
  }

  /**
   * Aller à l'étape suivante
   */
  function nextStep() {
    if (validateCurrentStep()) {
      if (currentStep < totalSteps) {
        showStep(currentStep + 1);
      }
    }
  }

  /**
   * Aller à l'étape précédente
   */
  function prevStep() {
    if (currentStep > 1) {
      showStep(currentStep - 1);
    }
  }

  // ============================================
  // VALIDATION
  // ============================================
  
  /**
   * Valider l'étape actuelle
   */
  function validateCurrentStep() {
    const currentStepEl = document.getElementById(`step-${currentStep}`);
    if (!currentStepEl) return false;

    const requiredFields = currentStepEl.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
      if (!field.value || field.value.trim() === '') {
        isValid = false;
        field.classList.add('is-invalid');
        
        // Créer message d'erreur si absent
        let feedback = field.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
          feedback = document.createElement('div');
          feedback.className = 'invalid-feedback';
          feedback.textContent = 'Ce champ est requis';
          field.parentNode.insertBefore(feedback, field.nextSibling);
        }
      } else {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
      }
    });

    if (!isValid) {
      showToast('Veuillez remplir tous les champs requis', 'danger');
    }

    return isValid;
  }

  // ============================================
  // CALCUL DU PRIX
  // ============================================
  
  /**
   * Calculer le prix estimé
   */
  function calculatePrice() {
    let totalPrice = 0;

    // Prix de base (surface)
    totalPrice += formData.surface * PRICING.basePricePerSqm;

    // Chambres
    if (formData.bedrooms && PRICING.bedrooms[formData.bedrooms]) {
      totalPrice += PRICING.bedrooms[formData.bedrooms];
    }

    // Salles de bain
    if (formData.bathrooms && PRICING.bathrooms[formData.bathrooms]) {
      totalPrice += PRICING.bathrooms[formData.bathrooms];
    }

    // Pièces spéciales
    formData.specialRooms.forEach(room => {
      if (PRICING.specialRooms[room]) {
        totalPrice += PRICING.specialRooms[room];
      }
    });

    // Multiplicateur de style
    if (formData.style && PRICING.styles[formData.style]) {
      totalPrice *= PRICING.styles[formData.style];
    }

    // Multiplicateur de matériaux
    if (formData.materials && PRICING.materials[formData.materials]) {
      totalPrice *= PRICING.materials[formData.materials];
    }

    // Multiplicateur de finitions
    const avgFinish = (
      (PRICING.finishings[formData.interiorFinish] || 1) +
      (PRICING.finishings[formData.exteriorFinish] || 1)
    ) / 2;
    totalPrice *= avgFinish;

    // Options écologiques
    formData.ecoOptions.forEach(option => {
      if (PRICING.ecoOptions[option]) {
        totalPrice += PRICING.ecoOptions[option];
      }
    });

    formData.estimatedPrice = Math.round(totalPrice);
    updatePriceDisplay();
    
    return formData.estimatedPrice;
  }

  /**
   * Mettre à jour l'affichage du prix
   */
  function updatePriceDisplay() {
    const priceElements = document.querySelectorAll('.estimated-price');
    priceElements.forEach(el => {
      el.textContent = formatPrice(formData.estimatedPrice);
    });

    // Indicateur de budget
    const budgetIndicator = document.getElementById('budget-indicator');
    if (budgetIndicator && formData.budget > 0) {
      const percentage = (formData.estimatedPrice / formData.budget) * 100;
      let status = 'success';
      let message = 'Dans votre budget';
      
      if (percentage > 120) {
        status = 'danger';
        message = 'Au-dessus du budget';
      } else if (percentage > 100) {
        status = 'warning';
        message = 'Légèrement au-dessus du budget';
      }
      
      budgetIndicator.className = `alert alert-${status}`;
      budgetIndicator.textContent = `${message} (${percentage.toFixed(0)}% du budget)`;
    }
  }

  // ============================================
  // GESTION DES DONNÉES
  // ============================================
  
  /**
   * Collecter les données de l'étape 1
   */
  function collectStep1Data() {
    formData.houseType = document.querySelector('input[name="house-type"]:checked')?.value || '';
    formData.surface = parseInt(document.getElementById('surface')?.value) || 80;
    formData.budget = parseInt(document.getElementById('budget')?.value) || 150000;
    formData.deadline = document.getElementById('deadline')?.value || '';
    
    calculatePrice();
  }

  /**
   * Collecter les données de l'étape 2
   */
  function collectStep2Data() {
    formData.bedrooms = parseInt(document.getElementById('bedrooms')?.value) || 3;
    formData.bathrooms = parseInt(document.getElementById('bathrooms')?.value) || 2;
    formData.style = document.getElementById('style')?.value || 'moderne';
    
    // Pièces spéciales (checkboxes)
    formData.specialRooms = [];
    document.querySelectorAll('input[name="special-rooms"]:checked').forEach(checkbox => {
      formData.specialRooms.push(checkbox.value);
    });
    
    calculatePrice();
  }

  /**
   * Collecter les données de l'étape 3
   */
  function collectStep3Data() {
    formData.materials = document.getElementById('materials')?.value || 'standard';
    formData.interiorFinish = document.getElementById('interior-finish')?.value || 'intermediate';
    formData.exteriorFinish = document.getElementById('exterior-finish')?.value || 'intermediate';
    
    // Options écologiques (checkboxes)
    formData.ecoOptions = [];
    document.querySelectorAll('input[name="eco-options"]:checked').forEach(checkbox => {
      formData.ecoOptions.push(checkbox.value);
    });
    
    calculatePrice();
  }

  /**
   * Collecter les données de l'étape 4
   */
  function collectStep4Data() {
    formData.firstName = document.getElementById('first-name')?.value || '';
    formData.lastName = document.getElementById('last-name')?.value || '';
    formData.email = document.getElementById('email')?.value || '';
    formData.phone = document.getElementById('phone')?.value || '';
    formData.familyStatus = document.getElementById('family-status')?.value || '';
    formData.landType = document.getElementById('land-type')?.value || '';
    formData.landAddress = document.getElementById('land-address')?.value || '';
    formData.constraints = document.getElementById('constraints')?.value || '';
  }

  /**
   * Générer le récapitulatif
   */
  function generateSummary() {
    const summaryEl = document.getElementById('quote-summary');
    if (!summaryEl) return;

    const summary = `
      <div class="card mb-3">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Informations du Projet</h5>
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-4">Type de maison :</dt>
            <dd class="col-sm-8">${formData.houseType}</dd>
            
            <dt class="col-sm-4">Surface :</dt>
            <dd class="col-sm-8">${formData.surface} m²</dd>
            
            <dt class="col-sm-4">Budget prévu :</dt>
            <dd class="col-sm-8">${formatPrice(formData.budget)}</dd>
            
            <dt class="col-sm-4">Délai souhaité :</dt>
            <dd class="col-sm-8">${formData.deadline}</dd>
          </dl>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Configuration</h5>
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-4">Chambres :</dt>
            <dd class="col-sm-8">${formData.bedrooms}</dd>
            
            <dt class="col-sm-4">Salles de bain :</dt>
            <dd class="col-sm-8">${formData.bathrooms}</dd>
            
            <dt class="col-sm-4">Style :</dt>
            <dd class="col-sm-8">${formData.style}</dd>
            
            ${formData.specialRooms.length > 0 ? `
              <dt class="col-sm-4">Pièces spéciales :</dt>
              <dd class="col-sm-8">${formData.specialRooms.join(', ')}</dd>
            ` : ''}
          </dl>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Matériaux & Finitions</h5>
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-4">Matériaux :</dt>
            <dd class="col-sm-8">${formData.materials}</dd>
            
            <dt class="col-sm-4">Finitions intérieures :</dt>
            <dd class="col-sm-8">${formData.interiorFinish}</dd>
            
            <dt class="col-sm-4">Finitions extérieures :</dt>
            <dd class="col-sm-8">${formData.exteriorFinish}</dd>
            
            ${formData.ecoOptions.length > 0 ? `
              <dt class="col-sm-4">Options écologiques :</dt>
              <dd class="col-sm-8">${formData.ecoOptions.join(', ')}</dd>
            ` : ''}
          </dl>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Vos Informations</h5>
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-4">Nom complet :</dt>
            <dd class="col-sm-8">${formData.firstName} ${formData.lastName}</dd>
            
            <dt class="col-sm-4">Email :</dt>
            <dd class="col-sm-8">${formData.email}</dd>
            
            <dt class="col-sm-4">Téléphone :</dt>
            <dd class="col-sm-8">${formData.phone}</dd>
            
            <dt class="col-sm-4">Type de terrain :</dt>
            <dd class="col-sm-8">${formData.landType}</dd>
            
            ${formData.landAddress ? `
              <dt class="col-sm-4">Adresse du terrain :</dt>
              <dd class="col-sm-8">${formData.landAddress}</dd>
            ` : ''}
          </dl>
        </div>
      </div>

      <div class="card border-success">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">Prix Estimé</h5>
        </div>
        <div class="card-body text-center">
          <h2 class="text-success mb-0 estimated-price">${formatPrice(formData.estimatedPrice)}</h2>
          <p class="text-muted mt-2">
            <small>Prix indicatif, devis détaillé sur mesure fourni sous 24h</small>
          </p>
        </div>
      </div>
    `;

    summaryEl.innerHTML = summary;
  }

  // ============================================
  // SOUMISSION
  // ============================================
  
  /**
   * Soumettre le formulaire
   */
  function submitQuote() {
    // Collecter toutes les données
    collectStep1Data();
    collectStep2Data();
    collectStep3Data();
    collectStep4Data();
    
    // Timestamp
    formData.timestamp = new Date().toISOString();
    
    // Validation finale
    if (!formData.email || !formData.firstName || !formData.lastName) {
      showToast('Veuillez remplir toutes les informations requises', 'danger');
      return;
    }

    // Afficher le loader
    const submitBtn = document.getElementById('submit-quote');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi en cours...';
    }

    // Simuler l'envoi (dans un vrai projet, envoyer au serveur)
    setTimeout(() => {
      console.log('Devis soumis:', formData);
      
      // Sauvegarder dans le localStorage
      saveToStorage('submitted-quote', formData);
      
      // Afficher message de succès
      showSuccessMessage();
      
      // Réinitialiser le bouton
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Envoyer le devis';
      }
      
      // Nettoyer la sauvegarde automatique
      removeFromStorage('quote-progress');
    }, 2000);
  }

  /**
   * Afficher le message de succès
   */
  function showSuccessMessage() {
    const wizardContainer = document.querySelector('.wizard-container');
    if (!wizardContainer) return;

    wizardContainer.innerHTML = `
      <div class="text-center py-5">
        <div class="mb-4">
          <svg width="80" height="80" fill="currentColor" class="text-success" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
          </svg>
        </div>
        <h2 class="text-success mb-3">Devis envoyé avec succès !</h2>
        <p class="lead mb-4">
          Merci ${formData.firstName} pour votre demande. Notre équipe vous contactera dans les 24h 
          pour finaliser votre projet de maison modulaire.
        </p>
        <p class="mb-4">
          Un email de confirmation a été envoyé à <strong>${formData.email}</strong>
        </p>
        <div class="alert alert-info mb-4">
          <strong>Prix estimé de votre projet : ${formatPrice(formData.estimatedPrice)}</strong>
        </div>
        <div class="d-flex gap-2 justify-content-center">
          <a href="index.html" class="btn btn-primary">Retour à l'accueil</a>
          <a href="produits.html" class="btn btn-outline-primary">Voir nos modèles</a>
          <button class="btn btn-outline-secondary" onclick="window.print()">Imprimer le devis</button>
        </div>
      </div>
    `;

    // Scroll en haut
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // ============================================
  // SAUVEGARDE AUTOMATIQUE
  // ============================================
  
  /**
   * Sauvegarder la progression
   */
  function saveProgress() {
    collectStep1Data();
    collectStep2Data();
    collectStep3Data();
    collectStep4Data();
    
    const progress = {
      currentStep,
      formData,
      savedAt: new Date().toISOString()
    };
    
    saveToStorage('quote-progress', progress);
  }

  /**
   * Restaurer la progression
   */
  function restoreProgress() {
    const progress = getFromStorage('quote-progress');
    
    if (progress && progress.formData) {
      const shouldRestore = confirm(
        'Voulez-vous reprendre votre demande de devis en cours ?'
      );
      
      if (shouldRestore) {
        formData = progress.formData;
        populateFormFields();
        showStep(progress.currentStep || 1);
        calculatePrice();
        showToast('Votre progression a été restaurée', 'success');
      } else {
        removeFromStorage('quote-progress');
      }
    }
  }

  /**
   * Remplir les champs du formulaire avec les données sauvegardées
   */
  function populateFormFields() {
    // Étape 1
    if (formData.houseType) {
      const radio = document.querySelector(`input[name="house-type"][value="${formData.houseType}"]`);
      if (radio) radio.checked = true;
    }
    const surfaceInput = document.getElementById('surface');
    if (surfaceInput) surfaceInput.value = formData.surface;
    
    const budgetInput = document.getElementById('budget');
    if (budgetInput) budgetInput.value = formData.budget;
    
    const deadlineInput = document.getElementById('deadline');
    if (deadlineInput) deadlineInput.value = formData.deadline;
    
    // Étape 2
    const bedroomsInput = document.getElementById('bedrooms');
    if (bedroomsInput) bedroomsInput.value = formData.bedrooms;
    
    const bathroomsInput = document.getElementById('bathrooms');
    if (bathroomsInput) bathroomsInput.value = formData.bathrooms;
    
    const styleInput = document.getElementById('style');
    if (styleInput) styleInput.value = formData.style;
    
    // ... (continuer pour les autres champs)
  }

  // ============================================
  // ÉVÉNEMENTS
  // ============================================
  
  /**
   * Initialiser les événements
   */
  function initEvents() {
    // Boutons de navigation
    const prevBtn = document.getElementById('prev-step');
    if (prevBtn) {
      prevBtn.addEventListener('click', prevStep);
    }

    const nextBtn = document.getElementById('next-step');
    if (nextBtn) {
      nextBtn.addEventListener('click', nextStep);
    }

    const submitBtn = document.getElementById('submit-quote');
    if (submitBtn) {
      submitBtn.addEventListener('click', submitQuote);
    }

    // Sauvegarde automatique à chaque changement
    document.querySelectorAll('input, select, textarea').forEach(field => {
      field.addEventListener('change', debounce(saveProgress, 1000));
    });

    // Recalcul du prix en temps réel
    document.querySelectorAll('#step-1 input, #step-2 input, #step-2 select, #step-3 input, #step-3 select').forEach(field => {
      field.addEventListener('change', () => {
        if (field.closest('#step-1')) collectStep1Data();
        if (field.closest('#step-2')) collectStep2Data();
        if (field.closest('#step-3')) collectStep3Data();
        calculatePrice();
      });
    });

    // Passer à l'étape 5 (récapitulatif)
    const step5 = document.getElementById('step-5');
    if (step5) {
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.target.classList.contains('active')) {
            generateSummary();
          }
        });
      });
      
      observer.observe(step5, { attributes: true, attributeFilter: ['class'] });
    }
  }

  // ============================================
  // INITIALISATION
  // ============================================
  
  /**
   * Initialisation du formulaire de devis
   */
  function init() {
    // Vérifier si on est sur la page devis
    if (!document.getElementById('quote-wizard')) return;

    console.log('Initialisation du formulaire de devis...');

    // Vérifier s'il y a un produit pré-sélectionné dans l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('produit');
    
    if (productId) {
      const product = getFromStorage('quote-product');
      if (product) {
        // Pré-remplir avec les données du produit
        formData.surface = product.surface;
        formData.bedrooms = product.bedrooms;
        formData.bathrooms = product.bathrooms;
        formData.style = product.style;
        
        showToast(`Devis pour ${product.name}`, 'info');
      }
    }

    // Restaurer la progression si disponible
    restoreProgress();

    // Initialiser les événements
    initEvents();

    // Afficher la première étape
    showStep(1);

    // Calcul initial du prix
    calculatePrice();

    console.log('Formulaire de devis prêt!');
  }

  // Initialiser au chargement du DOM
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Exporter pour utilisation externe
  window.quoteForm = {
    nextStep,
    prevStep,
    submitQuote,
    calculatePrice
  };

})();
