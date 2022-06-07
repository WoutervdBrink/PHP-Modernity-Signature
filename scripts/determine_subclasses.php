<?php

use HaydenPierce\ClassFinder\ClassFinder;
use PhpParser\Node;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Determines subclasses of AST nodes and replaces the mappings in Knevelina\Modernity\SubclassInformationRegistrar
 * accordingly. Any customizations to this class will be destroyed in the process.
 */
final class SubclassDeterminator
{
    private array $map;

    public function __construct()
    {
        $this->map = [];
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        $this->findClasses();
        $this->findSubclasses();
        $this->removeDuplicates();

        $mapping = $this->getTranslatedMapping();

        $stub = file_get_contents(__DIR__.'/../resources/stubs/SubclassInformationRegistrar.stub');
        $stub = str_replace('{{ mapping }}', $mapping, $stub);
        file_put_contents(__DIR__.'/../src/NodeInformation/SubclassInformationRegistrar.php', $stub);
    }

    private function getTranslatedMapping(): string
    {
        $mapping = '';

        foreach ($this->map as $className => $subclasses) {
            $mapping .= self::translateMapping($className, $subclasses);
        }

        return $mapping;
    }

    private static function translateMapping(string $className, array $subclasses): string
    {
        return sprintf(
            "         \$mapping->map(%s, new SubclassInformation([%s]));\n",
            self::translateClassName($className),
            implode(', ', array_map([SubclassDeterminator::class, 'translateClassName'], $subclasses)),
        );
    }

    private static function translateClassName(string $className): string
    {
        return substr($className, strlen('PhpParser\\')).'::class';
    }

    /**
     * @throws Exception
     */
    private function findClasses(): void
    {
        $classes = ClassFinder::getClassesInNamespace('PhpParser\\Node', ClassFinder::RECURSIVE_MODE);

        $classes = array_filter($classes, fn(string $className): bool => is_subclass_of($className, Node::class));

        foreach ($classes as $className) {
            $this->map[$className] = [];
        }
    }

    private function findSubclasses(): void
    {
        $classes = array_keys($this->map);

        foreach ($classes as $className) {
            foreach ($classes as $otherName) {
                if ($className === $otherName) {
                    continue;
                }

                if (is_subclass_of($otherName, $className)) {
                    $this->map[$className][] = $otherName;
                }
            }
        }
    }

    private function removeDuplicates()
    {
        foreach ($this->map as &$subclasses) {
            sort($subclasses);
            $subclasses = array_unique($subclasses);
        }
    }
}

try {
    (new SubclassDeterminator())->run();

    echo 'Successfully added subclass mappings to registrar.'.PHP_EOL;
} catch (Exception $e) {
    echo 'Exception: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}