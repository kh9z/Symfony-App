<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/tests', name: 'test_')]
class TestController extends AbstractController
{
    #[Route('/reset', name: 'reset', methods: ['GET'])]
    public function resetTests(SessionInterface $session): JsonResponse
    {
        $testItems = [
            ['id' => 0, 'name' => 'Item 1', 'desc' => 'Something is not here'],
            ['id' => 1, 'name' => 'Item 2', 'desc' => 'Something is here'],
            ['id' => 2, 'name' => 'Item 3', 'desc' => 'Something ig'],
        ];
        $session->set('testItems', $testItems);

        return $this->json($testItems);
    }

    #[Route('/', name: 'list', methods: ['GET'])]
    public function getTests(Request $request, SessionInterface $session): JsonResponse
    {
        $testItems = $session->get('testItems', []);
        $search = $request->query->get('search');

        $filteredItems = $search
            ? array_filter($testItems, fn($item) => stripos($item['name'], $search) !== false)
            : $testItems;

        return $this->json(array_values($filteredItems));
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getTest(int $id, SessionInterface $session): JsonResponse
    {
        $testItems = $session->get('testItems', []);
        $item = array_filter($testItems, fn($item) => $item['id'] === $id);

        return $item ? $this->json(array_shift($item)) : $this->json(['message' => 'Item not found'], 404);
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    public function addTest(Request $request, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['id'], $data['name'], $data['desc'])) {
            return $this->json(['message' => 'Missing fields'], 400);
        }

        $testItems = $session->get('testItems', []);
        $testItems[] = $data;
        $session->set('testItems', $testItems);

        return $this->json($data, 201);
    }

    #[Route('/update/{id}', name: 'update', methods: ['PATCH'])]
    public function updateTest(int $id, Request $request, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $testItems = $session->get('testItems', []);

        foreach ($testItems as &$item) {
            if ($item['id'] === $id) {
                $item = array_merge($item, array_intersect_key($data, ['id' => '', 'name' => '', 'desc' => '']));
                $session->set('testItems', $testItems);

                return $this->json($item);
            }
        }

        return $this->json(['message' => 'Item not found'], 404);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteTest(int $id, SessionInterface $session): JsonResponse
    {
        $testItems = $session->get('testItems', []);
        $testItems = array_filter($testItems, fn($item) => $item['id'] !== $id);

        $session->set('testItems', array_values($testItems));

        return $this->json(null, 204);
    }
}
