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

        $user->setNbClasses($user->getNbClasses() + 1);
        $user->setPoints($user->getPoints() + 30);
        // dd($user);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($classroom);
        $entityManager->persist($user);
        //dd($group);
        $entityManager->flush();

        return new JsonResponse([
            "message" => "Votre classe ". $classroom_name ." a bien été créé",
            "nbClasses" => $user->getNbClasses(),
            "nbPoints" => $user->getPoints(),
            ],
            201);
    }

    /**
     * @Route("/classroom/shows", name="/classrooom/shows", methods={"GET"})
     */
    public function showClassroomsByUser(Request $request)
    {
        //TODO : get user infos by cookie
        $id = $request->headers->get('id');
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneById($id);


        $classrooms = $this->getDoctrine()->getRepository(Classroom::class)->findByClassesByUserId($id);

        foreach($classrooms as $classroom){
            $jsonResponse[] = [
                "id" => $classroom->getId(),
                "name" => $classroom->getName(),
            ];
        }

        return new JsonResponse($jsonResponse,
             200);
    }
}
