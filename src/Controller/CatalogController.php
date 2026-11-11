<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[Route('/catalog', name: 'app_catalog')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository
    ): Response {
        $categoryId = $request->query->get('category');
        $search = $request->query->get('q', '');
        $page = max(1, $request->query->getInt('page', 1));

        $categories = $categoryRepository->findAll();

        // Fetch products based on filters
        $products = $productRepository->findByFilters($categoryId, $search, $page, 8);

        return $this->render('catalog/index.html.twig', [
            'categories' => $categories,
            'active_category' => $categoryId ? (int)$categoryId : null,
            'products' => $products,
            'search_query' => $search,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        return $this->render('catalog/show.html.twig', [
            'product' => $product,
        ]);
    }
}
