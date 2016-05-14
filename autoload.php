<?php
namespace Onedrive;

// The Onedrive SDK autoloader.  You probably shouldn't be using this.  Instead,
// use a global autoloader, like the Composer autoloader.
//
// But if you really don't want to use a global autoloader, do this:
//
//     require_once "<path-to-here>/Onedrive/autoload.php"

/**
 * @internal
 */
function autoload($name)
{
    // If the name doesn't start with "Krizalys\Onedrive\", then its not once of our classes.
    if (\substr_compare($name, "Krizalys\\Onedrive\\", 0, 19) !== 0) return;

    // Take the "Krizalys\\Onedrive\" prefix off.
    $stem = \substr($name, 19);

    // Convert "\" and "_" to path separators.
    $pathified_stem = \str_replace(array("\\", "_"), '/', $stem);

    $path = __DIR__ . "/src/Krizalys/Onedrive/" . $pathified_stem . ".php";
    if (\is_file($path)) {
        require_once $path;
    }
}

\spl_autoload_register('Onedrive\autoload');