<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use League\OAuth2\Client\Provider\Google;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class GoogleController extends AbstractController
{

    private $provider;

    public function __construct()
    {
       $this->provider=new Google([
         'clientId'          => $_ENV['GGL_ID'],
         'clientSecret'      => $_ENV['GGL_SECRET'],
         'redirectUri'       => $_ENV['GGL_CALLBACK'],
         'graphApiVersion'   => 'v18.0',
     ]);
    }

    #[Route('/google', name: 'app_google')]
    public function index(): Response
    {
        return $this->render('google/index.html.twig', [
            'controller_name' => 'GoogleController',
        ]);
    }

    #[Route('/ggl-login', name: 'ggl_login')]
    public function gglLogin(): Response
    {
         
        $helper_url=$this->provider->getAuthorizationUrl();
        return $this->redirect($helper_url);
    }


    #[Route('/ggl-callback', name: 'ggl_callback')]
    public function gglCallBack(UserRepository $userDb, EntityManagerInterface $manager): Response
    {
        //Récupérer le token
        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
        
        try {
            //Récupérer les informations de l'utilisateur
            
            $user=$this->provider->getResourceOwner($token);
            $user=$user->toArray();
            
            $email=$user['email'];
           //Vérifier si l'utilisateur existe dans la base des données
           $user_exist=$userDb->findOneByEmail($email);
           
           
           if($user_exist)
           {
               dd('Exist Success');
               return $this->render('google/index.html.twig');
            }
            
            else
            {
                $new_user=new User();
                $new_user
                ->setEmail($email)
                ->setPassword(sha1(str_shuffle('abscdop123390hHHH;:::OOOI')));
                
                $manager->persist($new_user);
                $manager->flush();
                dd('Save Success');
                return $this->render('google/index.html.twig');

           }


       } catch (\Throwable $th) {
        //throw $th;

          return $th->getMessage();
       }


    }
}
