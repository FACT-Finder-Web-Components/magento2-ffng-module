<?php

declare(strict_types=1);

namespace Omikron\FactfinderNG\ViewModel;

use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CategoryPath implements ArgumentInterface
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
        $this->param = $param;
        $this->registry = $registry;
        $this->initial = $initial;
    }

    public function getValue(): string
    {
        $categories = $this->getCategoryPath($this->getCurrentCategory());
        $value = $this->initial;
        switch ($this->scopeConfig->getValue('factfinder/advanced/version'))  {
            case 'ng':
                $value[] = sprintf('filter=%s', urlencode($this->param . ':' . implode('/', $categories)));
                break;
            default:
                $path = 'ROOT';
                foreach ($categories as $item) {
                    $value[] = vsprintf('fiter%s%s=%s', array_map('urlencode', [$this->param, $path, $item]));
                    $path    = "{$path}/{$item}";
                }
                break;
        }

        return implode(',', $value);
    }

    protected function getCategoryPath(Category $category): array
    {
        return array_map(function (Category $item): string {
            return (string) $item->getName();
        }, $category->getParentCategories());
    }

    private function getCurrentCategory(): Category
    {
        return $this->registry->registry('current_category');
    }
}
