<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    #[Route('/comment/delete/{id}', name: 'comment_delete')]
    public function delete(Comment $comment, EntityManagerInterface $manager){
        $edit = false;
        if ($comment){
            $manager->remove($comment);
            $manager->flush();
        }
        return$this->redirectToRoute("post_show", [
            "id"=>$comment->getPosts()->getId(),
            "editComm"=>$edit
        ]);
    }


    #[Route('/comment/create/{id}', name:'comment_create')]
    public function create(Request $request, EntityManagerInterface $manager, Post $post){


        $edit = false;

        $comment =  new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){

            $comment->setPosts($post);
            $comment->setAuthor($this->getUser());
            $comment->setCreatedAt(new \DateTime());
            $manager->persist($comment);
            $manager->flush();

            return$this->redirectToRoute("post_show", [
                "id"=>$comment->getPosts()->getId()
            ]);
        }
        return$this->redirectToRoute("post_show", [
            "id"=>$comment->getPosts()->getId(),
            "editComm"=>$edit
        ]);
    }

    #[Route('/comment/edit/{id}', name:'comment_edit')]
    public function update(Request $request, EntityManagerInterface $manager, Comment $comment){


        $edit = true;

        if ($comment){$edit=true;}


        $form = $this->createForm( CommentType::class, $comment);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()){
            if ($comment->getAuthor() != $this->getUser()){
                return $this->redirectToRoute('app_post');
            }
            $comment->setCreatedAt(new \DateTime());
            $manager->persist($comment);
            $manager->flush();

            return$this->redirectToRoute("post_show", [
                "id"=>$comment->getPosts()->getId()
            ]);
        }
        return $this->renderForm("comment/edit.html.twig", [
            "form"=>$form
        ]);
    }



}