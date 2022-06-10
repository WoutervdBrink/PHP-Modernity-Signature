<?php

namespace Knevelina\Modernity;

use Knevelina\Modernity\Enums\LanguageLevel;
use Knevelina\Modernity\NodeInformation\NodeInformationMapping;
use Knevelina\Modernity\NodeInformation\NodeInformationMappingFactory;
use Knevelina\Modernity\Visitors\LanguageLevelCounter;
use Knevelina\Modernity\Visitors\LanguageLevelVisitor;
use Knevelina\Modernity\Visitors\SubNodeLanguageLevelCountingVisitor;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use RuntimeException;

use function file_get_contents;
use function str_repeat;

final class Modernity
{
    /** @var Lexer Lexer for PHP code. */
    private readonly Lexer $lexer;

    /** @var Parser Parser for PHP code. */
    private readonly Parser $parser;

    /** @var array<NodeTraverser> The traversers that traverse the AST. */
    private readonly array $traverserChain;

    /** @var NodeInformationMapping Mapping registry from AST node class names to information about them. */
    private readonly NodeInformationMapping $mapping;

    /** @var SubNodeLanguageLevelCountingVisitor The visitor which counts the language levels of sub nodes. */
    private readonly SubNodeLanguageLevelCountingVisitor $subNodeLanguageLevelCountingVisitor;

    /**
     * @var array The array of statements representing the AST that is currently being processed.
     */
    private array $ast;

    public function __construct()
    {
        $this->lexer = new Lexer(
            [
                'usedAttributes' => ['startLine']
            ]
        );

        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $this->lexer);

        $this->mapping = NodeInformationMappingFactory::withDefaultRegistrars();

        $this->traverserChain = [
            TraverserFactory::fromVisitors(new ParentConnectingVisitor()),
            TraverserFactory::fromVisitors(new LanguageLevelVisitor()),
            TraverserFactory::fromVisitors(
                $this->subNodeLanguageLevelCountingVisitor = new SubNodeLanguageLevelCountingVisitor($this->mapping)
            ),
        ];
    }

    public function runForFile(string $path): void
    {
        $code = $this->getCodeFromFile($path);

        $this->runForCode($code);
    }

    public function runForCode(string $code): void
    {
        $this->parseString($code);

        $this->traverse();

        foreach ($this->subNodeLanguageLevelCountingVisitor->getCounters() as $parentClassName => $subNodeCounters) {
            echo $parentClassName . PHP_EOL;

            foreach ($subNodeCounters as $subNodeName => $counters) {
                echo '  '.$subNodeName.PHP_EOL;

                $total = array_sum(array_map(fn (LanguageLevelCounter $counter): int => $counter->getHits(), $counters));

                foreach ($counters as $className => $counter) {
                    printf(
                        '    %-50s (%5.1f%%) [%s]%s',
                        $className,
                        $total > 0 ? ($counter->getHits() / $total) * 100 : 0,
                        implode(', ', array_map(fn(int $count): string => sprintf('%6d', $count), $counter->getAll())),
                        PHP_EOL
                    );

                    printf(
                        '%s[%s]%s',
                        str_repeat(' ', 64),
                        implode(', ', array_map(fn (int $count): string => sprintf('%5.1f%%', ($count / $counter->getHits()) * 100), $counter->getAll())),
                        PHP_EOL
                    );
                }
            }
        }
    }

    private function getCodeFromFile(string $path): string
    {
        $code = file_get_contents($path);

        if ($code === false) {
            throw new RuntimeException(sprintf('Could not read from file "%s"', $path));
        }

        return $code;
    }

    private function parseString(string $code): void
    {
        $this->ast = $this->parser->parse($code);
    }

    private function traverse(): void
    {
        foreach ($this->traverserChain as $traverser) {
            $this->ast = $traverser->traverse($this->ast);
        }
    }
}