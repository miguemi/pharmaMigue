<?php

namespace App\Repository\Main;

use App\Entity\Main\TenantDbConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TenantDbConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass = TenantDbConfig::class)
    {
        parent::__construct($registry, $entityClass);
    }


    /**
     * @return TenantDbConfig[]
     */
    public function findAllWithoutTenants(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.tenants', 't')
            ->andWhere('t.id IS NULL')
            ->getQuery()
            ->getResult();
    }
}
