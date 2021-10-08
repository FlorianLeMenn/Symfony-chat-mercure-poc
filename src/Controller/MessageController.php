<?php

namespace App\Controller;

use App\Entity\GroupConversation;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\CookieGenerator;
use Cassandra\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;


/**
 * @Route("/messages", name="messages_")
 */
class MessageController extends AbstractController
{

    /**
     * @var MessageRepository
     */
    private $messageRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(MessageRepository $messageRepository,
                                UserRepository $userRepository)
    {
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
    }

    //BREAD controller action pattern
    /**
     * Display list of messages from conversation
     *
     * @Route("/{id}", name="browse")
     * @param GroupConversation $groupConversation
     */
    public function browse($id,CookieGenerator $cookieGenerator): Response {

        $messages = $this->messageRepository->findMessageByConversationId($id);

        $response = $this->render('message/browse.html.twig', [
            'conversationId' => $id,
            'messages' => $messages,
        ]);

        $response->headers->setCookie($cookieGenerator->generate());
        return $response;
    }

//    /**
//     * Display one message
//     *
//     * @Route("/message/{id}", name="message_read", requirements={"id" : "\d+"})
//     */
//    public function read(MessageRepository $messageRepository): Response
//    {
//
//    }

    /**
     * Create new message
     *
     * @Route("/{id}/add", name="add", requirements={"id" : "\d+"})
     */
    public function add(Request $request,
                        HubInterface $hub,
                        CookieGenerator $cookieGenerator,
                        GroupConversation $groupConversation): Response
    {
        $message = new Message();
        $em      = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user    = $this->getUser();

        $message->setContent("Nouveau message! - " . rand(0,10000));
        $message->setSeen(false);
        $message->setCreated(new \DateTime());
        $message->setUpdated(new \DateTime());
        $message->setUser($this->userRepository->findOneBy(['id' => 2]));

        $groupConversation->addMessage($message);

        $em->getConnection()->beginTransaction();
        try {
            $em->persist($message);
            $em->flush();
            $em->flush();
            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }

        $this->addFlash('success', "Nouveau message ajouté !");

        $update = new Update(
            '/messages/1', //IRI, the topic being updated, can be any string usually URL
            json_encode(['message' => 'Nouveau message']), //the content of the update, can be anything
            true, //private
            //'1234',//
            //'message'
        );

        //PUBLISHER JWT : doit contenir la liste des conversations dans lesquels il peut publier conf => mercure.publish
        //SUBSCRIBER JWT: doit contenir la liste des conversations dans lesquels il peut recevoir conf => mercure.subcribe
        //dd($update);

        $hub->publish($update);

        $response = $this->redirectToRoute("messages_browse", ["id" => 1]);
        $response->headers->setCookie($cookieGenerator->generate());
        return $response;
    }

//    /**
//     * Delete message
//     *
//     * @Route("/{id}/delete", name="delete", requirements={"id" : "\d+"}, methods={"DELETE"})
//     */
//    public function delete(): Response
//    {
//
//    }
}