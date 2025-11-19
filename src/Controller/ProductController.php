<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\Setting;
use App\Service\PriceCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}/products', requirements: ['_locale' => '[a-z]{2}'])]
class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PriceCalculatorService $priceCalculator,
        private TranslatorInterface $translator
    ) {}

    /**
     * Product catalog with filtering
     */
    #[Route('/', name: 'app_products_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $category = $request->query->get('category');
        $priceMin = $request->query->get('price_min');
        $priceMax = $request->query->get('price_max');
        $search = $request->query->get('search');

        $qb = $this->entityManager->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('c', 'pt')
            ->where('p.isActive = :active')
            ->setParameter('active', true);

        if ($category) {
            $qb->andWhere('c.code = :category')
               ->setParameter('category', $category);
        }

        if ($search) {
            $qb->andWhere('pt.name LIKE :search OR pt.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($priceMin) {
            $qb->andWhere('p.basePrice >= :priceMin')
               ->setParameter('priceMin', $priceMin);
        }

        if ($priceMax) {
            $qb->andWhere('p.basePrice <= :priceMax')
               ->setParameter('priceMax', $priceMax);
        }

        $qb->orderBy('p.sortOrder', 'ASC')
           ->addOrderBy('pt.name', 'ASC');

        $products = $qb->getQuery()->getResult();
        $categories = $this->entityManager->getRepository(\App\Entity\ProductCategory::class)->findBy([], ['sortOrder' => 'ASC']);

        return $this->render('@theme/products/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'category' => $category,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'search' => $search
            ]
        ]);
    }

    /**
     * Product detail with customization options
     */
    #[Route('/{code}', name: 'app_product_show', methods: ['GET', 'POST'])]
    public function show(Request $request, string $code): Response
    {
        $product = $this->entityManager->getRepository(Product::class)->findOneBy(['code' => $code]);
        
        if (!$product || !$product->isActive()) {
            throw $this->createNotFoundException($this->translator->trans('controller.product.not_found'));
        }

        $locale = $request->getLocale();
        
        // Group options by their option groups for the UI
        $groupedOptions = $this->priceCalculator->groupOptionsByGroup($product);
        
        // Calculate base price breakdown
        $pricingBreakdown = $this->priceCalculator->getPricingBreakdown($product);
        
        // Get gallery and technical images
        $galleryImages = $product->getGalleryImages();
        $mainImage = $product->getMainImage();
        $technicalImages = $product->getTechnicalImages();

        if ($request->isMethod('POST')) {
            return $this->handleProductCustomization($request, $product);
        }

        return $this->render('@theme/products/show.html.twig', [
            'product' => $product,
            'grouped_options' => $groupedOptions,
            'pricing_breakdown' => $pricingBreakdown,
            'gallery_images' => $galleryImages,
            'main_image' => $mainImage,
            'technical_images' => $technicalImages,
            'locale' => $locale
        ]);
    }

    /**
     * AJAX endpoint to calculate price with selected options
     */
    #[Route('/{code}/calculate-price', name: 'app_product_calculate_price', methods: ['POST'])]
    public function calculatePrice(Request $request, string $code): JsonResponse
    {
        $product = $this->entityManager->getRepository(Product::class)->findOneBy(['code' => $code]);
        
        if (!$product || !$product->isActive()) {
            return new JsonResponse(['error' => $this->translator->trans('controller.product.not_found')], 404);
        }

        $data = json_decode($request->getContent(), true);
        $selectedOptions = $data['selected_options'] ?? [];
        $quantity = $data['quantity'] ?? 1;

        $pricing = $this->priceCalculator->calculateOrderItemPrice($product, $selectedOptions, $quantity);
        
        return new JsonResponse([
            'success' => true,
            'pricing' => $pricing,
            'breakdown' => $this->priceCalculator->getPricingBreakdown($product, $selectedOptions, $quantity)
        ]);
    }

    /**
     * AJAX endpoint to validate selected options
     */
    #[Route('/{code}/validate-options', name: 'app_product_validate_options', methods: ['POST'])]
    public function validateOptions(Request $request, string $code): JsonResponse
    {
        $product = $this->entityManager->getRepository(Product::class)->findOneBy(['code' => $code]);
        
        if (!$product || !$product->isActive()) {
            return new JsonResponse(['error' => $this->translator->trans('controller.product.not_found')], 404);
        }

        $data = json_decode($request->getContent(), true);
        $selectedOptionCodes = $data['selected_options'] ?? [];

        $validation = $this->priceCalculator->validateProductOptions($product, $selectedOptionCodes);
        
        return new JsonResponse([
            'success' => true,
            'valid' => $validation['is_valid'],
            'valid_options' => array_map(fn($opt) => [
                'id' => $opt->getId(),
                'code' => $opt->getCode(),
                'name' => $opt->getName(),
                'price' => $opt->getPrice()
            ], $validation['valid']),
            'invalid_options' => $validation['invalid']
        ]);
    }

    /**
     * Handle product customization form submission
     */
    private function handleProductCustomization(Request $request, Product $product): Response
    {
        // Check if user is logged in - redirect to login if not
        if (!$this->getUser()) {
            // Store the customization in session to resume after login
            $formData = $request->request->all();
            $request->getSession()->set('pending_customization', [
                'product_code' => $product->getCode(),
                'form_data' => $formData
            ]);
            
            $this->addFlash('info', $this->translator->trans('controller.auth.login_required_for_quote'));
            return $this->redirectToRoute('app_user_login', ['_locale' => $request->getLocale()]);
        }
        
        $formData = $request->request->all();
        
        $selectedOptions = $formData['selected_options'] ?? [];
        $quantity = max(1, intval($formData['quantity'] ?? 1));
        $customizationNotes = $formData['customization_notes'] ?? null;
        
        // Get client info - name/email ALWAYS from authenticated user
        // phone/address can be provided in form
        $user = $this->getUser();
        $clientPhone = $formData['client_phone'] ?? '';
        $clientAddress = $formData['client_address'] ?? '';

        // Validate product and options
        $validation = $this->priceCalculator->validateProductOptions($product, $selectedOptions);
        
        if (!$validation['is_valid']) {
            $this->addFlash('error', $this->translator->trans('controller.product.options_invalid'));
            return $this->redirectToRoute('app_product_show', [
                'code' => $product->getCode(),
                '_locale' => $request->getLocale()
            ]);
        }

        // Store customization in session for next step
        // Note: name/email are not stored - they will be fetched from User at order creation
        $request->getSession()->set('product_customization', [
            'product_id' => $product->getId(),
            'selected_options' => $selectedOptions,
            'quantity' => $quantity,
            'customization_notes' => $customizationNotes,
            'client_info' => [
                'phone' => $clientPhone,
                'address' => $clientAddress
            ]
        ]);

        return $this->redirectToRoute('app_quote_create', ['_locale' => $request->getLocale()]);
    }

    /**
     * Get volume pricing for a product
     */
    #[Route('/{code}/volume-pricing', name: 'app_product_volume_pricing', methods: ['POST'])]
    public function volumePricing(Request $request, string $code): JsonResponse
    {
        $product = $this->entityManager->getRepository(Product::class)->findOneBy(['code' => $code]);
        
        if (!$product || !$product->isActive()) {
            return new JsonResponse(['error' => $this->translator->trans('controller.product.not_found')], 404);
        }

        $data = json_decode($request->getContent(), true);
        $selectedOptions = $data['selected_options'] ?? [];
        $quantity = $data['quantity'] ?? 1;

        $volumePricing = $this->priceCalculator->calculateVolumePricing($product, $selectedOptions, $quantity);
        
        return new JsonResponse([
            'success' => true,
            'volume_pricing' => $volumePricing
        ]);
    }

    /**
     * Get payment information from settings
     */
    #[Route('/payment-info', name: 'app_payment_info', methods: ['GET'])]
    public function getPaymentInfo(): JsonResponse
    {
        $setting = $this->entityManager->getRepository(Setting::class)->findOneBy([]);
        
        if (!$setting) {
            return new JsonResponse(['error' => $this->translator->trans('controller.system.payment_info_unavailable')], 404);
        }

        return new JsonResponse([
            'success' => true,
            'payment_info' => $setting->getPaymentInfo()
        ]);
    }
}