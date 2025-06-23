<?php
// 臨時修復 Twig Cache 問題
namespace Twig\Cache;

if (!class_exists('Twig\Cache\CacheInterface')) {
    interface CacheInterface
    {
        public function generateKey(string $name, string $className): string;
        public function load(string $key): void;
        public function write(string $key, string $content): void;
        public function getTimestamp(string $key): int;
    }
}

if (!class_exists('Twig\Cache\NullCache')) {
    class NullCache implements CacheInterface
    {
        public function generateKey(string $name, string $className): string
        {
            return '';
        }

        public function load(string $key): void {}

        public function write(string $key, string $content): void {}

        public function getTimestamp(string $key): int
        {
            return 0;
        }
    }
}

if (!class_exists('Twig\Cache\FilesystemCache')) {
    class FilesystemCache implements CacheInterface
    {
        const FORCE_BYTECODE_INVALIDATION = 1;

        private $directory;
        private $options;

        public function __construct(string $directory, int $options = 0)
        {
            $this->directory = $directory;
            $this->options = $options;
        }

        public function generateKey(string $name, string $className): string
        {
            return hash('sha256', $className);
        }

        public function load(string $key): void {}

        public function write(string $key, string $content): void {}

        public function getTimestamp(string $key): int
        {
            return 0;
        }
    }
}
