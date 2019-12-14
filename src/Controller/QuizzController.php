<?php

namespace App\Controller;

use App\Entity\Quizz;
use App\Entity\QuizzQuestion;
use App\Entity\User;
use App\Form\QCMFormType;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class QuizzController extends AbstractController
{
    /**
     * @Route("/quizz/new", name="quizz/new")
     * @return Response
     */
    public function index(Request $request) : Response
    {

        $quizz = new Quizz();

        //TODO : get user infos by cookie
        $id = $request->headers->get('id');
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneById($id);
        
        //dd("toto");
        $quizz_name = $request->query->get('quizz_name');
        //dd($qcm_name);
        if (strlen($quizz_name) > 255){
            return new JsonResponse([
                "error" => "La nom du quizz doit faire au maximum 255 caractères",
            ],
            400);
        }
        $quizz_description= $request->query->get('quizz_description');
        if (strlen($quizz_description) > 255){
            return new JsonResponse([
                "error" => "La description du quizz doit faire au maximum 255 caractères",
            ],
            400);
        }

        $user->setNbQuizz($user->getNbQuizz() + 1);
        $quizz->setName($quizz_name);
        $quizz->setDescription($quizz_description);
        $quizz->setCreator($user);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->persist($quizz);
        $entityManager->flush();

        //Traitement des questions/réponses
        //Initialisation de la question
        $i = 1;
        $current_question = $request->query->get('question'.$i);
        $rep1 = $request->query->get('q'.$i.'_rep1');
        $rep2 = $request->query->get('q'.$i.'_rep2');
        $rep3 = $request->query->get('q'.$i.'_rep3');
        $rep4 = $request->query->get('q'.$i.'_rep4');
        $right_answer = $request->query->get('q'.$i.'_right_answer');

        while (!is_null($current_question)){
            //Ajout de la question
            $question = new QuizzQuestion();
            $question->setQuizz($quizz);
            $question->setQuestion($current_question);
            $question->setRep1($rep1);
            $question->setRep2($rep2);
            $question->setRep3($rep3);
            $question->setRep4($rep4);
            $question->setRightAnswer($right_answer);

            $entityManager->persist($question);

            $quizz->setNbQuestions($quizz->getNbQuestions() + 1);
            $user->setPoints($user->getPoints() + 7);
            $entityManager->persist($quizz);
            $entityManager->flush();


            //Incrementation et recherche de la question suivante
            $i++;
            $current_question = $request->query->get('question'.$i);
            $rep1 = $request->query->get('q'.$i.'_rep1');
            $rep2 = $request->query->get('q'.$i.'_rep2');
            $rep3 = $request->query->get('q'.$i.'_rep3');
            $rep4 = $request->query->get('q'.$i.'_rep4');
            $right_answer = $request->query->get('q'.$i.'_right_answer');
        }


        return new JsonResponse([
            "message" => "Le quizz a bien été créé",
        ],
        201);
    }

    /**
     * @Route ("/quizz/show/all", name="quizz/show/all")
     * @param Request $request
     * @return Response
     */
    public function showAll(Request $request): Response
    {
        $limit = $request->query->get('limit');
        $page_number = $request->query->get('page_number');

        //LThe first page is the number 1
        $quizzs = $this->getDoctrine()->getRepository(Quizz::class)->findByLastsQuizzLimitPage($limit, $page_number);
        
        foreach($quizzs as $quizz){
            $json_quizz[] = [
                "id" => $quizz->getId(),
                "name" => $quizz->getName(),
                "description" => $quizz->getDescription(),
                "nb_questions" => $quizz->getNbQuestions(),
                "stats" => $quizz->getStats(),
                "nb_done" => $quizz->getNbDone(),
                "updated_at" => $quizz->getUpdatedAt(),
                "created_at" => $quizz->getCreatedAt(),
            ];
        }

        return new JsonResponse($json_quizz, 200);
    }


    /**
     * @Route ("/quizz/{id_quizz}/{questions_ids}", name="quizz/id")
     * @return Response
     */
    public function show($id_quizz, $questions_ids): Response
    {
        //Contrairement à un qcm, pour une quizz, je n'ai pas besoin de otutes le sinformations (listing des questions)
        //Mais seulement d'une question et de la liste des réponses possibles
        //Il me faut stocker la liste des questions auxquelles l'utilisateur a déjà répondu au sein d'un quizz
        //Puis récupérer aléatoirement une question parmis les restantes
        //Et à chaque soumission d'un réponse pa l'utilisateur, il me faut récupérer simultanément la réponse à la question en cours + la question suivante et les propositions qui lui sont associées.

        //Récupération des questions et réponses du quizz
        $questions = $this->getDoctrine()->getRepository(QuizzQuestion::class)->findBy(['quizz' => $id_quizz]);

        //Récupération du nom et de la description du quizz
        $quizz = $this->getDoctrine()->getRepository(Quizz::class)->findBy(['id' => $id_quizz])[0];

        $json_questions[0] = [
            "quizz_id" => $quizz->getId(),
            "quizz_name" => $quizz->getName(),
            "quizz_description" => $quizz->getDescription(),
        ];

        //Pour chaque élément du tableau on a un sous tableau dont le premier élément est la question et les suivants les réponses
        $cpt = 0;
        foreach ($questions as $question) {
            $cpt++;
            $json_questions[$cpt][] = [
                'question'.$cpt => $question->getQuestion(),
                'q'.$cpt.'_rep1' => $question->getRep1(),
                'q'.$cpt.'_rep2' => $question->getRep2(),
                'q'.$cpt.'_rep3' => $question->getRep3(),
                'q'.$cpt.'_rep4' => $question->getRep4(),
                'q'.$cpt.'_right_answer' => $question->getRightAnswer(),
            ];
        }
     
        
        return new JsonResponse($json_questions, 200);
    }


    /**
     * @Route ("/quizz/{id_quizz}/result", name="quizz/id_quizz/result")
     * @return Response
     */
    public function checkReponse($id_quizz): Response
    {
        $questions = $this->getDoctrine()->getRepository(QuizzQuestion::class)->findBy(['quizz' => $id_quizz]);

        //Récupération du nom et de la description du quizz
        $quizz = $this->getDoctrine()->getRepository(Quizz::class)->findBy(['id' => $id_quizz])[0];

        $json_questions[0] = [
            "quizz_id" => $quizz->getId(),
            "quizz_name" => $quizz->getName(),
            "quizz_description" => $quizz->getDescription(),
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