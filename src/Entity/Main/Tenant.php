<?php

namespace App\Entity\Main;

use App\Repository\Main\TenantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: TenantRepository::class)]
class Tenant
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    public string $tenant_name;

    #[ORM\Column(length: 127, unique: true, nullable: false)]
    public string $tenant_code;

    #[ORM\ManyToMany(targetEntity: TenantDbConfig::class, inversedBy: 'tenants')]
    #[ORM\JoinTable(name: 'tenant_tenant_db_config')]
    private Collection $dbConfigs;

    public function __construct()
    {
        $this->dbConfigs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDbConfigs(): Collection
    {
        return $this->dbConfigs;
    }

    public function addDbConfig(TenantDbConfig $config): self
    {
        if (!$this->dbConfigs->contains($config)) {
            $this->dbConfigs->add($config);
        }

        return $this;
    }

    public function removeDbConfig(TenantDbConfig $config): self
    {
        $this->dbConfigs->removeElement($config);
        return $this;
    }
}
