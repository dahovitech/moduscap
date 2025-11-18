/**
 * Product Gallery with Image Zoom Functionality
 * Author: Prudence Dieudonné ASSOGBA
 */

document.addEventListener('DOMContentLoaded', function() {
  
  // Elements
  const mainImage = document.getElementById('mainImage');
  const mainImageContainer = document.getElementById('mainImageContainer');
  const thumbnails = document.querySelectorAll('.thumbnail');
  const zoomModal = document.getElementById('imageZoomModal');
  const zoomedImage = document.getElementById('zoomedImage');
  const closeZoom = document.getElementById('closeZoom');
  
  // Thumbnail click - Change main image
  thumbnails.forEach(thumbnail => {
    thumbnail.addEventListener('click', function() {
      const newImageSrc = this.getAttribute('data-image');
      
      // Remove active class from all thumbnails
      thumbnails.forEach(t => t.classList.remove('active'));
      
      // Add active class to clicked thumbnail
      this.classList.add('active');
      
      // Fade out effect
      mainImage.style.opacity = '0';
      
      // Change image after fade out
      setTimeout(() => {
        mainImage.src = newImageSrc;
        mainImage.style.opacity = '1';
      }, 200);
    });
  });
  
  // Main image click - Open zoom modal
  if (mainImageContainer) {
    mainImageContainer.addEventListener('click', function() {
      const currentImageSrc = mainImage.src;
      zoomedImage.src = currentImageSrc;
      zoomModal.classList.add('active');
      document.body.style.overflow = 'hidden'; // Prevent scrolling
    });
  }
  
  // Close zoom modal - Click on close button
  if (closeZoom) {
    closeZoom.addEventListener('click', function(e) {
      e.stopPropagation();
      closeZoomModal();
    });
  }
  
  // Close zoom modal - Click on modal background
  if (zoomModal) {
    zoomModal.addEventListener('click', function(e) {
      if (e.target === zoomModal) {
        closeZoomModal();
      }
    });
  }
  
  // Close zoom modal - Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && zoomModal.classList.contains('active')) {
      closeZoomModal();
    }
  });
  
  // Function to close zoom modal
  function closeZoomModal() {
    zoomModal.classList.remove('active');
    document.body.style.overflow = ''; // Restore scrolling
  }
  
  // Keyboard navigation for thumbnails
  document.addEventListener('keydown', function(e) {
    if (!zoomModal.classList.contains('active')) {
      const activeThumbnail = document.querySelector('.thumbnail.active');
      const thumbnailsArray = Array.from(thumbnails);
      const currentIndex = thumbnailsArray.indexOf(activeThumbnail);
      
      if (e.key === 'ArrowRight' && currentIndex < thumbnails.length - 1) {
        thumbnails[currentIndex + 1].click();
      } else if (e.key === 'ArrowLeft' && currentIndex > 0) {
        thumbnails[currentIndex - 1].click();
      }
    }
  });
  
  // Touch swipe support for mobile
  let touchStartX = 0;
  let touchEndX = 0;
  
  if (mainImageContainer) {
    mainImageContainer.addEventListener('touchstart', function(e) {
      touchStartX = e.changedTouches[0].screenX;
    }, false);
    
    mainImageContainer.addEventListener('touchend', function(e) {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
    }, false);
  }
  
  function handleSwipe() {
    const activeThumbnail = document.querySelector('.thumbnail.active');
    const thumbnailsArray = Array.from(thumbnails);
    const currentIndex = thumbnailsArray.indexOf(activeThumbnail);
    
    // Swipe left - next image
    if (touchEndX < touchStartX - 50 && currentIndex < thumbnails.length - 1) {
      thumbnails[currentIndex + 1].click();
    }
    
    // Swipe right - previous image
    if (touchEndX > touchStartX + 50 && currentIndex > 0) {
      thumbnails[currentIndex - 1].click();
    }
  }
  
  // Lazy loading for thumbnails
  const observerOptions = {
    root: null,
    rootMargin: '50px',
    threshold: 0.1
  };
  
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target.querySelector('img');
        if (img && img.dataset.src) {
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
        }
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);
  
  thumbnails.forEach(thumbnail => {
    imageObserver.observe(thumbnail);
  });
  
});
