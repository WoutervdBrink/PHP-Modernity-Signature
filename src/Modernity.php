<?php

namespace Knevelina\Modernity;

use Knevelina\Modernity\Data\LanguageLevelTuple;
use Knevelina\Modernity\NodeInformation\NodeInformationMapping;
use Knevelina\Modernity\NodeInformation\NodeInformationMappingFactory;
use Knevelina\Modernity\Visitors\LanguageLevelVisitor;
use Knevelina\Modernity\Visitors\ModernityVisitor;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RuntimeException;

use function array_reduce;
use function file_get_contents;

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

    /** @var ModernityVisitor The visitor which counts the language levels of sub nodes. */
    private readonly ModernityVisitor $modernityVisitor;

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
                $this->modernityVisitor = new ModernityVisitor($this->mapping)
            ),
        ];
    }

    public function getTupleForDirectory(string $path): LanguageLevelTuple
    {
        $directory = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($iterator, '/^.+\.php5??$/i', RegexIterator::GET_MATCH);

        $results = [];
        $totalSize = 0;

        foreach ($files as $matches) {
            $path = $matches[0];

            $results[] = (object)[
                'path' => $path,
                'size' => $size = filesize($path),
                'tuple' => $this->getTupleForFile($path),
            ];

            $totalSize += $size;
        }

        return array_reduce(
            $results,
            fn(LanguageLevelTuple $tuple, object $result) => $tuple->add(
                $result->tuple->normalize()->scale($result->size / $totalSize)
            ),
            new LanguageLevelTuple()
        );
    }

    public function getTupleForFile(string $path): LanguageLevelTuple
    {
        $code = $this->getCodeFromFile($path);

        return $this->getTupleForCode($code);
    }

    public function getTupleForCode(string $code): LanguageLevelTuple
    {
        $this->parseString($code);

        $this->traverse();

        return $this->modernityVisitor->getTuple();
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