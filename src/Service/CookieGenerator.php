<?php

namespace App\Service;

use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Component\HttpFoundation\Cookie;


class CookieGenerator
{
    private $secret;
    private $jwt;

    /**
     * @param string $secret
     * @param JWTprovider $JWTprovider
     */
    public function __construct(string $secret, JWTprovider $JWTprovider)
    {
        //JWT secret key : set in services.yaml
        $this->secret = $secret;
        //On load grace à l'autowiring le service qui génère le JWT token
        $this->jwt = $JWTprovider->getJwt();
    }

    public function generate(): Cookie
    {
        $domain = ($_ENV["APP_ENV"] == "dev")? 'localhost' : '.monsite.com';
        $secure = ($_ENV["APP_ENV"] == "dev")? false : true;
        $httpOnly = false;

//        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($this->secret));
//
//        $token  = $config->builder()
//            // Configures the issuer (iss claim)
//            //->issuedBy('https://flameup.com')
//            // Configures the audience (aud claim)
//            //->permittedFor('https://app.flameup.com')
//            // Configures the id (jti claim)
//            //->identifiedBy('4f1g23a12aa')
//            // Configures the time that the token was issue (iat claim)
//            //->issuedAt($now)
//            // Configures the time that the token can be used (nbf claim)
//            //->canOnlyBeUsedAfter($now->modify('+1 minute'))
//            // Configures the expiration time of the token (exp claim)
//            //->expiresAt($now->modify('+3 hour'))
//            // Configures a new claim, called "uid"
//            //@TODO :  gerer les chanels de souscription / publication
//            ->withClaim('mercure', [
//                'subscribe' => ['*'],
//                'publish' => ['*']
//            ])
//            // Configures a new header, called "foo"
//            //->withHeader('Access-Control-Allow-Origin', '*')
//            // Builds a new token
//            ->getToken($config->signer(), $config->signingKey());
//
//        //compagre JWT generation
        //dd($this->jwt);

        return Cookie::create(
            'mercureAuthorization',
            $this->jwt,
            0,
            '/.well-known/mercure',
            $domain,
            $secure,
            $httpOnly,
            false,
            'lax'
        );
    }
}
