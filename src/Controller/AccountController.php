<?php

namespace App\Controller;

use App\Entity\Option;
use App\Entity\Media;
use App\Entity\File;
use App\Entity\User;
use App\Entity\Property;
use App\Form\MediaFormType;
use App\Form\UserFormType;
use App\Form\PropertyType;
use App\Form\EditAccountFormType;
use App\Form\EditImageAccountFormType;
use App\Repository\PropertyRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class AccountController
 * @package App\Controller
 */
class AccountController extends AbstractController
{

    public function __construct(UserRepository $repository, ObjectManager $em)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * @Route("/account/infos", name="account/infos")
     */
    public function index(Request $request)
    {
        //TODO : take infos by cookie
        //récupération provisoire de l'utilisateur
        $id = $request->headers->get('id');
        $pepper = $request->headers->get('pepper');
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
                        "updated_at" => $user->getUpdatedAt(),
                        "created_at" => $user->getCreatedAt(),
                        "image" => $user->getImage()->getImageName(),
                        
                    ],
                        200);
                }
            }
        }        


        if (is_null($user)){
            return new JsonResponse([
                "error" => "Vous n'êtes pas connecté !",
            ],
            401);
        }
        $id = $request->headers->get('id');
        
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
                        "updated_at" => $user->getUpdatedAt(),
                        "created_at" => $user->getCreatedAt(),
                        "image" => $user->getImage()->getImageName(),
                        
                    ],
                        200);
                }
            }
        }
    }

    /**
     * @Route("/account/edit/password", name="account/edit/password", methods={"POST"})
     */
    public function editPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $old_password = $request->query->get('old_password');
        $new_password1 = $request->query->get('new_password1');
        $new_password2 = $request->query->get('new_password2');

        //test new_password
        if($new_password1 != $new_password2){
            return new JsonResponse([
            "error" => "Les nouveaux mots de passes que vous avez saisi sont différents l'un de l'autre."],
            400);
        }

        //test old_password
        //TODO : take user infos by cookie
        //récupération provisoire de l'utilisateur
        $id = $request->headers->get('id');
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneById($id);


        if (!password_verify($old_password, $user->getPassword())){
            return new JsonResponse([
                "error" => "Le mot de passe que vous avez saisie n'est pas celui associé actuellement à votre compte",
            ],
            400);
        }


        $regex_password = "(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#&é\"'§è!çà°\-_*\$€ù%`£=+:;.,?<>])^[a-zA-Z\d@#&é\"'§è!çà°\-_*\$€ù%`£=+:;.,<>]{8,32}$";
        if(!preg_match('/^'.$regex_password.'$/', $new_password1)){
            return new JsonResponse([
            "error" => "Votre mot de passe est invalide. Il doit faire entre 8 et 32 charactères et contenir au moins une minuscule, une majuscule, un chiffre et un caractère spéciaux (@#é\"'§è!çà°_*\$€ù%`£=+:;.,?<>)"],
             400);
        }

        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user,
                $new_password1
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
            "updated_at" => $user->getUpdatedAt(),
            "created_at" => $user->getCreatedAt(),
            "image" => $user->getImage()->getImageName(),
        ],
             201);
    }

    /**
     * @Route("/account/edit/email", name="account/edit/email")
     */
    public function editEmail(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        //dd($request->headers->all(), $request->getMethod());
        /*return new JsonResponse([
            "error" => $request
        ],400);*/
       

        $old_email = $request->query->get('old_email');
        $new_email1 = $request->query->get('new_email1');
        $new_email2 = $request->query->get('new_email2');

        

        //test new_email
        if($new_email1 != $new_email2){
            return new JsonResponse([
            "error" => "Les nouveaux emails que vous avez saisi sont différents l'un de l'autre."],
            400);
        }

        
        //test old_email
        //TODO : take user infos by cookie
        //récupération provisoire de l'utilisateur
        $id = $request->headers->get('id');
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneById($id);


        if ($old_email != $user->getEmail()){
            return new JsonResponse([
                "error" => "L'email '".$old_email."' que vous avez saisie n'est pas celui associé actuellement à votre compte",
            ],
            400);
        }

        if (!filter_var($new_email1, FILTER_VALIDATE_EMAIL)){
            return new JsonResponse([
            "error" => "L'adresse : '".$new_email1."' est invalide"],
             400);
        }

        $repository = $this->getDoctrine()->getRepository(User::class);
        $obj_user = $repository->findOneByEmail($new_email1);
        if (!is_null($obj_user)){
            return new JsonResponse([
                "error" => "Un compte avec cet email existe déjà",
            ],
            400);
        }

        $user->setEmail($new_email1);
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
            "updated_at" => $user->getUpdatedAt(),
            "created_at" => $user->getCreatedAt(),
            "image" => $user->getImage()->getImageName(),
        ],
             201);
    }

    /**
     * @Route("/account/edit/image", name="account/edit/image")
     */
    public function editImage(Request $request)
    {
        //TODO : take user infos by cookie
        //récupération provisoire de l'utilisateur
        $id = $request->headers->get('id');
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneById($id);


        //dd($request->files->get('image'));
        $media = $user->getImage();
        $media->setImageFile($request->files->get('image'));
        $media->setImageSize($request->query->get('image_size'));
        $media->setImageName($request->query->get('image_name'));
        //dd($media);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($media);
        $entityManager->flush();

        dd($user);
        //$media->setImageFile();

    }

    /**
     * @Route("/account/edit/username", name="account/edit/username")
     */
    public function editUsername(Request $request)
    {
        //TODO : take user infos by cookie
        //récupération provisoire de l'utilisateur
        $id = $request->headers->get('id');
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneById($id);


        $username = $request->query->get('username');

        
        if(!preg_match('/^[a-zA-Z0-9]{5,}$/', $username)){
            return new JsonResponse([
            "error" => "Votre pseudo : '".$username."' est invalide. Il doit faire au moins 5 charactères et contenir seulement des minuscules, des majuscules ou des chiffres"],
             400);
        }

        $user->setUsername($username);
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
            "updated_at" => $user->getUpdatedAt(),
            "created_at" => $user->getCreatedAt(),
            "image" => $user->getImage()->getImageName(),
        ],
             201);

    }

}
