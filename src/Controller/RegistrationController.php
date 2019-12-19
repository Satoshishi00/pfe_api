<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Media;
use App\Form\RegistrationFormType;
use App\Security\StubAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Bridge\Google\Smtp\GmailTransport;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;


class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        

        $email = $request->query->get('email');
        //Control $email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return new JsonResponse([
            "error" => "L'adresse : '".$email."' est invalide"],
             200);
        }
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneByEmail($email);
        if (!is_null($user)){
            return new JsonResponse([
                "error" => "Un compte avec cet email existe déjà",
            ],
            400);
        }

        $username = $request->query->get('username');
        //Control $username
        if(!preg_match('/^[a-zA-Z0-9]{5,}$/', $username)){
            return new JsonResponse([
            "error" => "Votre pseudo : '".$username."' est invalide. Il doit faire au moins 5 charactères et contenir seulement des minuscules, des majuscules ou des chiffres"],
             400);
        }

        $plainPassword = $request->query->get('plainPassword');
        //Control $plainPassword
        $regex_password = "(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#&é\"'§è!çà°\-_*\$€ù%`£=+:;.,?<>])^[a-zA-Z\d@#&é\"'§è!çà°\-_*\$€ù%`£=+:;.,<>]{8,32}$";
        if(!preg_match('/^'.$regex_password.'$/', $plainPassword)){
            return new JsonResponse([
            "error" => "Votre mot de passe est invalide. Il doit faire entre 8 et 32 charactères et contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial (@#é\"'§è!çà°_*\$€ù%`£=+:;.,?<>)"],
             400);
        }


        $user = new User();
        // encode the plain password
        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user,
                $plainPassword
            )
        );
        $user->setEmail($email);
        $user->setUsername($username);

        //Pepper creation
        $salt = $user->getId().$user->getEmail().$user->getUpdatedAt()->format('Y-m-d H:i:s');
        $pepper = sha1($salt);
        $user->setPepper($pepper);

        //Token_password creation
        $token = base64_encode(sha1($pepper.$user->getUpdatedAt()->format('Y-m-d H:i:s')));
        $user->setTokenPassword($token);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        //Add user default Image
        $media = new Media();
        $media->setImageName(strtoupper($user->getUsername()[0]).'.png');
        $media->setUpdatedAt(new \DateTime('now'));
        $media->setIdUser($user);
        $entityManager->persist($media);
        $entityManager->flush();

        $user->setImage($media);
        
        

        //ATTENDRE SERVER DE PROD POUR METTRE EN PLACE LES MAILS
        //*****************************************************/
        //Envoie de mail de confirmation d'inscription
        /*$email = (new Email())
            ->from('titesteur@gmail.com')
            ->to(email)
            ->cc('bar@example.com')
            ->bcc('baz@example.com')
            ->replyTo('fabien@symfony.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Important Notification')
            ->text('Lorem ipsum...')
            ->html('<h1>Lorem ipsum</h1> <p>...</p>')
        ;

        $transport = new GmailTransport('titesteur@gmail.com', 'poPO98po');
        $mailer = new Mailer($transport);
        $mailer->send($email);*/

        return new JsonResponse([
            "id" => $user->getId(),
            "email" => $user->getEmail(),
            "username" => $user->getUsername(),
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
            "token_password" => $user->getTokenPassword(),
        ],
             201);
    }
}