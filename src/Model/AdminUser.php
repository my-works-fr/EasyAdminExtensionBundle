<?php

namespace AlterPHP\EasyAdminExtensionBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Storage agnostic admin user object.
 */
class AdminUser implements UserInterface
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var Collection
     */
    protected $groups;

    /**
     * Display as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getUserIdentifier();
    }

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get groups.
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getRoles(): array
    {
        $roles = ['ROLE_ADMIN'];

        foreach ($this->getGroups() as $group) {
            $roles = \array_merge($roles, $group->getRoles());
        }

        return \array_values(\array_unique($roles));
    }

    /**
     * {@inheritdoc}
     *
     * @return ?string
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @return ?string
     */
    public function getSalt()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated since Symfony 5.3
     */
    public function getUsername()
    {
        return $this->getUserIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
    }
}
