<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Post;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LikeController extends AbstractController
{
    #[Route('/like/{id}', name: 'app_like')]
    public function index(Post $post, LikeRepository $likeRepository, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();

        if ($post->isLikedBy($user)){
            $like = $likeRepository->findOneBy([
                'users'=>$user,
                'post'=>$post
            ]);
            $manager->remove($like);
            $isLiked = false;
        }else{

            $like = new Like();
            $like->setPost($post);
            $like->setUsers($user);
            $manager->persist($like);
            $isLiked = true;
        }
        $manager->flush();

        $data = [
            'liked' => $isLiked,
            'count' => $likeRepository->count(['post'=>$post])
        ];

        return $this->json($data, 200);
    }
}
