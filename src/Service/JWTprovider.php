<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class JWTprovider implements TokenProviderInterface
{

    /**
     * @var string
     */
    private $secret;

    private $tokenStorage;

    private $em;

    public function __construct(string $secret,
                                TokenStorageInterface $tokenStorage,
                                EntityManagerInterface $em)
    {
        $this->secret       = $secret;
        $this->tokenStorage = $tokenStorage;
        $this->em           = $em;
    }

    public function getJwt(): string
    {
        //get all conversation by user
        /**
         * @var User $user
         */
        $subscribe = [];
        //user test
        $user = $this->em->getRepository(User::class)->find(1);
        $conversations = $user->getConversations()->getValues();

        //save all sub/pub conversations
        foreach ($conversations as $conversation) {
            $subscribe[] =  '/messages/' . $conversation->getId();
        }
        $subscribe[] = '/ping/{id}';

        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($this->secret));
        $token  = $config->builder()
            ->withClaim('mercure', [
                'subscribe' => $subscribe,
                'publish' => $subscribe
            ])
            // Builds a new token
            ->getToken($config->signer(), $config->signingKey());

        //dd( $token->toString());
        return $token->toString();
    }
}