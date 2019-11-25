<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        //Determiner si l'utilisateur est connecté avec salt et id
        //Si c'est le cas renvoyer user infos
        $id = $request->headers->get('brainer-id');
        $pepper = $request->headers->get('brainer-pepper');
        if (!is_null($id) && !is_null($pepper)){
            $repository = $this->getDoctrine()->getRepository(User::class);
            $user = $repository->findOneById($id);
            if ($user) {
                if ($user->getPepper() == $pepper){
                    return new JsonResponse([
                    "id" => $user->getId(),
                    "email" => $user->getEmail(),
                    "username" => $user->getUsername(),
                    "password" => $user->getPassword(),
                    "nb_classes" => $user->getNbClasses(),
                    "roles" => $user->getRoles(),
                    "nb_qcm" => $user->getNbQcm(),
                    "nb_flash_cards" => $user->getNbFlashCards(),
                    "points" => $user->getPoints(),
                    "premium" => $user->getPremium(),
                    "updated_at" => $user->getUpdatedAt()->format('H:i d/m/Y'),
                    "created_at" => $user->getCreatedAt()->format('d/m/Y'),
                    "image" => $user->getImage()->getImageName(),
                    "pepper" => $user->getPepper(),
                ],
                    200);
                } else {
                    //Le pepper du header est faux, quelqu'un a probablement essayé d'introduire des infos ?
                    return new JsonResponse([
                        "error" => "Vous essayez de me pirater ?",
                    ],
                    401);
                }
                
            }
        }
         

        //Si on ne peut pas savoir si l'utilisateur est connecté on essaie de le connecter
        $email = $request->query->get('email');
        $password = $request->query->get('password');
         
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneByEmail($email);
        
        //Invalid email
        if (is_null($user)){
            return new JsonResponse([
                "error" => "Utilisateur email ou mot de passe invalide",
            ],
            400);
        }
        
        //Invalid password
        if (!password_verify($password, $user->getPassword())){
            return new JsonResponse([
                "error" => "Utilisateur email ou mot de passe invalide",
            ],
            400);
        }

        return new JsonResponse([
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "username" => $user->getUsername(),
                "password" => $user->getPassword(),
                "nb_classes" => $user->getNbClasses(),
                "roles" => $user->getRoles(),
                "nb_qcm" => $user->getNbQcm(),
                "nb_flash_cards" => $user->getNbFlashCards(),
                "points" => $user->getPoints(),
                "premium" => $user->getPremium(),
                "updated_at" => $user->getUpdatedAt()->format('H:i d/m/Y'),
                "created_at" => $user->getCreatedAt()->format('d/m/Y'),
                "image" => $user->getImage()->getImageName(),
                "pepper" => $user->getPepper(),
            ],
                200);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }


    /**
     * @Route("/mdp/forgotten", name="/mdp/forgotten")
     */
    public function forgottenPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $token = $request->query->get('token');
        $email = $request->query->get('email');

        //get user by email
        $user = $this->getDoctrine()->getRepository(User::class)->findOneByEmail($email);


        //Compare token and user tocken
        if (is_null($user) || $user->getTokenPassword() != $token){
            return new JsonResponse([
                "error" => "Vous avez un problème avec votre url. Contacter un administrateur si vous rencontrez un problème."
            ],
            404);
        }

        //proposer nouveau mdp
        $password1 = $request->query->get('password1');
        $password2 = $request->query->get('password2');

        if($password1 != $password2){
            return new JsonResponse([
                "error" => "Les mots de passes sont différents",
            ],
            400);
        }

        $regex_password = "(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#&é\"'§è!çà°\-_*\$€ù%`£=+:;.,<>])^[a-zA-Z\d@#&é\"'§è!çà°\-_*\$€ù%`£=+:;.,<>]{8,32}$";
        if(!preg_match('/^'.$regex_password.'$/', $password1)){
            return new JsonResponse([
            "error" => "Votre mot de passe est invalide. Il doit faire entre 8 et 32 charactères et contenir au moins une minuscule, une majuscule, un chiffre et un caractère spéciaux (@#é\"'§è!çà°_*\$€ù%`£=+:;.,<>)"],
             400);
        }

        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user,
                $password1
            )
        );

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            "id" => $user->getId(),
            "email" => $user->getEmail(),
            "username" => $user->getUsername(),
            "password" => $user->getPassword(),
            "nb_classes" => $user->getNbClasses(),
            "roles" => $user->getRoles(),
            "nb_qcm" => $user->getNbQcm(),
            "nb_flash_cards" => $user->getNbFlashCards(),
            "points" => $user->getPoints(),
            "premium" => $user->getPremium(),
            "updated_at" => $user->getUpdatedAt()->format('H:i d/m/Y'),
            "created_at" => $user->getCreatedAt()->format('d/m/Y'),
            "image" => $user->getImage()->getImageName(),
        ],
             201);
    }
}