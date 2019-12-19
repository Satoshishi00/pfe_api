<?php

namespace App\Controller;

use App\Entity\Qcm;
use App\Entity\Question;
use App\Entity\User;
use App\Form\QCMFormType;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class QcmController extends AbstractController
{
    /**
     * @Route("/qcm/new", name="qcm/new")
     * @return Response
     */
    public function index(Request $request) : Response
    {

        $qcm = new QCM();

        //TODO : get user infos by cookie
        $id = $request->headers->get('id');
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneById($id);
        
        $qcm_name = $request->query->get('qcm_name');
        if (strlen($qcm_name) > 255){
            return new JsonResponse([
                "error" => "La nom de qcm doit faire au maximum 255 caractères",
            ],
            400);
        }
        $qcm_description= $request->query->get('qcm_description');
        if (strlen($qcm_description) > 255){
            return new JsonResponse([
                "error" => "La description du qcm doit faire au maximum 255 caractères",
            ],
            400);
        }

        $user->setNbQcm($user->getNbQcm() + 1);
        $qcm->setName($qcm_name);
        $qcm->setDescription($qcm_description);
        $qcm->setIdCreator($user);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->persist($qcm);
        $entityManager->flush();

        //Traitement des questions/réponses
        //Initialisation de la question
        $i = 1;
        $current_question = $request->query->get('question'.$i);
        $current_advice = $request->query->get('q'.$i.'_advice');
        while (!is_null($current_question)){
            //Ajout de la question
            $question = new Question();
            $question->setIdQcm($qcm);
            $question->setQuestionResponse($current_question);
            $question->setAdvice($current_advice);
            $entityManager->persist($question);

            $qcm->setNbQuestions($qcm->getNbQuestions() + 1);
            $user->setPoints($user->getPoints() + 7);
            $entityManager->persist($qcm);
            $entityManager->flush();

            //Initialisation
            $j = 1;
            $current_answer = $request->query->get('q'.$i.'_rep'.$j);
            $current_good_rep = $request->query->get('q'.$i.'_ans'.$j);
            while (!is_null($current_answer) && !is_null($current_good_rep)){
                //Ajout de le question
                $answer = new Question();
                $answer->setIdQcm($qcm);
                $answer->setQuestionResponse($current_answer);
                $answer->setParent($question->getId());
                $answer->setGoodRep(($current_good_rep == "true" || $current_good_rep == 1) ? 1 : 0);
                $entityManager->persist($answer);
                $entityManager->flush();
                
                //Incrementation et recherche de la réponse suivante
                $j++;
                $current_answer = $request->query->get('q'.$i.'_rep'.$j);
                $current_good_rep = $request->query->get('q'.$i.'_ans'.$j);
            }
            //Incrementation et recherche de la question suivante
            $i++;
            $current_question = $request->query->get('question'.$i);
            $current_advice = $request->query->get('q'.$i.'_advice');
        }


        return new JsonResponse([
            "message" => "Le qcm a bien été créé",
        ],
        201);
    }

    /**
     * @Route ("/qcm/show/all", name="qcm/show/all")
     * @param Request $request
     * @return Response
     */
    public function showAll(Request $request): Response
    {
        $limit = $request->query->get('limit');
        $page_number = $request->query->get('page_number');

        //LThe first page is the number 1
        $qcms = $this->getDoctrine()->getRepository(Qcm::class)->findByLastsQcmLimitPage($limit, $page_number);
        
        foreach($qcms as $qcm){
            $json_qcm[] = [
                "id" => $qcm->getId(),
                "name" => $qcm->getName(),
                "description" => $qcm->getDescription(),
                "nb_questions" => $qcm->getNbQuestions(),
                "stats" => $qcm->getStats(),
                "nb_done" => $qcm->getNbDone(),
                "updated_at" => $qcm->getUpdatedAt(),
                "created_at" => $qcm->getCreatedAt(),
            ];
        }

        return new JsonResponse($json_qcm, 200);
    }


    /**
     * @Route ("/qcm/{id_qcm}", name="qcm/id")
     * @return Response
     */
    public function show($id_qcm): Response
    {
        //Récupération des questions et réponses du qcm
        $questions = $this->getDoctrine()->getRepository(Question::class)->findBy(['id_qcm' => $id_qcm]);

        //Récupération du nom et de la description du qcm
        $qcm = $this->getDoctrine()->getRepository(Qcm::class)->findBy(['id' => $id_qcm])[0];

        $json_questions[0] = [
            "qcm_id" => $qcm->getId(),
            "qcm_name" => $qcm->getName(),
            "qcm_description" => $qcm->getDescription(),
        ];

        //Pour chaque élément du tableau on a un sous tableau dont le premier élément est la question et les suivants les réponses
        $cpt = 0;
        for($i=0; $i<count($questions); $i++){
            if($questions[$i]->getParent() == null){
                $cpt++;
                $json_questions[$cpt][] = [
                    "id" => $questions[$i]->getId(),
                    "question_response" => $questions[$i]->getQuestionResponse(),
                    "parent" => $questions[$i]->getParent(),
                ];
            } elseif( $questions[$i]->getParent() > 0) {
                $json_questions[$cpt][] = [
                    "id" => $questions[$i]->getId(),
                    "question_response" => $questions[$i]->getQuestionResponse(),
                    "parent" => $questions[$i]->getParent(),
                ];
            } else {
                $json_questions[$cpt][] = [
                    "error" => "affirmatif! on a une erreur!"
                ];
            }
        }
        
        return new JsonResponse($json_questions, 200);
    }


    /**
     * @Route ("/qcm/{id_qcm}/result", name="qcm/id_qcm/result")
     * @return Response
     */
    public function checkReponse($id_qcm): Response
    {
        $questions = $this->getDoctrine()->getRepository(Question::class)->findBy(['id_qcm' => $id_qcm]);

        //Récupération du nom et de la description du qcm
        $qcm = $this->getDoctrine()->getRepository(Qcm::class)->findBy(['id' => $id_qcm])[0];

        $json_questions[0] = [
            "qcm_id" => $qcm->getId(),
            "qcm_name" => $qcm->getName(),
            "qcm_description" => $qcm->getDescription(),
        ];

        //Pour chaque élément du tableau on a un sous tableau dont le premier élément est la question et les suivants les réponses
        $cpt = 0;
        for($i=0; $i<count($questions); $i++){
            if($questions[$i]->getParent() == null){
                $cpt++;
                $json_questions[$cpt][] = [
                    "id" => $questions[$i]->getId(),
                    "question_response" => $questions[$i]->getQuestionResponse(),
                    "parent" => $questions[$i]->getParent(),
                    "good_rep" => $questions[$i]->getGoodRep(),
                    "advice" => $questions[$i]->getAdvice(),
                ];
            } elseif( $questions[$i]->getParent() > 0) {
                $json_questions[$cpt][] = [
                    "id" => $questions[$i]->getId(),
                    "question_response" => $questions[$i]->getQuestionResponse(),
                    "parent" => $questions[$i]->getParent(),
                    "good_rep" => $questions[$i]->getGoodRep(),
                    "advice" => $questions[$i]->getAdvice(),
                ];
            } else {
                $json_questions[$cpt][] = [
                    "error" => "affirmatif! on a une erreur!"
                ];
            }
        }
        
        /*foreach($questions as $question){
            $json_questions[] = [
                "id" => $question->getId(),
                "question_response" => $question->getQuestionResponse(),
                "parent" => $question->getParent(),
                "good_rep" => $question->getGoodRep(),
                "advice" => $question->getAdvice(),
            ];
        }*/

        return new JsonResponse($json_questions, 200);
    }

    /**
     * @Route ("/qcm/{id_qcm}/remove", name="qcm/id_qcm/remove")
     * @return Response
     */
    public function removeQcm($id_qcm): Response
    {
        $qcm = $this->getDoctrine()->getRepository(Qcm::class)->findBy(['id' => $id_qcm])[0];
        //dd($qcm);
        $questions = $this->getDoctrine()->getRepository(Question::class)->findBy(['id_qcm' => $id_qcm]);
        //dd($questions);
        $em = $this->getDoctrine()->getManager();
        foreach($questions as $question){
            //dd($question);
            $em->remove($question);
        }
        $em->remove($qcm);
        $em->flush();
   
        return new JsonResponse([
            "message" => "Le qcm a bien été supprimé",
        ],
        201);
    }

}