<?php

namespace Nzuridesigns\WPUtility;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use WP_Error;

/**
 * Class for automatically registering blocks from JSON metadata files.
 */
class AutoBlockRegistrar
{
    /**
     * The base directory for blocks.
     *
     * @var string
     */
    protected string $baseDir;

    /**
     * The build directory relative to the base directory.
     *
     * @var string
     */
    protected string $buildDir;

    /**
     * The source directory relative to the base directory.
     *
     * @var string
     */
    protected string $srcDir;

    /**
     * BlockRegistrar constructor.
     *
     * @param string $baseDir The base directory of the theme.
     * @param string $buildDir The build directory containing compiled block JSON files.
     * @param string $srcDir The source directory containing source block JSON files.
     */
    public function __construct(string $baseDir, string $buildDir = '/blocks/build', string $srcDir = '/blocks/src')
    {
        $this->baseDir = rtrim($baseDir, '/');
        $this->buildDir = $buildDir;
        $this->srcDir = $srcDir;
    }

    protected function fullPath(string $path): string
    {
        return $this->baseDir . $path;
    }

    protected function absoluteSrcPath(): string
    {
        return $this->fullPath($this->srcDir);
    }

    protected function absoluteBuildPath(): string
    {
        return $this->fullPath($this->buildDir);
    }


    /**
     * Registers all blocks in the build directory.
     */
    public function registerBuildBlocks(): void
    {
        $buildPaths = $this->getBlockPaths($this->absoluteBuildPath());
        foreach ($buildPaths['paths'] as $jsonPath) {
            register_block_type_from_metadata($jsonPath['path']);
        }
    }

    /**
     * Registers all blocks for development purposes by reconciling source and build paths.
     *
     * @return WP_Error|void Returns WP_Error on failure.
     */
    public function registerBlocksForDevelopmentMode()
    {
        try {
            $this->removeDuplicateBlocks($this->absoluteBuildPath());
            $buildPaths = $this->getBlockPaths($this->absoluteBuildPath());
            $srcPaths = $this->getBlockPaths($this->absoluteSrcPath());

            if (empty($buildPaths['paths']) || empty($srcPaths['paths'])) {
                return new WP_Error('no_blocks_found', 'No blocks found in the specified directories.');
            }

            $reconciledPaths = $this->reconcilePaths($buildPaths, $srcPaths);

            foreach ($reconciledPaths as $jsonPath) {
                register_block_type_from_metadata($jsonPath);
            }
        } catch (\Exception $e) {
            return new WP_Error('block_registration_error', $e->getMessage());
        }
    }

    protected function getPathRecursiveIterator(string $path): RecursiveIteratorIterator
    {

        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
    }

    protected function isBlockJsonFile(mixed $file): bool
    {
        return $file->isFile() && $file->getFilename() === 'block.json';
    }

    /**
     * Retrieves block paths and relative paths for JSON files.
     *
     * @param string $path The directory path to search.
     * @return array An array containing paths and relative paths.
     */
    protected function getBlockPaths(string $path): array
    {
        $dir = $this->getPathRecursiveIterator($path);

        $paths = [];
        $rel = [];
        foreach ($dir as $file) {
            if ($this->isBlockJsonFile($file)) {
                $relativePath = substr($file->getPath(), strlen($path));
                $paths[] = ['path' => $file->getPath(), 'rel' => $relativePath];
                $rel[] = $relativePath;
            }
        }

        return [
            'paths' => $paths,
            'rel' => $rel,
        ];
    }

    /**
     * Reconciles build and source paths to ensure only valid paths are registered.
     *
     * @param array $buildPaths The build paths data.
     * @param array $srcPaths The source paths data.
     * @return array An array of reconciled paths.
     */
    protected function reconcilePaths(array $buildPaths, array $srcPaths): array
    {
        $reconciledPaths = array_filter($buildPaths['paths'], function ($jsonPath) use ($srcPaths) {
            return in_array($jsonPath['rel'], $srcPaths['rel']);
        });

        return array_map(function ($jsonPath) {
            return $jsonPath['path'];
        }, $reconciledPaths);
    }


    /**
     * Removes duplicate blocks from a directory.
     *
     * @param string $path The path to the build directory.
     */
    protected function removeDuplicateBlocks(string $path): void
    {
        $uniqueNames = [];
        $build = $this->getPathRecursiveIterator($path);
        foreach ($build as $file) {
            if ($this->isBlockJsonFile($file)) {
                $block_json = json_decode(file_get_contents($file->getPathname()), true);
                if (!isset($block_json['name'])) {
                    continue;
                }
                $uniqueName = $block_json['name'];
                if (!isset($uniqueNames[$uniqueName])) {
                    $uniqueNames[$uniqueName] = $file->getPath();
                    continue;
                }
                // delete the oldest dir
                $parentDir = $file->getPath();
                $currentTime = filemtime($parentDir);
                $storedParentDir = $uniqueNames[$uniqueName];
                $storedTime = filemtime($storedParentDir);
                $pathToDelete = $parentDir;

                if ($storedTime > $currentTime) {
                    $uniqueNames[$uniqueName] = $parentDir;
                    $pathToDelete = $storedParentDir;
                }
                $this->deleteDirectory($pathToDelete);
            }
        }
    }

    protected function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false; // Return false if it's not a directory
        }

        $items = array_diff(scandir($dir), ['.', '..']); // Get all items except . and ..

        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir); // Remove the directory itself
    }
}
