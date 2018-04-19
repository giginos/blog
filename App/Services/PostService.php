<?php

namespace App\Services;

use App\Models\Post;
use Doctrine\ORM\EntityManagerInterface;

class PostService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * PostService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int    $start
     * @param int    $amount
     * @param string $orderBy
     * @param string $orderDirection
     * @param bool   $onlyActive
     *
     * @return array
     */
    public function listPosts($start = 0, $amount = 20, $orderBy = 'id', $orderDirection = 'ASC', $onlyActive = true): array
    {
        $postRepro = $this->entityManager->getRepository(Post::class);

        $filter = [];

        if ($onlyActive) {
            $filter = ['active' => true];
        }

        return $postRepro->findBy($filter, [$orderBy => $orderDirection], $amount, $start);
    }

    /**
     * @param int $postId
     *
     * @return null|object
     */
    public function getPost(int $postId)
    {
        $postRepro = $this->entityManager->getRepository(Post::class);

        /** @var Post $post */
        return $postRepro->find($postId);
    }

    /**
     * @param array $data
     */
    public function addPost(array $data)
    {
        $post = new Post();
        $post->setTitle($data['title']);
        $post->setUrl($data['url']);

        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }

    /**
     * @param array $data
     */
    public function updatePost(array $data)
    {
        $postRepro = $this->entityManager->getRepository(Post::class);
        /** @var Post $post */
        $post = $postRepro->find($data['id']);
        $post->setTitle($data['title']);

        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }

    /**
     * @param int $postId
     */
    public function deletePost(int $postId)
    {
        $postRepro = $this->entityManager->getRepository(Post::class);
        $post = $postRepro->find($postId);
        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }
}
