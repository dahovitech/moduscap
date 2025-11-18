//import './bootstrap.js';
import * as bootstrap from 'bootstrap';
import $ from 'jquery';



window.bootstrap = bootstrap;

// Rendre jQuery disponible globalement
global.$ = global.jQuery = $;

import './admin/styles/admin.scss';

// Import des composants média et éditeur
import './admin/js/components/media-picker.js';
import './admin/js/components/custom-editor.js';
import './admin/js/components/media-selector.js';

// Initialisation automatique des composants
$(() => {
    // Initialiser les éditeurs personnalisés
    $('textarea.custom-editor').each(function() {
        const $textarea = $(this);
        
        // Récupération des options depuis les attributs data
        const enableMedia = $textarea.data('enable-media') === true || $textarea.data('enable-media') === 'true';
        const enableEditor = $textarea.data('enable-editor') === true || $textarea.data('enable-editor') === 'true';
        const height = parseInt($textarea.data('editor-height')) || 300;
        const enableImageResize = $textarea.data('enable-image-resize') === true || $textarea.data('enable-image-resize') === 'true';
        const enableDragDrop = $textarea.data('enable-drag-drop') === true || $textarea.data('enable-drag-drop') === 'true';
        const maxImageWidth = parseInt($textarea.data('max-image-width')) || 800;
        const minImageWidth = parseInt($textarea.data('min-image-width')) || 50;
        
        // Initialiser seulement si l'éditeur doit être activé
        if (enableEditor) {
            $textarea.customEditor({
                height: height,
                enableMedia: enableMedia,
                enableImageResize: enableImageResize,
                enableDragDrop: enableDragDrop,
                maxImageWidth: maxImageWidth,
                minImageWidth: minImageWidth,
                enableAutoSave: true,
                autoSaveInterval: 30000,
                placeholder: $textarea.attr('placeholder') || 'Tapez votre contenu ici...',
                toolbar: [
                    'bold', 'italic', 'underline', '|',
                    'h1', 'h2', 'h3', '|',
                    'link', 'unlink', '|',
                    'image', 'media', '|',
                    'unorderedList', 'orderedList', '|',
                    'blockquote', 'code', '|',
                    'fullscreen', 'source', 'wordcount'
                ],
                onAutoSave: function(content) {
                    console.log('Sauvegarde automatique: ' + content.length + ' caractères');
                }
            });
        }
    });
});

