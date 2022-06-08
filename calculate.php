<?php

use Knevelina\Modernity\Visitors\LanguageLevelVisitor;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

require_once __DIR__ . '/vendor/autoload.php';

$lexer = new Lexer([
    'usedAttributes' => ['startLine']
]);

$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

$parentTraverser = new NodeTraverser();
$languageLeveLTraverser = new NodeTraverser();
$printingTraverser = new NodeTraverser();

$parentTraverser->addVisitor(new ParentConnectingVisitor());
$languageLeveLTraverser->addVisitor(new LanguageLevelVisitor());
$printingTraverser->addVisitor(
    new class extends NodeVisitorAbstract {
        private int $depth = 0;

        public function beforeTraverse(array $nodes)
        {
            $this->depth = 0;
        }

        public function enterNode(Node $node)
        {
            $this->depth++;
            printf(
                '%s%s%s%5s%5s%s',
                str_repeat(' ', $this->depth),
                $type = $node->getType(),
                str_repeat(' ', 50 - strlen($type) - $this->depth),
                $node->getAttribute('from')?->value ?: 'none',
                $node->getAttribute('to')?->value ?: 'none',
                PHP_EOL
            );
        }

        public function leaveNode(Node $node)
        {
            $this->depth--;
        }
    }
);

try {
    $file = __FILE__;
    if ($argc === 2) {
        $file = $argv[1];
    }
    $code = file_get_contents($file);
    $stmts = $parser->parse($code);
    $stmts = $parentTraverser->traverse($stmts);
    $stmts = $languageLeveLTraverser->traverse($stmts);
    $printingTraverser->traverse($stmts);
} catch (\PhpParser\Error $e) {
    echo 'Parse error: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}