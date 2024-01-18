<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

class ImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $em)
    {
        parent::__construct($registry, Image::class);
    }

    public function save(Image $image): void
    {
        $this->em->persist($image);
        $this->em->flush();
    }

    public function delete(Image $image): void
    {
        $this->em->remove($image);
        $this->em->flush();
    }
}
