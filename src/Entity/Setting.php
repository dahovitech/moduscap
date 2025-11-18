<?php

namespace App\Entity;

use App\Repository\SettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
class Setting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siteName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $whatsapp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Media $logo = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Media $logoLight = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Media $favicon = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $paymentInfo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $emailSender = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailReceived = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSiteName(): ?string
    {
        return $this->siteName;
    }

    public function setSiteName(?string $siteName): static
    {
        $this->siteName = $siteName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getWhatsapp(): ?string
    {
        return $this->whatsapp;
    }

    public function setWhatsapp(?string $whatsapp): static
    {
        $this->whatsapp = $whatsapp;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getLogo(): ?Media
    {
        return $this->logo;
    }

    public function setLogo(?Media $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getLogoLight(): ?Media
    {
        return $this->logoLight;
    }

    public function setLogoLight(?Media $logoLight): static
    {
        $this->logoLight = $logoLight;

        return $this;
    }

    public function getFavicon(): ?Media
    {
        return $this->favicon;
    }

    public function setFavicon(?Media $favicon): static
    {
        $this->favicon = $favicon;

        return $this;
    }

    public function getPaymentInfo(): ?string
    {
        return $this->paymentInfo;
    }

    public function setPaymentInfo(?string $paymentInfo): static
    {
        $this->paymentInfo = $paymentInfo;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getEmailSender(): ?string
    {
        return $this->emailSender;
    }

    public function setEmailSender(string $emailSender): static
    {
        $this->emailSender = $emailSender;

        return $this;
    }

    public function getEmailReceived(): ?string
    {
        return $this->emailReceived;
    }

    public function setEmailReceived(?string $emailReceived): static
    {
        $this->emailReceived = $emailReceived;

        return $this;
    }
}
