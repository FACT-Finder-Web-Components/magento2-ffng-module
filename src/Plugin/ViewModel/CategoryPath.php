<?php

declare(strict_types=1);

namespace Omikron\FactfinderNG\Plugin\ViewModel;

use Magento\Catalog\Model\Category;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Omikron\Factfinder\ViewModel\CategoryPath as ViewModel;

class CategoryPath
{
    /** @var Registry */
    private $registry;

    /** @var string */
    private $param;

    /** @var string[] */
    private $initial;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Registry $registry,
        string $param = 'CategoryPath',
        array $initial = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->param       = $param;
        $this->registry    = $registry;
        $this->initial     = $initial;
    }

    /**
     * @param ViewModel $subject
     * @param callable  $proceed
     * @param mixed     ...$params
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetValue(ViewModel $subject, callable $proceed, ...$params): string
    {
        switch ($this->scopeConfig->getValue('factfinder/advanced/version')) {
            case 'ng':
                $categories = $this->getCategoryPath($this->getCurrentCategory());
                $value      = $this->initial;
                $value[]    = sprintf('filter=%s', urlencode($this->param . ':' . implode('/', $categories)));
                return implode(',', $value);
            default:
                return $proceed(...$params);
        }
    }

    protected function getCategoryPath(?Category $category): array
    {
        $categories = $category ? $category->getParentCategories() : [];
        usort($categories, function (Category $a, Category $b): int {
            return $a->getLevel() - $b->getLevel();
        });
        return array_map(function (Category $item): string {
            return (string) $item->getName();
        }, $categories);
    }

    protected function getCurrentCategory(): ?Category
    {
        return $this->registry->registry('current_category');
    }
}
