<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Category;
use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<Category>
 */
class CategoryRepository extends EntityRepository
{
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
