
# Mise en place du POC Mercure Chat
## Composer & BDD
1. Installer les bundles & dépendances :`composer install --dev & composer update -W`
2. Créer la BDD (DATABASE_URL doit être renseigné préalablement dans le fichier .env.local) : ``php bin/console doctrine:database:create``
3. Exécuter les migrations :``php bin/console d:mi:mi``
4. Éxécuter les fixtures : ``php bin/console doctrine:fixtures:load``

## Mercure

1. Télécharger Mercure pour votre environment => [ici](https://github.com/dunglas/mercure/releases)
2. Créer un fichier ``.env.local``
3. Exemple ``.env.local``

```
#BDD
DATABASE_URL="mysql://root@localhost:3307/poc_chat?serverVersion=mariadb-10.4.13"

###> symfony/mercure-bundle ###
# See https://symfony.com/doc/current/mercure.html#configuration
# The URL of the Mercure hub, used by the app to publish updates (can be a local URL)
MERCURE_URL=https://localhost:3000/.well-known/mercure
MERCURE_DEBUG=true
# The public URL of the Mercure hub, used by the browser to connect
MERCURE_PUBLIC_URL=http://localhost:3000/.well-known/mercure
# The secret used to sign the JWTs
MERCURE_JWT_KEY="YourJwtKey"
MERCURE_ALLOW_ANONYMOUS=1
# a list of origins allowed to publish (only applicable when using cookie-based auth)
MERCURE_PUBLISH_URL=http://localhost:3000/.well-known/mercure
MERCURE_PUBLISH_ALLOWED_ORIGINS=*
# a space-separated list of allowed CORS origins, can be * for all
MERCURE_CORS_ALLOWED_ORIGINS=*
###< symfony/mercure-bundle ###
```
### Créer un batch por Windows
Pour gérer les variables d'environment à l'execution de mercure.exe, nous pouvons créer un fichier ``launcher_mercure.bat``, à placer au même niveau que mercure.exe.

Il contiendra les variables suivantes (à adapter en fonction de votre config) :
```
set JWT_KEY=YourJwtKey
set ADDR=localhost:3000
set ALLOW_ANONYMOUS=1
set PUBLISH_ALLOWED_ORIGINS=*
set CORS_ALLOWED_ORIGINS=*
.\mercure.exe
```
Documentation config Mercure => [Configuration server spec](https://mercure.rocks/docs/hub/config)

Si vous utilisez un cookie ou un ``Authorization HTTP header`` => [Authorization spec](https://mercure.rocks/spec#authorization)



## Twig
### Création d'une variable globale Twig MERCURE_PUBLISH_URL

Cette variable permet d'être utilisée lors de la communication avec le hub mercure, via javascript.
Elle retourne l'url du hub utilisé pour la communication.

Le fichier à modifier est situé dans : ```/config/packages/twig.yaml```
```
twig:
    globals:
        mercure_publish_url: '%env(MERCURE_PUBLISH_URL)%'
```
### Gestion en Javascript des réponses renvoyées par Mercure

Présent dans le fichier de template : ```templates/base.html.twig```
```
<script type='application/javascript'>
// Extract the hub URL from the Link header
const url   = new URL('{{ mercure_publish_url }}');
let pathUri = window.location.pathname.substring(1);

//Définir les topic a écouter : utilisation du template messages/{id} pour écouter plusieur sujet
url.searchParams.append('topic', '/messages/{id}');
//url.searchParams.append('topic', '/ping/{id}');

// listen to the HUB.
//MODE authorization, cookie or header
const eventSource = new EventSource(url, {withCredentials: true});
//MODE anonymous
//const eventSource = new EventSource(url);

eventSource.onmessage = (event) => {

    console.dir(event);
    console.debug(location.pathname);

    document.querySelector('h1').insertAdjacentHTML('afterend', `<div class="alert alert-success">Ping !</div> `);
    window.setTimeout( () => {
        const alert = document.querySelector('.alert');
        alert.parentNode.removeChild(alert);
    }, 2000)

}
</script>
```

## Tests
### Postman

Pour test la communication avec mercure voici une conf en JSON à imposter.

* Il faudra adapter le bearer token value symbolisé par le pattern ``[REPLACE_BY_YOUR_JWT]`` présent dans le fichier `test_postman.json`. 

* Importez dans Postman le fichier : `test_postman.json` présent à la racine du site.

Vous pouver générer un token JWT sur [jwt.io](jwt.io).

1. Exemple de PAYLOAD :
```
{
  "mercure": {
    "subscribe": ["*"],
    "publish": ["*"]
  }
}
```
    
2. VERIFY SIGNATURE : renseignez votre `MERCURE_JWT_KEY` (dispo dans .env & launcher_mercure.bat sous windows)