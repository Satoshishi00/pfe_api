<?php

namespace App\Controller;

use App\Entity\FlashCards;
use App\Entity\Qcm;
use App\Entity\Question;
use App\Entity\User;
use App\Entity\Card;
use App\Entity\Media;
use App\Form\QCMFormType;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FlashCardsController extends AbstractController
{
    /**
     * @Route("/flashCards/new", name="flashCards/new")
     * @return Response
     */
    public function add(Request $request) : Response
    {
        //TODO : get user infos by cookie
        $id = $request->headers->get('id');
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneById($id);


        $fc = new FlashCards();
        $fc_name = $request->query->get('fc_name');
        if (strlen($fc_name) > 255){
            return new JsonResponse([
                "error" => "La nom de la FlashCards doit faire au maximum 255 caractères",
            ],
            400);
        }

        $recto_name = $request->query->get('recto_name');
        if (strlen($recto_name) > 255){
            return new JsonResponse([
                "error" => "La nom du recto de vos FlashCards doit faire au maximum 255 caractères",
            ],
            400);
        }

        $verso_name = $request->query->get('verso_name');
        if (strlen($verso_name) > 255){
            return new JsonResponse([
                "error" => "La nom du verso de vos FlashCards doit faire au maximum 255 caractères",
            ],
            400);
        }

        $recto_type = $request->query->get('recto_type');
        if ($recto_type != "text" && $recto_type != "media"){
            return new JsonResponse([
                "error" => "Le recto de vos flashCards n'est pas un type correct. Il doit être de type 'text' ou 'media'",
            ],
            400);
        }

        $verso_type = $request->query->get('verso_type');
        if ($verso_type != "text" && $verso_type != "media"){
            return new JsonResponse([
                "error" => "Le verso de vos flashCards n'est pas un type correct. Il doit être de type 'text' ou 'media'",
            ],
            400);
        }

        $user->setNbFlashCards($user->getNbFlashCards() + 1);
        $fc->setIdCreator($user);
        $fc->setName($fc_name);
        $fc->setRectoName($recto_name);
        $fc->setVersoName($verso_name);
        $fc->setRectoType($recto_type);
        $fc->setVersoType($verso_type);
    
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->persist($fc);
        $entityManager->flush();


        //dd($fc);

        $i = 1;
        //recto
        if ($recto_type == "media"){
            $card_recto = $request->files->get('card_recto_'.$i);
        } else {
            $card_recto = $request->query->get('card_recto_'.$i);
        }
        //verso
        if ($verso_type == "media"){
            $card_verso = $request->files->get('card_verso_'.$i);
        } else {
            $card_verso = $request->query->get('card_verso_'.$i);
        }
        //dd($card_recto);
        //dd($card_verso);
        while (!is_null($card_recto) && !is_null($card_verso)){
            //dd('tata');
            $card = new Card();
            $card->setIdFlashCards($fc);
            
            //recto
            if ($recto_type == "media"){
                $media = new Media();
                
                //take media infos
                //Complexite du nom de l'image pour éviter les doublons
                //TODO : Améliorer le temps de traitement de l'image
                $media_name = base64_encode($card_recto->getClientOriginalName()).sha1($card_recto->getClientOriginalName().rand()).'.'.$card_recto->getClientOriginalExtension();
                $media_size = $card_recto->getClientSize();

                //Set first infos in card and flush to have id_card
                $card->setRecto($media_name);
                $card->setVerso("");
                $entityManager->persist($card);
                $entityManager->flush();

                //Add media in db
                $media->setIdCard($card);
                $media->setImageFile($card_recto);
                $entityManager->persist($media);
                $media->setImageName($media_name);
                $media->setImageSize($media_size);
                $entityManager->flush();

                //Change of image name
                rename("/Applications/MAMP/htdocs/pfe/api/public/medias/images/".$card_recto->getClientOriginalName(), "/Applications/MAMP/htdocs/pfe/api/public/medias/images/".$media_name);
                //dd($media);
            } else {
                //text
                $card->setRecto($card_recto);
                $entityManager->persist($card);

            }
            
            //increase in the number of cards
            $fc->setNbCards($fc->getNbCards()+1);

            //add points to user (10points for 1 question)
            $user->setPoints($user->getPoints() + 5);
            
            //verso
            if ($verso_type == "media"){
                $media = new Media();

                //take media infos
                //Complexite du nom de l'image pour éviter les doublons
                //TODO : Améliorer le temps de traitement de l'image
                $media_name = base64_encode(sha1(rand(1,10).$card_verso->getClientOriginalName().rand(1,10))).base64_encode(sha1($card_verso->getClientOriginalName().rand(1,10))).'.'.$card_verso->getClientOriginalExtension();
                $media_size = $card_verso->getClientSize();

                //Set first infos in card and flush to have id_card
                $card->setVerso($media_name);
                $entityManager->flush();

                //Add media in db
                $media->setIdCard($card);
                $media->setImageFile($card_verso);
                $entityManager->persist($media);
                $media->setImageName($media_name);
                $media->setImageSize($media_size);
                $entityManager->flush();

                //Change of image name
                rename("/Applications/MAMP/htdocs/pfe/api/public/medias/images/".$card_verso->getClientOriginalName(), "/Applications/MAMP/htdocs/pfe/api/public/medias/images/".$media_name);
            } else {
                //text
                $card->setVerso($card_verso);
                $entityManager->flush();
                //dd($card);
            }
            
            
            $i++;
            //recto
            if ($recto_type == "media"){
                $card_recto = $request->files->get('card_recto_'.$i);
            } else {
                $card_recto = $request->query->get('card_recto_'.$i);
            }
            //verso
            if ($verso_type == "media"){
                $card_verso = $request->files->get('card_verso_'.$i);
            } else {
                $card_verso = $request->query->get('card_verso_'.$i);
            }
        }


        return new JsonResponse([
            "message" => "Le jeu de FlashCards a bien été créé",
            "i" => $i,
        ],
        201);
    }

    /**
     * @Route ("/flashCards/show/all", name="flashCards/show/all")
     * @param Request $request
     * @return Response
     */
    public function showAll(Request $request): Response
    {
        $limit = $request->query->get('limit');
        $page_number = $request->query->get('page_number');

        //LThe first page is the number 1
        $fcs = $this->getDoctrine()->getRepository(FlashCards::class)->findByLastsFlashCardsLimitPage($limit, $page_number);
        
        foreach($fcs as $fc){
            $json_fc[] = [
                "id" => $fc->getId(),
                "name" => $fc->getName(),
                "nb_cards" => $fc->getNbCards(),
                "recto_type" => $fc->getRectoType(),
                "verso_type" => $fc->getVersoType(),
                "recto_name" => $fc->getRectoName(),
                "verso_name" => $fc->getVersoName(),
                "updated_at" => $fc->getUpdatedAt(),
                "created_at" => $fc->getCreatedAt(),
            ];
        }

        return new JsonResponse($json_fc, 200);
    }


    /**
     * @Route ("/flashCards/{id_fc}", name="flashCards/id_fc")
     * @return Response
     */
    public function show($id_fc): Response
    {
        $fc = $this->getDoctrine()->getRepository(FlashCards::class)->findBy(['id' => $id_fc])[0];
        //dd($fc);
        $json_fc[] = [
            'fc_id' => $fc->getId(),
            'fc_name' => $fc->getName(),
            'fc_nb_cards' => $fc->getNbCards(),
            'fc_recto_type' => $fc->getRectoType(),
            'fc_verso_type' => $fc->getVersoType(),
            'fc_recto_name' => $fc->getRectoName(),
            'fc_verso_name' => $fc->getVersoName(),
            'fc_id_creator' => $fc->getIdCreator()->getId(),
        ];

        $cards = $this->getDoctrine()->getRepository(Card::class)->findBy(['id_flash_cards' => $id_fc]);

        foreach($cards as $card){
            $json_fc[] = [
                "id" => $card->getId(),
                "recto" => $card->getRecto(),
            ];
        }
        
        return new JsonResponse($json_fc, 200);
    }

    /**
     * @Route ("/flashCards/{id_fc}/getRandomCard", name="flashCards/id_fc/getRandomCard", methods="POST")
     * @return Response
     */
    public function getRandomCardWithExcept($id_fc, Request $request): Response
    {
        
        //Récupération de l'id de la flash card
        $fc = $this->getDoctrine()->getRepository(FlashCards::class)->findBy(['id' => $id_fc])[0];
        
        $json_fc[] = [
            'fc_id' => $fc->getId(),
            'fc_name' => $fc->getName(),
            'fc_nb_cards' => $fc->getNbCards(),
            'fc_recto_type' => $fc->getRectoType(),
            'fc_verso_type' => $fc->getVersoType(),
            'fc_recto_name' => $fc->getRectoName(),
            'fc_verso_name' => $fc->getVersoName(),
            'fc_id_creator' => $fc->getIdCreator()->getId(),
        ];

        // ---- 1 ----
        //Récupération de la liste des cards de la fc
        $cards = $this->getDoctrine()->getRepository(Card::class)->findBy(['id_flash_cards' => $id_fc]);
        
        // ---- 2 ----
        //Récupération de la liste des id des cards de la fc
        $cards_id = [];
        for($i=0; $i<count($cards); $i++){
            $cards_id[] = $cards[$i]->getId();
        }

        // ---- 3 ----
        //Récupération des id des cartes déjà faites
        $liste = $request->request->get('result');
        $cards_done = explode(",", $liste);
        foreach($cards_done as $card_done) {
            $list_tmp[] = intval($card_done);
        }
        $cards_done = $list_tmp;

        // ---- 4 ----
        //Récupération de la liste qui contient les cartes restantes, 
        //c'est à dire la différence entre les cartes de la liste et les cartes faites
        $cards_rest = array_diff($cards_id, $cards_done);
        $tmp = [];
        foreach($cards_rest as $item) {
            $tmp[] = $item;
        }
        $cards_rest = $tmp;
    
        // ---- 5 ----
        //S'il y a encore au moins une carte dans le paquet des id cartes restantes
        if(count($cards_rest)>0){
            //On choisi (l'id) d'une carte aléatoirement
            $card_id = $cards_rest[rand(0,count($cards_rest)-1)];
            foreach($cards as $c){
                //On récupére les informations sur la carte du paquet qui nous intéresse
                if ($c->getId() === $card_id){
                    $card = $c;
                    break;
                }
            }
        } else {
            return new JsonResponse(["finish" => "Vous avez fini ce jeu de carte"], 200);
        }
        
        // ---- 6 ----
        //Affichage de la carte
        $json_fc[] = [
            "card_id" => $card->getId(),
            "card_recto" => $card->getRecto(),
            "card_verso" => $card->getVerso(),
            "cards_id" => $cards_id,
            "cards_done" => $cards_done,
            "cards_rest" => $cards_rest,
            "remaining" => count($cards_rest),
        ];

        return new JsonResponse($json_fc, 200);
    }


    /**
     * @Route ("/flashCards/{id_fc}/result", name="flashCards/id_fc/result")
     * @return Response
     */
    public function checkReponse($id_fc): Response
    {
        $fc = $this->getDoctrine()->getRepository(FlashCards::class)->findBy(['id' => $id_fc])[0];
        //dd($fc);
        $json_fc[] = [
            'fc_id' => $fc->getId(),
            'fc_name' => $fc->getName(),
            'fc_nb_cards' => $fc->getNbCards(),
            'fc_recto_type' => $fc->getRectoType(),
            'fc_verso_type' => $fc->getVersoType(),
            'fc_recto_name' => $fc->getRectoName(),
            'fc_verso_name' => $fc->getVersoName(),
            'fc_id_creator' => $fc->getIdCreator()->getId(),
        ];

        $cards = $this->getDoctrine()->getRepository(Card::class)->findBy(['id_flash_cards' => $id_fc]);

        foreach($cards as $card){
            $json_fc[] = [
                "id" => $card->getId(),
                "recto" => $card->getRecto(),
                "verso" => $card->getVerso(),
            ];
        }
        
        return new JsonResponse($json_fc, 200);
    }

    /**
     * @Route ("/flashCards/{id_fc}/remove", name="flashCards/id_fc/remove")
     * @return Response
     */
    public function removeQcm($id_fc): Response
    {
        $fc = $this->getDoctrine()->getRepository(FlashCards::class)->findBy(['id' => $id_fc])[0];
        $cards = $this->getDoctrine()->getRepository(Card::class)->findBy(['id_flash_cards' => $id_fc]);

        $em = $this->getDoctrine()->getManager();
        
        foreach($cards as $card){
            $em->remove($card);
        }
        $em->remove($fc);
        $em->flush();
   
        return new JsonResponse([
            "message" => "Le jeu de flashCards a bien été supprimé",
        ],
        201);
    }

    /**
     * @Route ("/flashCards/{id_fc}/getInformationsAfterAnswering", name="flashCards/id_fc/getInformationsAfterAnswering")
     * @return Response
     */
    public function getInformationsAfterAnswering($id_fc, Request $request)
    {
        //Incrémentation de 'nb_done' au jeu
        $fc = $this->getDoctrine()->getRepository(FlashCards::class)->findBy(['id' => $id_fc])[0];
        $fc->setNbDone($fc->getNbDone()+1);

        //Ajouter n points à l'utilisateur
        $nb_good_rep = $request->request->get('nb_good_rep');
        $user_id = $request->request->get('user_id');
        
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneById($user_id);
        $user->setPoints($user->getPoints()+$nb_good_rep);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            "ok" => "Informations de réponses ont été bien ajoutées.",
        ],
        201);
    }
}