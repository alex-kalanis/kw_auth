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
     * @return string[]
     */
    public function separateStr(string $parent, string $separator = IFile::PARENT_SEPARATOR): array
    {
        return array_values(
            array_filter(
                array_map(
                    'strval',
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
     * @param string[] $parent
     * @param string $separator
     * @return string
     */
    public function compactStr(array $parent, string $separator = IFile::PARENT_SEPARATOR): string
    {
        return implode($separator, array_values(array_filter(array_map('strval', $parent))));
    }
}
