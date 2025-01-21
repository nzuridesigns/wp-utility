<?php

namespace Jcodify\CarRentalTheme\Wordpress;

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

    /**
     * Registers all blocks by reconciling source and build paths.
     *
     * @return WP_Error|void Returns WP_Error on failure.
     */
    public function registerBlocks()
    {
        try {
            $buildPaths = $this->getBlockPaths($this->baseDir . $this->buildDir);
            $srcPaths = $this->getBlockPaths($this->baseDir . $this->srcDir);

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

    /**
     * Retrieves block paths and relative paths for JSON files.
     *
     * @param string $path The directory path to search.
     * @return array An array containing paths and relative paths.
     */
    protected function getBlockPaths(string $path): array
    {
        $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::LEAVES_ONLY);

        $paths = [];
        $rel = [];
        foreach ($dir as $file) {
            if ($file->isFile() && $file->getFilename() === 'block.json') {
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
}
