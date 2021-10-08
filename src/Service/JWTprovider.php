<?php

namespace App\Service;

use App\Entity\User;
use DateTimeImmutable;
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

    public function __construct(string $secret, TokenStorageInterface $tokenStorage)
    {
        $this->secret       = $secret;
        $this->tokenStorage = $tokenStorage;
    }


    public function getJwt(): string
    {
        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($this->secret));
        $token  = $config->builder()
            ->withClaim('mercure', [
                'subscribe' => ['*'],
                'publish' => ['*']
            ])
            // Builds a new token
            ->getToken($config->signer(), $config->signingKey());

        //dd( $token->toString());
        return $token->toString();
    }
}