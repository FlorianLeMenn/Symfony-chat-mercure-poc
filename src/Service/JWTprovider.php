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
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class JWTprovider implements TokenProviderInterface
{

    /**
     * @var string
     */
    private $secret;

    private $tokenStorage;

    private $em;

    /**
     * @var Security
     */
    private Security $security;

    public function __construct(string $secret,
                                TokenStorageInterface $tokenStorage,
                                EntityManagerInterface $em,
                                Security $security)
    {
        $this->secret       = $secret;
        $this->tokenStorage = $tokenStorage;
        $this->em           = $em;
        $this->security     = $security;
    }

    public function getJwt(): string
    {
        $subscribe = [];

        $user = $this->security->getUser();

        if($user) {
            $conversations = $user->getConversations()->getValues();
            //save all sub/pub conversations
            if($conversations) {
                foreach ($conversations as $conversation) {
                    $subscribe[] =  '/messages/' . $conversation->getId();
                }
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
        return "";
    }
}