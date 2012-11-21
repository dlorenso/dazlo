<?php
/**
 * Dazlo Framework Copyright (c) 2012 D. Dante Lorenso.  All Rights Reserved.
 * This source file is subject to the new BSD license that is bundled with
 * this package in the file LICENSE.txt.  It is also available through the
 * world-wide web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 */
namespace Daz;

class File
{
    /**
     * Loop through a given path and remove subdirectories which are empty. We
     * do this depth-first in order to recursively remove empty directories
     * inside other empty directories, etc.
     */
    public static function removeEmptyDirectories($path, $level = 0)
    {
        $empty = true;

        // iterate over this directory
        $iterator = new DirectoryIterator($path);

        // check all directory entries
        foreach ($iterator as $finfo) {
            // skip dot files
            if ($finfo->isDot()) {
                continue;
            }

            // process sub-directories
            $empty &= $finfo->isDir() && self :: removeEmptyDirectories($finfo->getPathname(), $level + 1);
        }

        // remove directory and return
        return $empty && $level && rmdir($path);
    }
}