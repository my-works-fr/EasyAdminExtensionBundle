<?php

namespace AlterPHP\EasyAdminExtensionBundle\EventListener;

use AlterPHP\EasyAdminExtensionBundle\Model\ListFilter;
use AlterPHP\EasyAdminMongoOdmBundle\Event\EasyAdminMongoOdmEvents;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;

/**
 * Apply filters on list/search queryBuilder.
 */
class MongoOdmPostQueryBuilderSubscriber extends AbstractPostQueryBuilderSubscriber
{
    protected const APPLIABLE_OBJECT_TYPE = 'document';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EasyAdminMongoOdmEvents::POST_LIST_QUERY_BUILDER => ['onPostListQueryBuilder'],
            EasyAdminMongoOdmEvents::POST_SEARCH_QUERY_BUILDER => ['onPostSearchQueryBuilder'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function supportsQueryBuilder($queryBuilder): bool
    {
        return $queryBuilder instanceof QueryBuilder;
    }

    /**
     * Filters queryBuilder.
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $field
     * @param ListFilter   $listFilter
     */
    protected function filterQueryBuilder(QueryBuilder $queryBuilder, string $field, ListFilter $listFilter)
    {
        $value = $this->filterEasyadminAutocompleteValue($listFilter->getValue());
        // Empty string and numeric keys is considered as "not applied filter"
        if (null === $value || '' === $value || \is_int($field)) {
            return;
        }

        $queryField = $listFilter->getProperty();

        // Checks if filter is directly appliable on queryBuilder
        if (!$this->isFilterAppliable($queryBuilder, $queryField)) {
            return;
        }

        $operator = $listFilter->getOperator();

        switch ($operator) {
            case ListFilter::OPERATOR_EQUALS:
                if ('_NULL' === $value) {
                    $filterExpr = $queryBuilder->expr()->field($queryField)->equals(null);
                } elseif ('_NOT_NULL' === $value) {
                    $filterExpr = $queryBuilder->expr()->field($queryField)->notEqual(null);
                } else {
                    $filterExpr = $queryBuilder->expr()->field($queryField)->equals($value);
                }
                break;
            case ListFilter::OPERATOR_NOT:
                $filterExpr = $queryBuilder->expr()->field($queryField)->not($value);
                break;
            case ListFilter::OPERATOR_IN:
                // Checks that $value is not an empty Traversable
                if (0 < \count($value)) {
                    $filterExpr = $queryBuilder->expr()->field($queryField)->in($value);
                }
                break;
            case ListFilter::OPERATOR_NOTIN:
                // Checks that $value is not an empty Traversable
                if (0 < \count($value)) {
                    $filterExpr = $queryBuilder->expr()->field($queryField)->notin($value);
                }
                break;
            case ListFilter::OPERATOR_GT:
                $filterExpr = $queryBuilder->expr()->field($queryField)->gt($value);
                break;
            case ListFilter::OPERATOR_GTE:
                $filterExpr = $queryBuilder->expr()->field($queryField)->gte($value);
                break;
            case ListFilter::OPERATOR_LT:
                $filterExpr = $queryBuilder->expr()->field($queryField)->lt($value);
                break;
            case ListFilter::OPERATOR_LTE:
                $filterExpr = $queryBuilder->expr()->field($queryField)->lte($value);
                break;
            default:
                throw new \RuntimeException(\sprintf('Operator "%s" is not supported !', $operator));
        }

        if (isset($filterExpr)) {
            $queryBuilder->addAnd($filterExpr);
        }
    }
}
