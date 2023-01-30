<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AbstractController
{
    #[Route('/', name: 'auth_demo')]
    #[Template('auth/auth_demo.html.twig')]
    public function authDemo()
    {
    }

    #[Route('/auth', name: 'app_auth')]
    #[Template('auth/index.html.twig')]
    public function index(Request $request)
    {
        // Manual: https://github.com/stevenmaguire/oauth2-keycloak

        $session = $request->getSession();

        $error = 0;
        $errorMessage = '';
        $userName = '';

        $provider = new \Stevenmaguire\OAuth2\Client\Provider\Keycloak([
            'authServerUrl'         => $this->getParameter('app.oidc.authServerUrl'),
            'realm'                 => $this->getParameter('app.oidc.realm'),
            'clientId'              => $this->getParameter('app.oidc.clientId'),
            'clientSecret'          => $this->getParameter('app.oidc.clientSecret'),
            'redirectUri'           => $this->getParameter('app.oidc.redirectUri'),
            'version'               => $this->getParameter('app.oidc.version'),
        ]);

        if (!isset($_GET['code'])) {
            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl();
            $session->set('oauth2state', $provider->getState());
            header('Location: ' . $authUrl);
            exit;

            // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $session->get('oauth2state'))) {
            $session->remove('oauth2state');
            exit('Invalid state, make sure HTTP sessions are enabled.');
        } else {
            // Try to get an access token (using the authorization code grant)
            try {
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);
            } catch (Exception $e) {
                return [
                    'error' => 1,
                    'errorMessage' => 'Failed to get access token: ' . $e->getMessage(),
                ];
            }

            // Optional: Now you have a token you can look up a users profile data
            try {
                // We got an access token, let's now get the user's details
                $user = $provider->getResourceOwner($token);

                // Use these details to create a new profile
                $userName = $user->getName();
            } catch (Exception $e) {
                return [
                    'error' => 1,
                    'errorMessage' => 'Failed to get resource owner: ' . $e->getMessage(),
                ];
            }

            // Use this to interact with an API on the users behalf
            //echo $token->getToken();
        }

        return [
            'userName' => $userName,
            'error' => $error,
            'errorMessage' => $errorMessage,
        ];
    }

    #[Route('/authcallback', name: 'app_auth_callback')]
    #[Template('auth/callback.html.twig')]
    public function callback()
    {
        return [
            'userName' => 'User Name (default)',
        ];
    }
}
