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

        $this->denyAccessUnlessGranted('ROLE_USER');

        $messages = $this->messageRepository->findMessageByConversationId($groupConversation->getId());

        $response = $this->render('message/browse.html.twig', [
            'conversation' => $groupConversation,
            'messages' => $messages,
        ]);

        return $response;
    }

    /**
     * Create new message
     *
     * @Route("/{id}/add", name="add", requirements={"id" : "\d+"})
     */
    public function add(Request $request,
                        HubInterface $hub,
                        GroupConversation $groupConversation): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $message = new Message();

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);
        $content = $request->get('message-box', null);

        if ($content) {

            $message->setCreated(new \DateTime('now'));
            $message->setUpdated(new \DateTime('now'));
            $message->setContent($content);
            $message->setMine(true);
            $message->setSeen(false);

            //By default is set to group admin, custom this with connected user
            $message->setUser($groupConversation->getAdmin());
            $groupConversation->addMessage($message);

            try {
                $date   = new \DateTime('now');
                $update = new Update(
                    '/messages/' . $groupConversation->getId(), //IRI, the topic being updated, can be any string usually URL
                    json_encode([
                        'conversation'  => 'Nouveau message conversation :' . $groupConversation->getName(),
                        'message'       => $content,
                        'from'          => $groupConversation->getAdmin()->getUsername(),
                        'to'            => $groupConversation->getUsers(),
                        'date'          => $date->format('H:i'),
                    ]), //the content of the update, can be anything
                    $groupConversation->getPrivate(), //private
                    'message-' . Uuid::v4(),//mercure id
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
        }

        return $this->redirectToRoute('messages_browse', ['groupConversation' => $groupConversation->getId()] );
    }

    /**
     * Ping mercure
     * @Route("/{id}/ping", name="ping")
     */
    public function ping(Request $request, HubInterface $hub)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $update = new Update(
            '/ping/' .  $request->get('id'), //IRI, the topic being updated, can be any string usually URL
            json_encode(['message' => 'pinged !']), //the content of the update, can be anything
            false, //private
            'ping-' . Uuid::v4(), //mercure id
            'ping'
        );

        $hub->publish($update);

        return $this->redirectToRoute('messages_browse', ['groupConversation' => $request->get('id')]);
    }
}