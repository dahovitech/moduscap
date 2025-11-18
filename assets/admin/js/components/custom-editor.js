/**
 * CustomEditor - Éditeur de texte personnalisé avec intégration média
 * Version 2.0 - Avec redimensionnement d'images et améliorations majeures
 * Transforme les champs TextareaType en éditeur riche
 */
(function($) {
    'use strict';

    class CustomEditor {
        constructor(element, options = {}) {
            this.element = $(element);
            this.options = $.extend({
                height: 300,
                placeholder: 'Tapez votre contenu ici...',
                enableMedia: true,
                enableFormatting: true,
                enableLinks: true,
                enableAutoSave: false,
                autoSaveInterval: 30000, // 30 secondes
                enableFullscreen: true,
                enableWordCount: true,
                enableSourceMode: false,
                enableImageResize: true, // NOUVELLE FONCTIONNALITÉ
                enableDragDrop: true,    // NOUVELLE FONCTIONNALITÉ
                maxImageWidth: 800,      // NOUVELLE OPTION
                minImageWidth: 50,       // NOUVELLE OPTION
                toolbar: [
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'h1', 'h2', 'h3', 'paragraph', '|',
                    'link', 'unlink', '|',
                    'image', 'media', '|',
                    'unorderedList', 'orderedList', '|',
                    'blockquote', 'code', '|',
                    'removeFormat', 'undo', 'redo', '|',
                    'fullscreen', 'source', 'wordcount'
                ],
                onChange: null,
                onAutoSave: null,
                onImageResize: null     // NOUVEAU CALLBACK
            }, options);

            this.editorId = 'editor_' + Date.now();
            this.content = this.element.val() || '';
            this.mediaPicker = null;
            this.isFullscreen = false;
            this.isSourceMode = false;
            this.autoSaveTimer = null;
            this.originalContent = this.content;
            this.wordCount = 0;
            this.selectedImage = null;      // NOUVEAU: pour le redimensionnement
            this.resizeHandle = null;       // NOUVEAU: handle de redimensionnement
            this.isResizing = false;        // NOUVEAU: état de redimensionnement
            this.changeDebouncer = null;    // NOUVEAU: debouncing des changements

            this.init();
        }

        init() {
            this.injectStyles();           // NOUVEAU: injection unique des styles
            this.createEditor();
            this.bindEvents();
            this.setupImageResize();       // NOUVEAU: configuration du redimensionnement
            this.setupDragDrop();          // NOUVEAU: configuration du drag & drop
            this.hideOriginalTextarea();
            this.setupAutoSave();
        }

        /**
         * NOUVEAU: Injection centralisée de tous les styles CSS
         */
        injectStyles() {
            if (!$('#custom-editor-master-styles').length) {
                $('<style id="custom-editor-master-styles">').text(`
                    /* Styles de base de l'éditeur */
                    .custom-editor {
                        position: relative;
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
                    }
                    
                    .editor-content.empty:before {
                        content: attr(data-placeholder);
                        color: #6c757d;
                        pointer-events: none;
                    }
                    
                    /* Mode plein écran */
                    .editor-fullscreen {
                        position: fixed !important;
                        top: 0 !important;
                        left: 0 !important;
                        width: 100vw !important;
                        height: 100vh !important;
                        z-index: 9999 !important;
                        background: white !important;
                        border: none !important;
                    }
                    .editor-fullscreen .editor-content {
                        height: calc(100vh - 60px) !important;
                    }
                    [data-bs-theme="dark"] .editor-fullscreen {
                        background: var(--bs-dark) !important;
                    }
                    
                    /* NOUVEAU: Styles pour le redimensionnement d'images */
                    .editor-content img {
                        position: relative;
                        cursor: pointer;
                        transition: box-shadow 0.2s ease;
                    }
                    
                    .editor-content img.selected {
                        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
                        outline: 2px solid #0d6efd;
                    }
                    
                    .image-resize-handle {
                        position: absolute;
                        width: 12px;
                        height: 12px;
                        background: #0d6efd;
                        border: 2px solid white;
                        border-radius: 50%;
                        cursor: nw-resize;
                        z-index: 1000;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                    }
                    
                    .image-resize-handle.se {
                        cursor: se-resize;
                    }
                    
                    .image-resize-tooltip {
                        position: absolute;
                        background: rgba(0,0,0,0.8);
                        color: white;
                        padding: 4px 8px;
                        border-radius: 4px;
                        font-size: 12px;
                        pointer-events: none;
                        z-index: 1001;
                        white-space: nowrap;
                    }
                    
                    /* NOUVEAU: Styles pour le drag & drop */
                    .editor-content.drag-over {
                        border-color: #0d6efd !important;
                        background-color: rgba(13, 110, 253, 0.05);
                    }
                    
                    .drag-overlay {
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(13, 110, 253, 0.1);
                        border: 2px dashed #0d6efd;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 100;
                        font-size: 18px;
                        color: #0d6efd;
                        font-weight: 500;
                    }
                    
                    /* Amélioration des modales d'insertion */
                    .image-insert-modal {
                        position: fixed;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        background: white;
                        border-radius: 8px;
                        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                        z-index: 10000;
                        min-width: 400px;
                        max-width: 600px;
                    }
                    
                    [data-bs-theme="dark"] .image-insert-modal {
                        background: var(--bs-dark);
                        color: var(--bs-light);
                    }
                    
                    .modal-backdrop-custom {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.5);
                        z-index: 9999;
                    }
                    
                    /* Auto-save indicator improvements */
                    .auto-save-indicator {
                        animation: fadeInOut 2s ease-in-out;
                    }
                    
                    @keyframes fadeInOut {
                        0%, 100% { opacity: 0; }
                        50% { opacity: 1; }
                    }
                `).appendTo('head');
            }
        }

        createEditor() {
            const toolbar = this.createToolbar();
            const editor = $(`
                <div class="custom-editor" id="${this.editorId}">
                    ${toolbar}
                    <div class="editor-content" contenteditable="true" 
                         style="min-height: ${this.options.height}px; padding: 15px; border: 1px solid #dee2e6; border-top: none; outline: none; overflow-y: auto;"
                         placeholder="${this.options.placeholder}">
                        ${this.content}
                    </div>
                </div>
            `);

            this.element.after(editor);
            this.editorElement = editor;
            this.contentElement = editor.find('.editor-content');

            // Initialiser le MediaPicker si activé
            if (this.options.enableMedia) {
                this.initMediaPicker();
            }
        }

        createToolbar() {
            const toolbar = $('<div class="editor-toolbar" style="border: 1px solid #dee2e6; background: #f8f9fa; padding: 8px; display: flex; flex-wrap: wrap; gap: 4px; align-items: center;"></div>');

            this.options.toolbar.forEach(item => {
                if (item === '|') {
                    toolbar.append('<div class="toolbar-separator" style="width: 1px; height: 24px; background: #dee2e6; margin: 0 4px;"></div>');
                } else {
                    const button = this.createToolbarButton(item);
                    toolbar.append(button);
                }
            });

            return toolbar.prop('outerHTML');
        }

        createToolbarButton(type) {
            const buttons = {
                bold: { icon: 'bi-type-bold', title: 'Gras', command: 'bold' },
                italic: { icon: 'bi-type-italic', title: 'Italique', command: 'italic' },
                underline: { icon: 'bi-type-underline', title: 'Souligné', command: 'underline' },
                strikethrough: { icon: 'bi-type-strikethrough', title: 'Barré', command: 'strikeThrough' },
                h1: { icon: 'bi-type-h1', title: 'Titre 1', command: 'formatBlock', value: 'h1' },
                h2: { icon: 'bi-type-h2', title: 'Titre 2', command: 'formatBlock', value: 'h2' },
                h3: { icon: 'bi-type-h3', title: 'Titre 3', command: 'formatBlock', value: 'h3' },
                paragraph: { icon: 'bi-paragraph', title: 'Paragraphe', command: 'formatBlock', value: 'p' },
                link: { icon: 'bi-link', title: 'Lien', command: 'createLink' },
                unlink: { icon: 'bi-link-45deg', title: 'Supprimer le lien', command: 'unlink' },
                unorderedList: { icon: 'bi-list-ul', title: 'Liste à puces', command: 'insertUnorderedList' },
                orderedList: { icon: 'bi-list-ol', title: 'Liste numérotée', command: 'insertOrderedList' },
                blockquote: { icon: 'bi-quote', title: 'Citation', command: 'formatBlock', value: 'blockquote' },
                code: { icon: 'bi-code', title: 'Code', command: 'formatBlock', value: 'pre' },
                removeFormat: { icon: 'bi-eraser', title: 'Supprimer le formatage', command: 'removeFormat' },
                undo: { icon: 'bi-arrow-counterclockwise', title: 'Annuler', command: 'undo' },
                redo: { icon: 'bi-arrow-clockwise', title: 'Refaire', command: 'redo' },
                image: { icon: 'bi-image', title: 'Insérer une image', command: 'insertImage' },
                media: { icon: 'bi-collection', title: 'Insérer un média', command: 'insertMedia' },
                fullscreen: { icon: 'bi-arrows-fullscreen', title: 'Plein écran', command: 'toggleFullscreen' },
                source: { icon: 'bi-code-slash', title: 'Code source', command: 'toggleSource' },
                wordcount: { icon: 'bi-123', title: 'Compteur de mots', command: 'showWordCount' }
            };

            const config = buttons[type];
            if (!config) return $('<span></span>');

            return $(`
                <button type="button" class="btn btn-sm btn-outline-secondary toolbar-btn" 
                        data-command="${config.command}" 
                        ${config.value ? `data-value="${config.value}"` : ''}
                        title="${config.title}">
                    <i class="bi ${config.icon}"></i>
                </button>
            `);
        }

        hideOriginalTextarea() {
            this.element.hide();
        }

        bindEvents() {
            const editor = this.editorElement;
            const content = this.contentElement;

            // Événements de la barre d'outils
            editor.find('.toolbar-btn').on('click', (e) => {
                e.preventDefault();
                this.executeCommand($(e.currentTarget));
            });

            // AMÉLIORATION: Événements du contenu avec debouncing
            content.on('input', () => {
                // Débouncing pour les performances
                clearTimeout(this.changeDebouncer);
                this.changeDebouncer = setTimeout(() => {
                    this.updateOriginalTextarea();
                    this.updateWordCount();
                    if (this.options.onChange) {
                        this.options.onChange(this.getContent());
                    }
                }, 300); // 300ms de délai
            });

            content.on('paste', (e) => {
                // AMÉLIORATION: Gestion améliorée du collage
                setTimeout(() => {
                    this.cleanPastedContent();
                    this.updateOriginalTextarea();
                    this.setupImageResize(); // Re-setup pour les nouvelles images
                }, 10);
            });

            // Placeholder avec gestion d'erreur
            content.on('focus blur input', () => {
                try {
                    this.updatePlaceholder();
                } catch (error) {
                    console.warn('Erreur lors de la mise à jour du placeholder:', error);
                }
            });

            // AMÉLIORATION: Raccourcis clavier
            content.on('keydown', (e) => {
                // Tab pour indentation
                if (e.key === 'Tab') {
                    e.preventDefault();
                    if (document.execCommand) {
                        document.execCommand('insertText', false, '    ');
                    } else {
                        // Fallback moderne
                        const selection = window.getSelection();
                        if (selection.rangeCount > 0) {
                            const range = selection.getRangeAt(0);
                            range.deleteContents();
                            range.insertNode(document.createTextNode('    '));
                            range.collapse(false);
                        }
                    }
                }
                
                // NOUVEAU: Raccourcis clavier avancés
                if (e.ctrlKey || e.metaKey) {
                    switch (e.key) {
                        case 's':
                            e.preventDefault();
                            this.autoSave();
                            this.showSuccess('Contenu sauvegardé manuellement');
                            break;
                        case 'f':
                            if (e.shiftKey) {
                                e.preventDefault();
                                this.toggleFullscreen();
                            }
                            break;
                        case 'u':
                            if (e.shiftKey) {
                                e.preventDefault();
                                this.toggleSourceMode();
                            }
                            break;
                    }
                }
                
                // Escape pour désélectionner les images
                if (e.key === 'Escape') {
                    this.deselectImage();
                }
            });

            // NOUVEAU: Gestion du redimensionnement de la fenêtre
            $(window).on('resize', () => {
                if (this.selectedImage) {
                    this.updateResizeHandles();
                }
            });

            this.updatePlaceholder();
        }

        executeCommand(button) {
            const command = button.data('command');
            const value = button.data('value');

            this.contentElement.focus();

            switch (command) {
                case 'createLink':
                    this.insertLink();
                    break;
                case 'insertImage':
                    this.insertImageDialog();
                    break;
                case 'insertMedia':
                    this.insertMediaDialog();
                    break;
                case 'toggleFullscreen':
                    this.toggleFullscreen();
                    break;
                case 'toggleSource':
                    this.toggleSourceMode();
                    break;
                case 'showWordCount':
                    this.showWordCount();
                    break;
                default:
                    if (value) {
                        document.execCommand(command, false, value);
                    } else {
                        document.execCommand(command, false, null);
                    }
                    break;
            }

            this.updateOriginalTextarea();
        }

        insertLink() {
            const selection = window.getSelection();
            const selectedText = selection.toString();
            const url = prompt('Entrez l\'URL du lien:', 'https://');
            
            if (url && url !== 'https://') {
                if (selectedText) {
                    document.execCommand('createLink', false, url);
                } else {
                    const linkText = prompt('Texte du lien:', url);
                    if (linkText) {
                        document.execCommand('insertHTML', false, `<a href="${url}">${linkText}</a>`);
                    }
                }
            }
        }

        /**
         * AMÉLIORATION: Dialog moderne d'insertion d'image avec prévisualisation
         */
        insertImageDialog() {
            const modal = this.createImageInsertModal();
            $('body').append(modal);
            
            // Focus sur l'input URL
            modal.find('input[name="imageUrl"]').focus();
            
            // Gestion de la fermeture
            modal.find('.btn-close, .btn-cancel, .modal-backdrop-custom').on('click', () => {
                modal.remove();
            });
            
            // Gestion de l'insertion
            modal.find('.btn-insert').on('click', () => {
                this.insertImageFromModal(modal);
            });
            
            // Prévisualisation en temps réel
            modal.find('input[name="imageUrl"]').on('input', (e) => {
                this.previewImageInModal(modal, $(e.target).val());
            });
            
            // Insertion sur Entrée
            modal.find('input').on('keypress', (e) => {
                if (e.which === 13) {
                    this.insertImageFromModal(modal);
                }
            });
        }
        
        /**
         * NOUVEAU: Création du modal d'insertion d'image
         */
        createImageInsertModal() {
            return $(`
                <div class="modal-backdrop-custom">
                    <div class="image-insert-modal">
                        <div class="modal-header p-3 border-bottom">
                            <h5 class="modal-title">Insérer une image</h5>
                            <button type="button" class="btn-close"></button>
                        </div>
                        <div class="modal-body p-3">
                            <div class="mb-3">
                                <label class="form-label">URL de l'image</label>
                                <input type="url" name="imageUrl" class="form-control" 
                                       placeholder="https://exemple.com/image.jpg" required>
                                <div class="form-text">Entrez l'URL complète de l'image</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Texte alternatif (optionnel)</label>
                                <input type="text" name="imageAlt" class="form-control" 
                                       placeholder="Description de l'image">
                                <div class="form-text">Important pour l'accessibilité</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Largeur maximale</label>
                                <select name="imageWidth" class="form-select">
                                    <option value="">Taille originale</option>
                                    <option value="200">200px</option>
                                    <option value="400">400px</option>
                                    <option value="600" selected>600px</option>
                                    <option value="800">800px</option>
                                    <option value="100%">Largeur complète</option>
                                </select>
                            </div>
                            <div class="image-preview-container d-none">
                                <label class="form-label">Prévisualisation</label>
                                <div class="border rounded p-2 text-center">
                                    <img class="image-preview" style="max-width: 200px; max-height: 150px;" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer p-3 border-top">
                            <button type="button" class="btn btn-secondary btn-cancel">Annuler</button>
                            <button type="button" class="btn btn-primary btn-insert" disabled>Insérer</button>
                        </div>
                    </div>
                </div>
            `);
        }
        
        /**
         * NOUVEAU: Prévisualisation d'image dans le modal
         */
        previewImageInModal(modal, url) {
            const previewContainer = modal.find('.image-preview-container');
            const preview = modal.find('.image-preview');
            const insertBtn = modal.find('.btn-insert');
            
            if (!url || !this.isValidImageUrl(url)) {
                previewContainer.addClass('d-none');
                insertBtn.prop('disabled', true);
                return;
            }
            
            const img = new Image();
            img.onload = () => {
                preview.attr('src', url);
                previewContainer.removeClass('d-none');
                insertBtn.prop('disabled', false);
            };
            img.onerror = () => {
                previewContainer.addClass('d-none');
                insertBtn.prop('disabled', true);
            };
            img.src = url;
        }
        
        /**
         * NOUVEAU: Validation d'URL d'image
         */
        isValidImageUrl(url) {
            try {
                new URL(url);
                return /\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i.test(url);
            } catch {
                return false;
            }
        }
        
        /**
         * NOUVEAU: Insertion d'image depuis le modal
         */
        insertImageFromModal(modal) {
            const url = modal.find('input[name="imageUrl"]').val().trim();
            const alt = modal.find('input[name="imageAlt"]').val().trim();
            const width = modal.find('select[name="imageWidth"]').val();
            
            if (!url || !this.isValidImageUrl(url)) {
                this.showError('URL d\'image invalide');
                return;
            }
            
            let style = 'max-width: 100%; height: auto; margin: 10px 0;';
            if (width && width !== '') {
                if (width.includes('%')) {
                    style = `width: ${width}; height: auto; margin: 10px 0;`;
                } else {
                    style = `max-width: ${width}px; width: 100%; height: auto; margin: 10px 0;`;
                }
            }
            
            const imageHtml = `<img src="${this.escapeHtml(url)}" 
                                   alt="${this.escapeHtml(alt)}" 
                                   style="${style}"
                                   data-resizable="true">`;
            
            // Utilisation de l'API moderne au lieu de execCommand
            if (document.getSelection && document.getSelection().rangeCount > 0) {
                const selection = document.getSelection();
                const range = selection.getRangeAt(0);
                range.deleteContents();
                const div = document.createElement('div');
                div.innerHTML = imageHtml;
                range.insertNode(div.firstChild);
            } else {
                // Fallback
                this.contentElement.append(imageHtml);
            }
            
            this.updateOriginalTextarea();
            this.setupImageResize(); // Re-setup pour la nouvelle image
            modal.remove();
            
            if (this.options.onImageResize) {
                this.options.onImageResize('inserted', url);
            }
        }
        
        /**
         * NOUVEAU: Échappement HTML pour la sécurité
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        /**
         * ✨ FONCTIONNALITÉ PRINCIPALE: Configuration du redimensionnement d'images
         */
        setupImageResize() {
            if (!this.options.enableImageResize) return;
            
            // Supprimer les anciens event listeners
            this.contentElement.off('click.imageResize');
            this.contentElement.off('mousedown.imageResize');
            
            // Click sur les images pour les sélectionner
            this.contentElement.on('click.imageResize', 'img', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.selectImage($(e.target));
            });
            
            // Clic en dehors pour désélectionner
            this.contentElement.on('click.imageResize', (e) => {
                if (!$(e.target).is('img') && !$(e.target).hasClass('image-resize-handle')) {
                    this.deselectImage();
                }
            });
            
            // Double-clic pour éditer l'image
            this.contentElement.on('dblclick.imageResize', 'img', (e) => {
                e.preventDefault();
                this.editImageProperties($(e.target));
            });
        }
        
        /**
         * NOUVEAU: Sélection d'une image pour redimensionnement
         */
        selectImage(img) {
            this.deselectImage(); // Désélectionner l'ancienne
            
            this.selectedImage = img;
            img.addClass('selected');
            
            // Créer les handles de redimensionnement
            this.createResizeHandles(img);
            
            // Afficher les informations de l'image
            this.showImageTooltip(img);
        }
        
        /**
         * NOUVEAU: Désélection de l'image
         */
        deselectImage() {
            if (this.selectedImage) {
                this.selectedImage.removeClass('selected');
                this.selectedImage = null;
            }
            
            // Supprimer les handles et tooltips
            $('.image-resize-handle, .image-resize-tooltip').remove();
        }
        
        /**
         * NOUVEAU: Création des handles de redimensionnement
         */
        createResizeHandles(img) {
            const imgPos = img.position();
            const imgWidth = img.outerWidth();
            const imgHeight = img.outerHeight();
            
            // Handle sud-est (principal)
            const handleSE = $(`
                <div class="image-resize-handle se" 
                     style="left: ${imgPos.left + imgWidth - 6}px; 
                            top: ${imgPos.top + imgHeight - 6}px;">
                </div>
            `);
            
            this.contentElement.append(handleSE);
            
            // Handle nord-ouest (secondaire)
            const handleNW = $(`
                <div class="image-resize-handle nw" 
                     style="left: ${imgPos.left - 6}px; 
                            top: ${imgPos.top - 6}px;">
                </div>
            `);
            
            this.contentElement.append(handleNW);
            
            // Événements de redimensionnement
            this.bindResizeEvents(handleSE, 'se');
            this.bindResizeEvents(handleNW, 'nw');
        }
        
        /**
         * NOUVEAU: Liaison des événements de redimensionnement
         */
        bindResizeEvents(handle, direction) {
            handle.on('mousedown', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                this.isResizing = true;
                const img = this.selectedImage;
                const startX = e.clientX;
                const startY = e.clientY;
                const startWidth = img.width();
                const startHeight = img.height();
                const aspectRatio = startWidth / startHeight;
                
                const mouseMoveHandler = (e) => {
                    if (!this.isResizing) return;
                    
                    let newWidth, newHeight;
                    
                    if (direction === 'se') {
                        newWidth = startWidth + (e.clientX - startX);
                    } else { // direction === 'nw'
                        newWidth = startWidth - (e.clientX - startX);
                    }
                    
                    // Respecter les limites
                    newWidth = Math.max(this.options.minImageWidth, 
                              Math.min(this.options.maxImageWidth, newWidth));
                    
                    // Maintenir les proportions
                    newHeight = newWidth / aspectRatio;
                    
                    // Appliquer les nouvelles dimensions
                    img.css({
                        width: newWidth + 'px',
                        height: newHeight + 'px'
                    });
                    
                    // Mettre à jour les handles
                    this.updateResizeHandles();
                    
                    // Mettre à jour le tooltip
                    this.updateImageTooltip(newWidth, newHeight);
                };
                
                const mouseUpHandler = () => {
                    this.isResizing = false;
                    $(document).off('mousemove', mouseMoveHandler);
                    $(document).off('mouseup', mouseUpHandler);
                    
                    this.updateOriginalTextarea();
                    
                    // Callback de redimensionnement
                    if (this.options.onImageResize) {
                        this.options.onImageResize('resized', img.attr('src'), {
                            width: img.width(),
                            height: img.height()
                        });
                    }
                };
                
                $(document).on('mousemove', mouseMoveHandler);
                $(document).on('mouseup', mouseUpHandler);
            });
        }
        
        /**
         * NOUVEAU: Mise à jour des handles de redimensionnement
         */
        updateResizeHandles() {
            if (!this.selectedImage) return;
            
            const img = this.selectedImage;
            const imgPos = img.position();
            const imgWidth = img.outerWidth();
            const imgHeight = img.outerHeight();
            
            $('.image-resize-handle.se').css({
                left: imgPos.left + imgWidth - 6,
                top: imgPos.top + imgHeight - 6
            });
            
            $('.image-resize-handle.nw').css({
                left: imgPos.left - 6,
                top: imgPos.top - 6
            });
        }
        
        /**
         * NOUVEAU: Affichage du tooltip d'information d'image
         */
        showImageTooltip(img) {
            const tooltip = $(`
                <div class="image-resize-tooltip" 
                     style="left: ${img.position().left}px; 
                            top: ${img.position().top - 30}px;">
                    ${Math.round(img.width())}×${Math.round(img.height())}px
                </div>
            `);
            
            this.contentElement.append(tooltip);
        }
        
        /**
         * NOUVEAU: Mise à jour du tooltip d'image
         */
        updateImageTooltip(width, height) {
            $('.image-resize-tooltip').text(`${Math.round(width)}×${Math.round(height)}px`);
        }
        
        /**
         * NOUVEAU: Édition des propriétés d'image
         */
        editImageProperties(img) {
            const currentSrc = img.attr('src');
            const currentAlt = img.attr('alt') || '';
            const currentWidth = img.width();
            
            const modal = $(`
                <div class="modal-backdrop-custom">
                    <div class="image-insert-modal">
                        <div class="modal-header p-3 border-bottom">
                            <h5 class="modal-title">Propriétés de l'image</h5>
                            <button type="button" class="btn-close"></button>
                        </div>
                        <div class="modal-body p-3">
                            <div class="mb-3">
                                <label class="form-label">URL de l'image</label>
                                <input type="url" name="imageUrl" class="form-control" 
                                       value="${currentSrc}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Texte alternatif</label>
                                <input type="text" name="imageAlt" class="form-control" 
                                       value="${currentAlt}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Largeur actuelle</label>
                                <input type="number" name="imageWidth" class="form-control" 
                                       value="${Math.round(currentWidth)}" min="${this.options.minImageWidth}" 
                                       max="${this.options.maxImageWidth}">
                                <div class="form-text">Entre ${this.options.minImageWidth} et ${this.options.maxImageWidth} pixels</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Actions</label>
                                <div class="btn-group w-100">
                                    <button type="button" class="btn btn-outline-danger btn-delete">
                                        <i class="bi bi-trash"></i> Supprimer
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-reset">
                                        <i class="bi bi-arrow-repeat"></i> Taille originale
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer p-3 border-top">
                            <button type="button" class="btn btn-secondary btn-cancel">Annuler</button>
                            <button type="button" class="btn btn-primary btn-apply">Appliquer</button>
                        </div>
                    </div>
                </div>
            `);
            
            $('body').append(modal);
            
            // Événements du modal
            modal.find('.btn-close, .btn-cancel, .modal-backdrop-custom').on('click', () => {
                modal.remove();
            });
            
            modal.find('.btn-delete').on('click', () => {
                img.remove();
                this.deselectImage();
                this.updateOriginalTextarea();
                modal.remove();
            });
            
            modal.find('.btn-reset').on('click', () => {
                img.css({ width: '', height: '' });
                this.updateResizeHandles();
                this.updateOriginalTextarea();
                modal.remove();
            });
            
            modal.find('.btn-apply').on('click', () => {
                const newSrc = modal.find('input[name="imageUrl"]').val();
                const newAlt = modal.find('input[name="imageAlt"]').val();
                const newWidth = parseInt(modal.find('input[name="imageWidth"]').val());
                
                if (newSrc !== currentSrc) img.attr('src', newSrc);
                img.attr('alt', newAlt);
                
                if (newWidth && newWidth !== currentWidth) {
                    const aspectRatio = img.height() / img.width();
                    img.css({
                        width: newWidth + 'px',
                        height: (newWidth * aspectRatio) + 'px'
                    });
                    this.updateResizeHandles();
                }
                
                this.updateOriginalTextarea();
                modal.remove();
            });
        }

        /**
         * ✨ NOUVELLE FONCTIONNALITÉ: Configuration du drag & drop d'images
         */
        setupDragDrop() {
            if (!this.options.enableDragDrop) return;
            
            const content = this.contentElement;
            
            // Prévenir le comportement par défaut
            content.on('dragover dragenter', (e) => {
                e.preventDefault();
                e.stopPropagation();
                content.addClass('drag-over');
            });
            
            content.on('dragleave', (e) => {
                e.preventDefault();
                e.stopPropagation();
                // Ne retirer la classe que si on sort vraiment de l'élément
                if (!content[0].contains(e.relatedTarget)) {
                    content.removeClass('drag-over');
                }
            });
            
            content.on('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                content.removeClass('drag-over');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    this.handleDroppedFiles(files);
                }
            });
        }
        
        /**
         * NOUVEAU: Gestion des fichiers déposés
         */
        handleDroppedFiles(files) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    this.insertDroppedImage(file);
                } else {
                    this.showError(`Le fichier ${file.name} n'est pas une image supportée.`);
                }
            });
        }
        
        /**
         * NOUVEAU: Insertion d'image déposée
         */
        insertDroppedImage(file) {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                const imageData = e.target.result;
                const img = new Image();
                
                img.onload = () => {
                    // Calculer la taille optimale
                    let width = img.width;
                    let height = img.height;
                    
                    if (width > this.options.maxImageWidth) {
                        const ratio = this.options.maxImageWidth / width;
                        width = this.options.maxImageWidth;
                        height = height * ratio;
                    }
                    
                    const imageHtml = `<img src="${imageData}" 
                                           alt="${file.name}" 
                                           style="width: ${width}px; height: ${height}px; margin: 10px 0;"
                                           data-resizable="true"
                                           data-filename="${file.name}">`;
                    
                    // Insertion à la position du curseur
                    if (document.getSelection && document.getSelection().rangeCount > 0) {
                        const selection = document.getSelection();
                        const range = selection.getRangeAt(0);
                        range.deleteContents();
                        const div = document.createElement('div');
                        div.innerHTML = imageHtml;
                        range.insertNode(div.firstChild);
                    } else {
                        this.contentElement.append(imageHtml);
                    }
                    
                    this.updateOriginalTextarea();
                    this.setupImageResize();
                    
                    this.showSuccess(`Image ${file.name} ajoutée avec succès!`);
                    
                    if (this.options.onImageResize) {
                        this.options.onImageResize('dropped', imageData, { width, height });
                    }
                };
                
                img.src = imageData;
            };
            
            reader.onerror = () => {
                this.showError(`Erreur lors de la lecture du fichier ${file.name}`);
            };
            
            reader.readAsDataURL(file);
        }
        
        /**
         * NOUVEAU: Affichage d'erreur amélioré
         */
        showError(message) {
            this.showNotification(message, 'danger');
        }
        
        /**
         * NOUVEAU: Affichage de succès
         */
        showSuccess(message) {
            this.showNotification(message, 'success');
        }
        
        /**
         * NOUVEAU: Système de notification amélioré
         */
        showNotification(message, type = 'info') {
            const toast = $(`
                <div class="toast align-items-center text-white bg-${type} border-0 position-fixed" 
                     style="top: 20px; right: 20px; z-index: 10000; min-width: 300px;" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-${type === 'danger' ? 'exclamation-triangle' : 
                                                type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto"></button>
                    </div>
                </div>
            `);
            
            $('body').append(toast);
            
            // Auto-suppression
            setTimeout(() => {
                toast.fadeOut(() => toast.remove());
            }, type === 'success' ? 3000 : 5000);
            
            // Fermeture manuelle
            toast.find('.btn-close').on('click', () => {
                toast.fadeOut(() => toast.remove());
            });
        }

        insertMediaDialog() {
            if (!this.mediaPicker) {
                this.initMediaPicker();
            }
            this.mediaPicker.show();
        }

        initMediaPicker() {
            // Créer un élément temporaire pour le MediaPicker
            const tempElement = $('<div>').appendTo('body');
            
            this.mediaPicker = new MediaPicker(tempElement, {
                multiple: true,
                insertMode: true,
                onSelect: (medias) => {
                    this.insertMedias(Array.isArray(medias) ? medias : [medias]);
                }
            });
        }

        insertMedias(medias) {
            let html = '';
            
            medias.forEach(media => {
                if (media.isImage) {
                    html += `<img src="${media.url}" alt="${media.alt || ''}" style="max-width: 100%; height: auto; margin: 10px 0;">`;
                } else {
                    // Pour les autres types de médias, créer un lien de téléchargement
                    html += `<p><a href="${media.url}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download me-2"></i>${media.alt || media.fileName}
                    </a></p>`;
                }
            });

            document.execCommand('insertHTML', false, html);
            this.updateOriginalTextarea();
        }

        cleanPastedContent() {
            // Nettoyer le contenu collé en supprimant les styles indésirables
            const content = this.contentElement;
            content.find('*').each(function() {
                const element = $(this);
                // Garder seulement certains attributs
                const allowedAttrs = ['href', 'src', 'alt', 'title'];
                const attrs = this.attributes;
                for (let i = attrs.length - 1; i >= 0; i--) {
                    const attr = attrs[i];
                    if (!allowedAttrs.includes(attr.name)) {
                        element.removeAttr(attr.name);
                    }
                }
            });
        }

        /**
         * AMÉLIORATION: Mise à jour du placeholder sans styles inline répétitifs
         */
        updatePlaceholder() {
            const content = this.contentElement;
            const isEmpty = content.text().trim() === '' && content.find('img, video, audio').length === 0;
            
            if (isEmpty) {
                content.attr('data-placeholder', this.options.placeholder);
                content.addClass('empty');
            } else {
                content.removeClass('empty');
                content.removeAttr('data-placeholder');
            }
        }

        updateOriginalTextarea() {
            const html = this.contentElement.html();
            this.element.val(html);
        }

        getContent() {
            return this.contentElement.html();
        }

        setContent(html) {
            this.contentElement.html(html);
            this.updateOriginalTextarea();
            this.updatePlaceholder();
        }

        insertHTML(html) {
            document.execCommand('insertHTML', false, html);
            this.updateOriginalTextarea();
        }

        focus() {
            this.contentElement.focus();
        }

        // Nouvelles fonctionnalités avancées
        
        /**
         * AMÉLIORATION: Mode plein écran sans styles inline répétitifs
         */
        toggleFullscreen() {
            this.isFullscreen = !this.isFullscreen;
            const button = this.editorElement.find('[data-command="toggleFullscreen"]');
            
            if (this.isFullscreen) {
                this.editorElement.addClass('editor-fullscreen');
                button.find('i').removeClass('bi-arrows-fullscreen').addClass('bi-fullscreen-exit');
                button.attr('title', 'Quitter le plein écran');
                $('body').addClass('editor-fullscreen-active');
                
                // Notification
                this.showSuccess('Mode plein écran activé. Appuyez sur Échap ou cliquez sur l\'icône pour quitter.');
            } else {
                this.editorElement.removeClass('editor-fullscreen');
                button.find('i').removeClass('bi-fullscreen-exit').addClass('bi-arrows-fullscreen');
                button.attr('title', 'Plein écran');
                $('body').removeClass('editor-fullscreen-active');
            }
            
            // Mettre à jour les handles si une image est sélectionnée
            if (this.selectedImage) {
                setTimeout(() => this.updateResizeHandles(), 100);
            }
        }

        toggleSourceMode() {
            this.isSourceMode = !this.isSourceMode;
            const button = this.editorElement.find('[data-command="toggleSource"]');
            
            if (this.isSourceMode) {
                // Passer en mode source
                const html = this.contentElement.html();
                this.contentElement.attr('contenteditable', 'false').hide();
                
                const sourceArea = $(`<textarea class="editor-source" style="
                    width: 100%; 
                    height: ${this.options.height}px; 
                    padding: 15px; 
                    border: none; 
                    outline: none; 
                    font-family: 'Courier New', monospace;
                    font-size: 14px;
                    resize: none;
                    background: #f8f9fa;
                    color: #333;
                ">${html}</textarea>`);
                
                this.contentElement.after(sourceArea);
                this.sourceArea = sourceArea;
                
                button.addClass('active');
                button.attr('title', 'Mode visuel');
                
                // Désactiver les autres boutons
                this.editorElement.find('.toolbar-btn').not('[data-command="toggleSource"]').prop('disabled', true);
            } else {
                // Retour au mode visuel
                const sourceHtml = this.sourceArea.val();
                this.contentElement.html(sourceHtml).show().attr('contenteditable', 'true');
                this.sourceArea.remove();
                this.sourceArea = null;
                
                button.removeClass('active');
                button.attr('title', 'Code source');
                
                // Réactiver les autres boutons
                this.editorElement.find('.toolbar-btn').prop('disabled', false);
                
                this.updateOriginalTextarea();
            }
        }

        showWordCount() {
            this.updateWordCount();
            const toast = $(`
                <div class="toast align-items-center text-white bg-primary border-0 position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999;" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong>Statistiques du texte :</strong><br>
                            Mots : ${this.wordCount}<br>
                            Caractères : ${this.getCharacterCount()}<br>
                            Paragraphes : ${this.getParagraphCount()}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                                data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `);
            
            $('body').append(toast);
            
            if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                new bootstrap.Toast(toast[0]).show();
            } else {
                toast.show();
                setTimeout(() => toast.fadeOut(() => toast.remove()), 5000);
            }
        }

        updateWordCount() {
            const text = this.contentElement.text();
            this.wordCount = text.trim() ? text.trim().split(/\s+/).length : 0;
        }

        getCharacterCount() {
            return this.contentElement.text().length;
        }

        getParagraphCount() {
            return this.contentElement.find('p, div, h1, h2, h3, h4, h5, h6').length || 1;
        }

        setupAutoSave() {
            if (this.options.enableAutoSave && this.options.autoSaveInterval > 0) {
                this.autoSaveTimer = setInterval(() => {
                    this.autoSave();
                }, this.options.autoSaveInterval);
            }
        }

        autoSave() {
            const currentContent = this.getContent();
            if (currentContent !== this.originalContent) {
                this.originalContent = currentContent;
                
                if (this.options.onAutoSave) {
                    this.options.onAutoSave(currentContent);
                }
                
                // Afficher un indicateur de sauvegarde
                const indicator = $('<span class="auto-save-indicator text-success ms-2"><i class="bi bi-check-circle"></i> Sauvegardé</span>');
                this.editorElement.find('.editor-toolbar').append(indicator);
                setTimeout(() => indicator.fadeOut(() => indicator.remove()), 2000);
            }
        }

        clearAutoSave() {
            if (this.autoSaveTimer) {
                clearInterval(this.autoSaveTimer);
                this.autoSaveTimer = null;
            }
        }

        /**
         * AMÉLIORATION: Destruction complète avec nettoyage des nouvelles fonctionnalités
         */
        destroy() {
            // Nettoyer les timers
            this.clearAutoSave();
            if (this.changeDebouncer) {
                clearTimeout(this.changeDebouncer);
            }
            
            // Nettoyer les event listeners spécifiques
            this.contentElement.off('.imageResize');
            $(window).off('resize.customEditor');
            
            // Désélectionner l'image active
            this.deselectImage();
            
            // Nettoyer le MediaPicker
            if (this.mediaPicker) {
                this.mediaPicker.destroy();
            }
            
            // Supprimer l'éditeur et restaurer le textarea
            this.editorElement.remove();
            this.element.show();
            
            // Supprimer les données associées
            this.element.removeData('customEditor');
            
            // Nettoyer les modals ouvertes
            $('.modal-backdrop-custom, .image-insert-modal').remove();
            
            // Nettoyer les toasts
            $('.toast').remove();
        }
    }

    // Plugin jQuery
    $.fn.customEditor = function(options) {
        return this.each(function() {
            if (!$(this).data('customEditor')) {
                $(this).data('customEditor', new CustomEditor(this, options));
            }
        });
    };

    // Auto-initialisation pour les textareas avec la classe 'custom-editor'
    $(document).ready(function() {
        $('textarea.custom-editor').customEditor();
    });

    // Expose la classe globalement
    window.CustomEditor = CustomEditor;

})(jQuery);
