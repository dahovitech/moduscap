<?php

namespace App\Controller\Admin;

use App\Form\Type\MediaTextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/editor-demo', name: 'admin_editor_demo_')]
class EditorDemoController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/editor-demo/index.html.twig');
    }

    #[Route('/basic', name: 'basic', methods: ['GET', 'POST'])]
    public function basicExample(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'article',
                'attr' => ['class' => 'form-control']
            ])
            ->add('content', MediaTextareaType::class, [
                'label' => 'Contenu de l\'article',
                'required' => false,
                'enable_media' => true,
                'enable_editor' => true,
                'editor_height' => 400,
                'attr' => [
                    'rows' => 10,
                    'placeholder' => 'Rédigez votre article ici...'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer l\'article',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            $this->addFlash('success', sprintf(
                'Article "%s" enregistré avec succès ! Contenu: %d caractères.',
                $data['title'],
                strlen(strip_tags($data['content']))
            ));

            return $this->redirectToRoute('admin_editor_demo_basic');
        }

        return $this->render('admin/editor-demo/basic.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/advanced', name: 'advanced', methods: ['GET', 'POST'])]
    public function advancedExample(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('title', TextType::class, [
                'label' => 'Titre du document',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', MediaTextareaType::class, [
                'label' => 'Description courte',
                'required' => false,
                'enable_media' => false,
                'enable_editor' => true,
                'editor_height' => 150,
                'attr' => [
                    'placeholder' => 'Description courte sans médias...',
                    'data-enable-auto-save' => 'true',
                    'data-auto-save-interval' => '10000' // 10 secondes
                ]
            ])
            ->add('content', MediaTextareaType::class, [
                'label' => 'Contenu principal (avec sauvegarde automatique)',
                'required' => false,
                'enable_media' => true,
                'enable_editor' => true,
                'editor_height' => 500,
                'attr' => [
                    'rows' => 15,
                    'placeholder' => 'Contenu principal avec toutes les fonctionnalités...',
                    'data-enable-auto-save' => 'true',
                    'data-auto-save-interval' => '15000' // 15 secondes
                ]
            ])
            ->add('notes', MediaTextareaType::class, [
                'label' => 'Notes internes',
                'required' => false,
                'enable_media' => true,
                'enable_editor' => true,
                'editor_height' => 200,
                'attr' => [
                    'placeholder' => 'Notes et commentaires internes...'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer le document',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            $this->addFlash('success', sprintf(
                'Document "%s" enregistré avec succès !',
                $data['title']
            ));

            return $this->redirectToRoute('admin_editor_demo_advanced');
        }

        return $this->render('admin/editor-demo/advanced.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/programmatic', name: 'programmatic', methods: ['GET'])]
    public function programmaticExample(): Response
    {
        return $this->render('admin/editor-demo/programmatic.html.twig');
    }

    #[Route('/v2-showcase', name: 'v2_showcase', methods: ['GET'])]
    public function v2Showcase(): Response
    {
        return $this->render('admin/editor-demo/v2-showcase.html.twig');
    }

    #[Route('/ajax-save', name: 'ajax_save', methods: ['POST'])]
    public function ajaxSave(Request $request): Response
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        // Simuler la sauvegarde
        if (isset($data['content'])) {
            // Ici vous pourriez sauvegarder en base de données
            return $this->json([
                'success' => true,
                'message' => 'Contenu sauvegardé automatiquement',
                'timestamp' => new \DateTime(),
                'word_count' => str_word_count(strip_tags($data['content']))
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Erreur lors de la sauvegarde'
        ], 400);
    }
}