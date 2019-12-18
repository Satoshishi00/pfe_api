<?php
namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;


class RequestListener
{

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onKernelRequest(RequestEvent $event)
    {

        if ($event->isMasterRequest()) {

            $request = $event->getRequest();
            $security = $request->headers->get("security");
            //S'il n'y a pas besoin de sérutié on laisse la requete se propager
            if($security == "false") {
                return;
            } 
            //Autrement, on regarde les informations à l'intérieur du cookie
            else {
                //Si l'id et le pepper de l'utilisateur correspondent bien, on laisse l'utilisateur récupérrer les informations de sa requête
                $brainer_id = $request->headers->get("id");
                $brainer_pepper = $request->headers->get("pepper");
                $repository = $this->em->getRepository(User::class);
                $user = $repository->findOneById($brainer_id);
                //Si l'utilisateur est existe (donc que l'id fourni correspond à un utilisateur)
                //et que le pepper fourni est bien celui associé à l'utilisateur
                //On fait confiance et on laisse la requête se propager
                if ($user && $user->getPepper() === $brainer_pepper){
                    // $response = new Response("");
                    // $response->setContent($user);
                    return;
                }

                //Autrement on annonce à l'utilisaeur qu'il n'est pas connecté
                $response = new Response("");
                $message =  new JsonResponse(["error" => "Vous ne semblez pas être connecté"], 500);
                $response->setContent($message);
                $event->setResponse($response);
            }
        }
        
    }
}