<?php

namespace App\Entity\Admin;

use App\ActionLog\LoggableObjectInterface;
use App\Repository\Admin\UserRepository;
use App\Timestampable\TimestampableEntity;
use App\Timestampable\TimestampableInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User for the admin environment.
 *
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="admin_user")
 * @UniqueEntity("emailAddress")
 */
class User implements UserInterface, TimestampableInterface, LoggableObjectInterface
{
    use TimestampableEntity;

    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email
     * @Assert\Length(max=50)
     */
    private string $emailAddress;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private string $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private string $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $password;

    /**
     * @ORM\Column(type="json")
     * @Assert\Choice({User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN}, multiple=true)
     *
     * @var array<string>
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isEnabled = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTimeInterface $lastLoginAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTimeInterface $lastActiveAt = null;

    /**
     * Non-mapped password used for password generation.
     *
     * @Assert\NotBlank(groups={"new"})
     * @Assert\Length(
     *      min = 6,
     *      max = 128
     * )
     */
    private ?string $plainPassword = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getRoles(): ?array
    {
        return array_merge($this->roles, [self::ROLE_USER]);
    }

    /**
     * @param array<string> $roles
     *
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    /**
     * Returns the most important role of this user.
     */
    public function getMainRole(): string
    {
        if (in_array(self::ROLE_SUPER_ADMIN, $this->roles)) {
            return self::ROLE_SUPER_ADMIN;
        } elseif (in_array(self::ROLE_ADMIN, $this->roles)) {
            return self::ROLE_ADMIN;
        } else {
            return self::ROLE_USER;
        }
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeInterface
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function getLastActiveAt(): ?\DateTimeInterface
    {
        return $this->lastActiveAt;
    }

    public function setLastActiveAt(?\DateTimeInterface $lastActiveAt): self
    {
        $this->lastActiveAt = $lastActiveAt;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->lastActiveAt && $this->lastActiveAt > new \DateTimeImmutable('2 minutes ago');
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->getEmailAddress();
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getNonLoggableProperties(): array
    {
        return [
            'lastLoginAt',
            'lastActiveAt',
        ];
    }
}
