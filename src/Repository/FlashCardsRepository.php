<?php

namespace App\Repository;

use App\Entity\FlashCards;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method FlashCards|null find($id, $lockMode = null, $lockVersion = null)
 * @method FlashCards|null findOneBy(array $criteria, array $orderBy = null)
 * @method FlashCards[]    findAll()
 * @method FlashCards[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlashCardsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FlashCards::class);
    }

    // /**
    //  * @return FlashCards[] Returns an array of FlashCards objects
    //  */
    // La première page est la page 1
    public function findByLastsFlashCardsLimitPage($value = 50, $page = 1)
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.created_at', 'DESC')
            ->setMaxResults($value)
            ->setFirstResult(($page-1)*$value)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?FlashCards
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
