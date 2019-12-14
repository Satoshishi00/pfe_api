<?php

namespace App\Repository;

use App\Entity\Quizz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Quizz|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quizz|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quizz[]    findAll()
 * @method Quizz[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuizzRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quizz::class);
    }


      // La première page est la page 1
    public function findByLastsQuizzLimitPage($value = 50, $page = 1)
    {
        return $this->createQueryBuilder('q')
            //->andWhere('q.exampleField = :val')
            //->setParameter('val', $value)
            ->orderBy('q.created_at', 'DESC')
            ->setMaxResults($value)
            ->setFirstResult(($page-1)*$value)
            ->getQuery()
            ->getResult();
    }
       

    // /**
    //  * @return Quizz[] Returns an array of Quizz objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Quizz
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
