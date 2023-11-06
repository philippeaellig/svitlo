<?php

namespace App\Repository;

use App\Entity\Campaign;
use App\Entity\Child;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Child>
 *
 * @method Child|null find($id, $lockMode = null, $lockVersion = null)
 * @method Child|null findOneBy(array $criteria, array $orderBy = null)
 * @method Child[]    findAll()
 * @method Child[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChildRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Child::class);
    }

    public function getNumberOfChildrenByGenderAndCampaign(int $gender, Campaign $campaign): ?int
    {
        try {
            return $this->createQueryBuilder('c')
                ->select('count(c.id)')
                ->andWhere('c.gender = :gender')
                ->andWhere('c.campaign = :campaign')
                ->setParameter('gender', $gender)
                ->setParameter('campaign', $campaign)
                ->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException $e) {
            return null;
        }
    }

//    /**
//     * @return Child[] Returns an array of Child objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Child
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
