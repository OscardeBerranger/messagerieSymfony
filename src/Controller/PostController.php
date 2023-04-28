<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Form\SearchType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Array_;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Sodium\add;

class PostController extends AbstractController
{
    #[Route('/', name: 'app_post')]
    public function index(PostRepository $postRepository): Response
    {
        $editComm = false;
        $posts = $postRepository->findAll();
        return $this->render('post/index.html.twig', [
            'posts'=>$posts,
            'search'=>false
        ]);
    }

    #[Route('/create', name:'post_create')]
    #[Route("/edit/{id}", name:'post_edit')]
    public function create(Request $request, EntityManagerInterface $manager, Post $post = null)
    {

        $edit = false;

        if ($post){
            $edit=true;
        if ($post->getAuthor() != $this->getUser()){
            return $this->redirectToRoute('app_post');
        }
        }

        if (!$post){
            $post = new Post;
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()){
            $post->setAuthor($this->getUser());
            $post->setCreatedAt(new \DateTime());
            $manager->persist($post);
            $manager->flush();

            return$this->redirectToRoute("app_post");
        }
        return $this->renderForm("post/create.html.twig", [
            "form"=>$form,
            "edit"=>$edit
        ]);

    }

    #[Route('/show{id}', name:'post_show')]
    public function show(Post $post, Request $request){
        $edit=false;
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        return$this->renderForm('post/show.html.twig', [
            'form'=>$form,
            'post'=>$post,
            "editComm"=>$edit

        ]);
    }

    #[Route('/delete/{id}', name:'post_delete')]
    public function delete(Post $post, EntityManagerInterface $manager){
        if ($post){
            $manager->remove($post);
            $manager->flush();
        }
        return $this->redirectToRoute('app_post');
    }
    #[Route('/post/search', name: 'post_search')]
    public function search(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
        $postsRender = array();
        if ($_GET['query']){
            $query = $_GET['query'];
            $i = 0;
            foreach ($posts as $post){
                if (str_contains($post->getContent(), $query)){
                    array_push($postsRender, $post);
                }
            }
            return $this->render('post/index.html.twig', [
                'posts'=>$postsRender
            ]);
        }
        return $this->render('post/index.html.twig', [
            'posts'=>$posts
        ]);
    }
}