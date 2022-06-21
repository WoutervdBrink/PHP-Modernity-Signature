<?php

namespace Knevelina\Modernity\Data;

use Knevelina\Modernity\Enums\LanguageLevel;

class LanguageLevelTupleStore
{
    const PATH = __DIR__.'/../../resources/store.json';

    private array $store;

    private array $keys;

    public function __construct()
    {
        $this->store = [];

        foreach (LanguageLevel::range() as $level) {
            $this->keys[] = $level;
        }

        $this->load();
    }

    public function has(string $key): bool
    {
        return isset($this->store[$key]);
    }

    public function get(string $key): ?LanguageLevelTuple
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->store[$key];
    }

    public function set(string $key, LanguageLevelTuple $tuple): void
    {
        $this->store[$key] = $tuple;

        $this->save();
    }

    private function encode(LanguageLevelTuple $tuple): array
    {
        $result = [];

        foreach ($this->keys as $key) {
            $result[] = $tuple[$key];
        }

        return $result;
    }

    private function decode(array $values): LanguageLevelTuple
    {
        $tuple = new LanguageLevelTuple();

        for ($i = 0; $i < count($this->keys); $i++) {
            $tuple[$this->keys[$i]] = $values[$i];
        }

        return $tuple;
    }

    private function save(): void
    {
        $data = [];

        foreach ($this->store as $key => $level) {
            $data[$key] = $this->encode($level);
        }

        \file_put_contents(self::PATH, \json_encode($data, \JSON_PRETTY_PRINT));
    }

    private function load(): void
    {
        if (!\file_exists(self::PATH)) {
            return;
        }

        if (!\is_readable(self::PATH)) {
            return;
        }

        $data = @\file_get_contents(self::PATH);

        if ($data === false) {
            return;
        }

        $data = @\json_decode($data);

        if ($data === false) {
            return;
        }

        $data = (array) $data;

        foreach ($data as $key => $level) {
            $this->store[$key] = $this->decode($level);
        }
    }
}