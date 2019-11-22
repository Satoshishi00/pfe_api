<?php

namespace App\Controller;

use App\Entity\Classroom;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ClassroomController extends AbstractController
{
    /**
     * @Route("/classroom/new", name="/classroom/new", methods={"PUT"})
     */
    public function addClassroom(Request $request)
    {

        //TODO : get user infos by cookie
        $id = $request->headers->get('id');
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneById($id);

        $classroom_name = $request->query->get('group_name');

        $classroom = new Classroom();
        
        $classroom->setLeader($user);
        $classroom->setName($classroom_name);
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($classroom);
        //dd($group);
        $entityManager->flush();

        return new JsonResponse([
            "message" => "Votre classe a bien été créé",
            ],
            201);
    }

    /**
     * @Route("/classroom/show", name="/classrooom/show", methods={"GET"})
     */
    public function showClassroom(Request $request)
    {
        //TODO : get user infos by cookie
        $id = $request->headers->get('id');
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneById($id);


        $classrooms = $user->getClassrooms();
        dd($classrooms);

        /*return new JsonResponse([
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
             200);*/
    }
}
