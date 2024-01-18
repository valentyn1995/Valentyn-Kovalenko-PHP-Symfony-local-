<?php

namespace App\Repository;

use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ProfileRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 8;

    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $em)
    {
        parent::__construct($registry, Profile::class);
    }

    public function getProfilesPaginator(Profile $authProfile, int $offset): Paginator
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.id != :authProfile')
            ->setParameter('authProfile', $authProfile)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery();

        return new Paginator($query);
    }
}
