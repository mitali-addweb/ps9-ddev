<?php

declare(strict_types=1);

namespace PrestaShop\Module\Tshirtecommerce\Model;

use PrestaShop\PrestaShop\Core\Foundation\Database\EntityInterface;
use PrestaShop\PrestaShop\Core\Foundation\Database\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ps_tshirtecommerce")
 * @ORM\Entity(repositoryClass="PrestaShop\Module\Tshirtecommerce\Repository\TshirtecommerceRepository")
 */
class Tshirtecommerce implements EntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id_tshirtecommerce", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="design_product_id", type="string", length=255)
     */
    private $designProductId;

    /**
     * @ORM\Column(name="id_product", type="integer")
     */
    private $productId;

    /**
     * @ORM\Column(name="id_lang", type="integer")
     */
    private $langId;

    /**
     * @ORM\Column(name="id_shop", type="integer")
     */
    private $shopId;

    /**
     * @ORM\Column(name="customization_id", type="string", length=255)
     */
    private $customizationId;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDesignProductId(): string
    {
        return $this->designProductId;
    }

    public function setDesignProductId(string $designProductId): self
    {
        $this->designProductId = $designProductId;
        return $this;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;
        return $this;
    }

    public function getLangId(): int
    {
        return $this->langId;
    }

    public function setLangId(int $langId): self
    {
        $this->langId = $langId;
        return $this;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function setShopId(int $shopId): self
    {
        $this->shopId = $shopId;
        return $this;
    }

    public function getCustomizationId(): string
    {
        return $this->customizationId;
    }

    public function setCustomizationId(string $customizationId): self
    {
        $this->customizationId = $customizationId;
        return $this;
    }
}