<?php

namespace  App\Controller;

use App\Entity\GroupConversation;
use App\Form\GroupConversationType;
use App\Repository\GroupConversationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class GroupConversationController extends AbstractController
{
    //BREAD controller action pattern
    /**
     * Display list of conversations
     *
     * @Route("/conversations", name="conversation_browse")
     * @param GroupConversationRepository $groupConversationRepository
     * @return Response
     */
    public function browse(GroupConversationRepository $groupConversationRepository): Response {
        $conversations = $groupConversationRepository->findAll();

        $response = $this->render('conversation/browse.html.twig', [
            'conversations' => $conversations,
        ]);

        return $response;
    }

    /**
     * Display one conversation group
     *
     * @Route("/conversation/{id}", name="conversation_read", requirements={"id" : "\d+"})
     */
    public function read(GroupConversationRepository $groupConversationRepository): Response
    {

    }

    /**
     * Create new Conversation group
     *
     * @Route("/conversation/add", name="conversation_add")
     */
    public function add(Request $request): Response
    {
        // just setup a fresh $task object (remove the example data)
        $conversation = new GroupConversation();

        $form = $this->createForm(GroupConversationType::class, $conversation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $conversationFormValues = $form->getData();

            $conversation->setCreated(new \DateTime('now'));
            $conversation->setUpdated(new \DateTime('now'));

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
             $em = $this->getDoctrine()->getManager();
             $em->persist($conversationFormValues);
             $em->flush();

            $this->addFlash('success', 'Nouvelle conversation ajoutée.');
            return $this->redirectToRoute('conversation_browse');
        }

        return $this->renderForm('conversation/_form/add.html.twig', [
            'form' => $form,
        ]);
    }

//    /**
//     * Delete conversation group
//     *
//     * @Route("/conversation/{id}/delete", name="conversation_delete", requirements={"id" : "\d+"})
//     */
//    public function delete(): Response
//    {
//
//    }
}