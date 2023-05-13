<?php

namespace kalanis\kw_auth\Traits;


use kalanis\kw_auth\Interfaces\IFile;


/**
 * Trait TSeparated
 * @package kalanis\kw_auth\Traits
 * Separate values in entry
 */
trait TSeparated
{
    /**
     * @param string $parent
     * @param string $separator
     * @return int[]
     */
    public function separateInt(string $parent, string $separator = IFile::PARENT_SEPARATOR): array
    {
        return array_values(
            array_filter(
                array_map(
                    'intval',
                    array_filter(
                        (array) explode(
                            $separator ?: IFile::PARENT_SEPARATOR, $parent
                        )
                    )
                )
            )
        );
    }

    /**
     * @param int[] $parent
     * @param string $separator
     * @return string
     */
    public function compactInt(array $parent, string $separator = IFile::PARENT_SEPARATOR): string
    {
        return implode($separator, array_values(array_filter(array_map('strval', $parent))));
    }
}
