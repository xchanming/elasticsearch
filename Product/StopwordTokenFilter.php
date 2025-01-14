<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Product;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Term\Filter\AbstractTokenFilter;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Cicada\Core\Framework\Uuid\Uuid;
use Doctrine\DBAL\Connection;

#[Package('core')]
class StopwordTokenFilter extends AbstractTokenFilter
{
    /**
     * @var array<string, int>
     */
    private array $config = [];

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function getDecorated(): AbstractTokenFilter
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $tokens, Context $context): array
    {
        if (empty($tokens)) {
            return $tokens;
        }

        $minSearchLength = $this->getMinSearchLength($context->getLanguageId());

        if ($minSearchLength === null) {
            return $tokens;
        }

        return $this->searchTermLengthFilter($tokens, $minSearchLength);
    }

    public function reset(): void
    {
        $this->config = [];
    }

    /**
     * @param list<string> $tokens
     *
     * @return list<string>
     */
    private function searchTermLengthFilter(array $tokens, int $minSearchTermLength): array
    {
        $filtered = [];
        foreach ($tokens as $tag) {
            $tag = trim($tag);

            if (empty($tag) || mb_strlen($tag) < $minSearchTermLength) {
                continue;
            }

            $filtered[] = $tag;
        }

        return $filtered;
    }

    private function getMinSearchLength(string $languageId): ?int
    {
        if (isset($this->config[$languageId])) {
            return $this->config[$languageId];
        }

        $config = $this->connection->fetchAssociative('
            SELECT `min_search_length`
            FROM product_search_config
            WHERE language_id = :languageId
            LIMIT 1
        ', ['languageId' => Uuid::fromHexToBytes($languageId)]);

        if (empty($config)) {
            return null;
        }

        return $this->config[$languageId] = (int) ($config['min_search_length'] ?? self::DEFAULT_MIN_SEARCH_TERM_LENGTH);
    }
}
