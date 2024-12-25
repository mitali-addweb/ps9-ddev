<?php

declare(strict_types=1);

namespace PrestaShop\Module\Tshirtecommerce\Repository;

use Doctrine\ORM\EntityRepository;
use PrestaShop\Module\Tshirtecommerce\Model\Tshirtecommerce;

class TshirtecommerceRepository extends EntityRepository
{
    public function findByProductId(int $productId): ?Tshirtecommerce
    {
        return $this->findOneBy(['productId' => $productId]);
    }

    public function findByDesignProductId(string $designProductId): ?Tshirtecommerce
    {
        return $this->findOneBy(['designProductId' => $designProductId]);
    }

    public function findCustomizations(int $productId, int $langId, int $shopId): array
    {
        return $this->findBy([
            'productId' => $productId,
            'langId' => $langId,
            'shopId' => $shopId,
        ]);
    }

    public function saveDesign(Tshirtecommerce $design): void
    {
        $this->_em->persist($design);
        $this->_em->flush();
    }

    public function removeDesign(Tshirtecommerce $design): void
    {
        $this->_em->remove($design);
        $this->_em->flush();
    }
}