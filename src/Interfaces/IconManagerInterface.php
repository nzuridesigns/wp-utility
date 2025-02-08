<?php

namespace Nzuridesigns\WPUtility\Interfaces;

/**
 * Interface for managing SVG icons.
 */
interface IconManagerInterface
{
    /**
     * Renders an SVG icon.
     *
     * @param string $iconName The name of the icon (without .svg extension).
     * @param array $attributes Key-value pairs of attributes (e.g., class, width, height).
     * @return string The HTML output of the SVG.
     */
    public function render(string $iconName, array $attributes = []): string;
}
