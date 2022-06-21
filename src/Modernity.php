<?php

namespace Knevelina\Modernity;

use Knevelina\Modernity\Data\LanguageLevelTuple;
use Knevelina\Modernity\Visitors\LanguageLevelVisitor;
use Knevelina\Modernity\Visitors\ModernityVisitor;
use PhpParser\ErrorHandler;
use PhpParser\Lexer;
use PhpParser\Node\Stmt\Nop;
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

use const STDERR;

final class Modernity
{
    /** @var Lexer Lexer for PHP code. */
    private readonly Lexer $lexer;

    /** @var Parser Parser for PHP code. */
    private readonly Parser $parser;

    /** @var ErrorHandler Error handler for the PHP parser. */
    private readonly ErrorHandler $errorHandler;

    /** @var array<NodeTraverser> The traversers that traverse the AST. */
    private readonly array $traverserChain;

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

        $this->errorHandler = new ErrorHandler\Collecting();

        $this->traverserChain = [
            TraverserFactory::fromVisitors(new ParentConnectingVisitor()),
            TraverserFactory::fromVisitors(new LanguageLevelVisitor()),
            TraverserFactory::fromVisitors($this->modernityVisitor = new ModernityVisitor()),
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

        $this->errorHandler->clearErrors();

        $tuple = $this->getTupleForCode($code);

        if ($this->errorHandler->hasErrors()) {
            fwrite(STDERR, sprintf('Warning: parse error(s) in file %s:%s', $path, PHP_EOL));
            foreach ($this->errorHandler->getErrors() as $error) {
                fwrite(STDERR, sprintf(' - %s%s', $error->getMessage(), PHP_EOL));
            }
        }

        return $tuple;
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
        $this->errorHandler->clearErrors();

        $this->ast = $this->parser->parse($code, $this->errorHandler) ?? [new Nop()];
    }

    private function traverse(): void
    {
        foreach ($this->traverserChain as $traverser) {
            $this->ast = $traverser->traverse($this->ast);
        }
    }
}