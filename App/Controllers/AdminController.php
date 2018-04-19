<?php

namespace App\Controllers;

use App\Factory\ImageServiceFactory;
use App\Services\PostService;

class AdminController extends AbstractController
{
    public function indexAction()
    {
        $this->template = 'admin/index.html';

        $postService = new PostService($this->entityManager);
        $this->view['posts'] = $postService->listPosts(0, 20, 'id', 'ASC', false);

        return $this->render();
    }

    public function addPostAction()
    {
        $this->template = 'admin/addPost.html';

        //TODO: Validate token!

        if (isset($_POST['csfrtoken'])) {
            $postService = new PostService($this->entityManager);
            $postService->addPost($_POST);

            return $this->indexAction();
        }

        return $this->render();
    }

    public function editPostAction()
    {
        // @TODO: Add media, too
        $this->template = 'admin/editPost.html';

        $postService = new PostService($this->entityManager);

        if (isset($_POST['csfrtoken'])) {
            $postService->updatePost($_POST);

            return $this->indexAction();
        }

        $this->view['post'] = $postService->getPost((int) $_GET['id']);

        return $this->render();
    }

    public function listImagesAction()
    {
        $this->template = 'admin/listImages.html';

        // @TODO: Finish me!

        return $this->render();
    }

    public function addImageAction()
    {
        $this->template = 'admin/addImage.html';

        // @TODO: Finish me!

        //TODO: Validate token!

        if (isset($_POST['csfrtoken'])) {
            var_dump($_POST);

            var_dump($_FILES);

            $imageService = ImageServiceFactory::build($this->entityManager);
            $imageService->uploadImages($_FILES, $_POST);
        }

        return $this->render();
    }

    public function phpinfoAction()
    {
        die(phpinfo());
    }
}
