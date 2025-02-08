<?php

// TODO: implement caching

namespace Nzuridesigns\WPUtility;

use Exception;
use Illuminate\Support\Str;
use Nzuridesigns\WPUtility\Interfaces\IconManagerInterface;
use ValueError;

/**
 * Class for managing SVG icons from a repository.
 */
class IconManager implements IconManagerInterface
{
    /**
     * The base path for the SVG icons.
     *
     * @var string
     */
    protected string $iconPath;

    /**
     * Array of default attributes.
     *
     * @var array
     */
    private array $defautAttributes = [];

    /**
     * Constructor to initialize the icon path.
     *
     * @param string $iconPath The directory where SVG icons are stored.
     */
    public function __construct(string $iconPath)
    {
        $this->iconPath = rtrim($iconPath, '/');
    }

    /**
     * Sets default attributes.
     *
     * @param array $attributes Key-value pairs of attributes (e.g., class, width, height).
     * @return void
     */
    public function setDefaultAttributes(array $attributes)
    {
        $this->defautAttributes = $attributes;
    }


    /**
     * Returns the default attribute.
     *
     * @param string $key The key of the attribute.
     * @return mixed
     */
    public function getDefaultAttribute(string $key): mixed
    {
        return $this->defautAttributes[$key] ?? null;
    }


    /**
     * Renders an SVG icon.
     * When using default attributes and render function attibutes together,
     * a merge of the two will occur where the render function attributes
     * override the default attributes.
     * @param string $iconName The name of the icon (without .svg extension).
     * @param array $attributes Key-value pairs of attributes (e.g., class, width, height).
     * @return string The HTML output of the SVG.
     * @throws Exception If the SVG file is not found.
     */
    public function render(string $iconName, array $attributes = []): string
    {
        $iconFile = realpath($this->getIconPath($iconName));
        try {
            $iconFile = realpath($this->getIconPath($iconName));

            $svgContent = file_get_contents($iconFile);

            if (!$svgContent) {
                throw new Exception("Icon '{$iconName}' could not be read.");
            }

            // Prepare attributes string.
            $attributesString = $this->formatAttributes(array_merge($this->defautAttributes, $attributes));

            // Inject attributes into the SVG tag.
            $svgContent = preg_replace('/<svg\b/i', "<svg {$attributesString}", $svgContent, 1);

            return $svgContent;
        } catch (ValueError $e) {
            // ensure /resources/svg/ exists and is not empty
            if (!is_dir(dirname($this->iconPath)) || $this->isDirEmpty(dirname($this->iconPath))) {
                echo "<pre>Error: Icon File does not exist or ensure /resources/svg/ exists : " . $iconFile . " or</pre>";
                echo "<pre>Error: /resources/svg/ is empty</pre>";
                throw $e;
            }

            echo "<pre>Error: Icon '{$iconName}' could not be found.  </pre> ";

            throw $e;
        }
    }

    /**
     * Checks if a directory is empty.
     *
     * @param string $dir The path to the directory.
     * @return bool True if the directory is empty, false otherwise.
     */
    public function isDirEmpty(string $dir): bool
    {
        // Check if the path is a directory.
        if (!is_dir($dir)) {
            return false;
        }

        // Use scandir to list files and filter out '.' and '..'.
        $files = array_diff(scandir($dir), ['.', '..']);

        // Return true if there are no files, false otherwise.
        return empty($files);
    }

    /**
     * Gets the full path to an SVG icon file.
     *
     * @param string $iconName The name of the icon.
     * @return string The full path to the SVG file.
     */
    protected function getIconPath(string $iconName): string
    {
        // Convert icon name to kebab-case to match file naming conventions.
        $iconName = Str::kebab($iconName);
        return "{$this->iconPath}/{$iconName}.svg";
    }

    /**
     * Formats an array of attributes into a string for HTML.
     *
     * @param array $attributes Key-value pairs of attributes.
     * @return string The formatted attributes.
     */
    protected function formatAttributes(array $attributes): string
    {
        $attributesString = '';

        foreach ($attributes as $key => $value) {
            $attributesString .= htmlspecialchars($key) . '="' . htmlspecialchars($value) . '" ';
        }

        return trim($attributesString);
    }
}
