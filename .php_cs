<?php

return Symfony\CS\Config\Symfony23Config::create()
    ->setUsingCache(true)
    ->fixers([
        'align_double_arrow',
        'align_equals',
        'concat_with_spaces',
        'multiline_spaces_before_semicolon',
        'ordered_use',
        'short_array_syntax',
    ]);
