<?php

namespace App\Repository;

use App\Entity\Qcm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Qcm|null find($id, $lockMode = null, $lockVersion = null)
 * @method Qcm|null findOneBy(array $criteria, array $orderBy = null)
 * @method Qcm[]    findAll()
 * @method Qcm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QcmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Qcm::class);
    }

    // /**
    //  * @return Qcm[] Returns an array of Qcm objects
    //  */
    // La première page est la page 1
    public function findByLastsQcmLimitPage($value = 50, $page = 1)
    {
        return $this->createQueryBuilder('q')
            //->andWhere('q.exampleField = :val')
            //->setParameter('val', $value)
            ->orderBy('q.created_at', 'DESC')
            ->setMaxResults($value)
            ->setFirstResult(($page-1)*$value)
            ->getQuery()
            ->getResult()
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?Qcm
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
