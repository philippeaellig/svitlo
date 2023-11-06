<?php

namespace App\Repository;

use App\Entity\ChildTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChildTemplate>
 *
 * @method ChildTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChildTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChildTemplate[]    findAll()
 * @method ChildTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChildTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChildTemplate::class);
    }

    public function findRandomTemplate() :?ChildTemplate
    {
        $templates = $this->findAll();
        $randomIndex = array_rand($templates);
        return $templates[$randomIndex];
    }

    public function findRandomTemplateByGender($gender) :?ChildTemplate
    {
        $templates = $this->findBy(['gender' => $gender]);
        $randomIndex = array_rand($templates);
        return $templates[$randomIndex];
    }



//    /**
//     * @return ChildTemplate[] Returns an array of ChildTemplate objects
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

//    public function findOneBySomeField($value): ?ChildTemplate
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
