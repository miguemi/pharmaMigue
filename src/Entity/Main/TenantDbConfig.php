<?php

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;
use Hakam\MultiTenancyBundle\Services\TenantDbConfigurationInterface;
use Hakam\MultiTenancyBundle\Traits\TenantDbConfigTrait;
use App\Repository\Main\TenantDbConfigRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: TenantDbConfigRepository::class)]
class TenantDbConfig implements TenantDbConfigurationInterface
{

    use TenantDbConfigTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Tenant::class, mappedBy: 'dbConfigs')]
    private Collection $tenants;

    public function __construct()
    {
        $this->tenants = new ArrayCollection();
    }

    public function getIdentifierValue(): mixed
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTenants(): Collection
    {
        return $this->tenants;
    }

    public function getConfigArray(): array
    {
        return [
            'database' => $this->getDbName(),
            'user' => $this->getDbUserName(),
            'password' => $this->getDbPassword(),
            'host' => $this->getDbHost(),
            'port' => $this->getDbPort(),
        ];
    }
}
