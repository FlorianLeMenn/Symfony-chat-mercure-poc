<?php

namespace App\Controller;

use App\Entity\GroupConversation;
use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\CookieGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Uid\Uuid;


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

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(MessageRepository $messageRepository, UserRepository $userRepository, EntityManagerInterface $em)
    {
        $this->messageRepository    = $messageRepository;
        $this->userRepository       = $userRepository;
        $this->em                   = $em;
    }

    //BREAD controller action pattern
    /**
     * Display list of messages from conversation
     *
     * @Route("/{groupConversation}", name="browse")
     * @param GroupConversation $groupConversation
     */
    public function browse(GroupConversation $groupConversation, ?CookieGenerator $cookieGenerator): Response {

        $messages = $this->messageRepository->findMessageByConversationId($groupConversation->getId());

        $response = $this->render('message/browse.html.twig', [
            'conversation' => $groupConversation,
            'messages' => $messages,
        ]);

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
                        GroupConversation $groupConversation): Response
    {
        $message = new Message();
        $user = $this->userRepository->find(5);

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $message->setCreated(new \DateTime('now'));
            $message->setUpdated(new \DateTime('now'));
            $message->setSeen(false);

            //By default is set to group admin, custom this with connected user
            $message->setUser($groupConversation->getAdmin());
            $groupConversation->addMessage($message);

            try {

                $update = new Update(
                    '/messages/' . $groupConversation->getId(), //IRI, the topic being updated, can be any string usually URL
                    json_encode([
                        'message' => 'Nouveau message conversation :' . $groupConversation->getId(),
                        'from' => $groupConversation->getAdmin()->getId(),
                        'to'   => $groupConversation->getUsers(),
                        'date' => new \DateTime('now'),
                    ]), //the content of the update, can be anything
                    $groupConversation->getPrivate(), //private
                    'message-' . Uuid::v4(),//
                'message'
                );

            //PUBLISHER JWT : doit contenir la liste des conversations dans lesquels il peut publier conf => mercure.publish
            //SUBSCRIBER JWT: doit contenir la liste des conversations dans lesquels il peut recevoir conf => mercure.subcribe

                $hub->publish($update);
                $this->em->flush();
            }
            catch (\Exception $e) {
                dd($groupConversation);
                throw $e;
            }

            return $this->redirectToRoute('messages_browse', ['groupConversation' => $groupConversation->getId()] );
        }


        return $this->renderForm('message/_form/add.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Ping mercure
     * @Route("/{id}/ping", name="ping")
     */
    public function ping(Request $request,HubInterface $hub)
    {
        $update = new Update(
            '/ping/1', //IRI, the topic being updated, can be any string usually URL
            json_encode(['message' => 'Nouveau ping']), //the content of the update, can be anything
            false, //private
            'ping-'. Uuid::v4(),//
            'ping'
        );

        //PUBLISHER JWT : doit contenir la liste des conversations dans lesquels il peut publier conf => mercure.publish
        //SUBSCRIBER JWT: doit contenir la liste des conversations dans lesquels il peut recevoir conf => mercure.subcribe
        //dd($update);
        $hub->publish($update);

        return $this->redirectToRoute('messages_browse', ['groupConversation' => $request->get('id')]);
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