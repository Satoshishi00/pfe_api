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

        //die($event);
        if ($event->isMasterRequest()) {
            /*$request = $event->getRequest();
            $brainer_id = $request->request->get("brainer_id");
            $brainer_pepper = $request->request->get("brainer_pepper");
            //Récupération de l'utilisateur depuis l'id
            $repository = $this->getDoctrine()->getRepository(User::class);
            $user = $repository->findOneById($brainer_id);
            //On compare les token 'pepper' (celui en cookie et celui associé de l'utilisateur)
            if($user->getPepper() === $brainer_pepper) {
                $response = new Response();
                $message =  new JsonResponse(["logout" => "true"], 200);
                $response->setContent($message);
                $event->setResponse($response);
                
            }
            $response = new Response();
            $message =  new JsonResponse(["logout" => "true"], 200);
            $response->setContent($message);
            $event->setResponse($response);*/

            return;
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
                $user = $user ? $user : "toto";


                $response = new Response();
                $message =  new JsonResponse(["id" => $brainer_id], 200);
                $response->setContent($message);
                $event->setResponse($response);
            }
            //Autrement, on redirige l'utilisateur vers la page de connexion
          
            

            /*$brainer_id = $request->request->get("brainer_id");
            //$brainer_pepper = $request->request->get("brainer_pepper");
            //Récupération de l'utilisateur depuis l'id
            $repository = $this->_em->getRepository(User::class);
            $user = $repository->findOneById($brainer_id);
            $response = new Response();
            $message =  new JsonResponse(["logout" => "true"], 200);
            $response->setContent($message);
            $event->setResponse($response);*/
        }
       /* $request = $event->getRequest();
        $brainer_id = $request->request->get("brainer_id");
        //$brainer_pepper = $request->request->get("brainer_pepper");
        //Récupération de l'utilisateur depuis l'id
        $repository = $this->_em->getRepository(User::class);
        $user = $repository->findOneById($brainer_id);
        $response = new Response();
        $message =  new JsonResponse(["logout" => "true"], 200);
        $response->setContent($message);
        $event->setResponse($response);*/
        
    }
}