<?php

namespace App\Support;

use Illuminate\Http\Request;

final class PaginationPerPage
{
    public const OPTIONS = [10, 15, 25, 50, 100];

    public const DEFAULT = 15;

    public static function resolve(Request $request, int $default = self::DEFAULT): int
    {
        $v = (int) $request->query('per_page', $default);

        return in_array($v, self::OPTIONS, true) ? $v : $default;
    }
}
