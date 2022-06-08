<?php

use HaydenPierce\ClassFinder\ClassFinder;
use PhpParser\Node;
use PhpParser\NodeAbstract;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Determines hierarchy of AST nodes and replaces the mappings in the corresponding registrars accordingly. Any
 * customizations to the registrars will be destroyed in the process.
 */
final class HierarchyDeterminator
{
    private array $subMap;
    private array $supMap;

    public function __construct()
    {
        $this->subMap = [];
        $this->supMap = [];
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        $this->findClasses();
        $this->findSubclasses();
        $this->removeDuplicates();
        $this->findSuperclasses();

        self::putMapping('SubclassInformationRegistrar', $this->getTranslatedSubclassMapping());
        self::putMapping('SuperclassInformationRegistrar', $this->getTranslatedSuperclassMapping());
    }

    private static function putMapping(string $stub, string $mapping): void
    {
        $from = __DIR__.'/../resources/stubs/'.$stub.'.stub';
        $to = __DIR__.'/../src/NodeInformation/'.$stub.'.php';

        $stub = file_get_contents($from);
        $stub = str_replace('{{ mapping }}', $mapping, $stub);
        file_put_contents($to, $stub);
    }

    private function getTranslatedSubclassMapping(): string
    {
        $mapping = '';

        foreach ($this->subMap as $className => $subclasses) {
            $mapping .= self::translateSubclassMapping($className, $subclasses);
        }

        return $mapping;
    }

    private function getTranslatedSuperclassMapping(): string
    {
        $mapping = '';

        foreach ($this->supMap as $className => $parent) {
            $mapping .= self::translateSuperclassMapping($className, $parent);
        }

        return $mapping;
    }

    private static function translateSubclassMapping(string $className, array $subclasses): string
    {
        return sprintf(
            "         \$mapping->map(%s, new SubclassInformation([%s]));\n",
            self::translateClassName($className),
            implode(', ', array_map([HierarchyDeterminator::class, 'translateClassName'], $subclasses)),
        );
    }

    private static function translateSuperclassMapping(string $className, ?string $parent): string
    {
        return sprintf(
            "         \$mapping->map(%s, new SuperclassInformation(%s));\n",
            self::translateClassName($className),
            self::translateClassName($parent ?: $className)
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

        $classes = array_filter($classes, fn(string $className): bool => is_subclass_of($className, NodeAbstract::class));

        foreach ($classes as $className) {
            $this->subMap[$className] = [];
            $this->supMap[$className] = null;
        }
    }

    private function findSubclasses(): void
    {
        $classes = array_keys($this->subMap);

        foreach ($classes as $className) {
            foreach ($classes as $otherName) {
                if ($className === $otherName) {
                    continue;
                }

                if (is_subclass_of($otherName, $className)) {
                    $this->subMap[$className][] = $otherName;
                }
            }
        }
    }

    private function removeDuplicates(): void
    {
        foreach ($this->subMap as &$subclasses) {
            sort($subclasses);
            $subclasses = array_unique($subclasses);
        }
    }

    private function findSuperclasses(): void
    {
        $classes = array_keys($this->subMap);

        foreach ($classes as $class) {
            foreach ($this->subMap as $parent => $children) {
                if (in_array($class, $children)) {
                    if (
                        is_null($this->supMap[$class])
                        // Higher parent was found
                        || is_subclass_of($this->supMap[$class], $parent)
                    ) {
                        $this->supMap[$class] = $parent;
                    }
                }
            }
        }
    }
}

try {
    (new HierarchyDeterminator())->run();

    echo 'Successfully added hierarchy mappings to registrar.'.PHP_EOL;
} catch (Exception $e) {
    echo 'Exception: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}