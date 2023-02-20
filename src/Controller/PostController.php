<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Post;

#[Route('/api', name: 'blog_')]
class PostController extends AbstractController
{
    #[Route('/post', name: 'index', methods: 'GET')]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {
        $posts = $doctrine
            ->getRepository(Post::class)
            ->findAll();

        $data = [];

        foreach ($posts as $post) {
            $data[] = [
                'id' => $post->getId(),
                'authorName' => $post->getAuthorName(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/post', name: 'new', methods: 'POST')]
    public function new(ManagerRegistry $doctrine, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $post = new Post();

        $post->setAuthorName($request->request->get('authorName'));
        $post->setTitle($request->request->get('title'));
        $post->setContent($request->request->get('content'));

        $errors = $validator->validate($post);

        if (count($errors) > 0) {
            $validationErrors = array();
            foreach ($errors as $error) {
                $validationErrors[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json($validationErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager = $doctrine->getManager();
        $entityManager->persist($post);
        $entityManager->flush();

        return $this->json([
            'id' => $post->getId(),
            'authorName' => $post->getAuthorName(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
        ]);
    }
}
