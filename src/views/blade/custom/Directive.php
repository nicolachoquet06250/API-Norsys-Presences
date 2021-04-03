<?php

namespace DI\views\blade\custom;

class Directive {
    public static function datetime($expr) {
        $expr = str_replace([', '], [','], $expr);
        $expr = explode(',', $expr);
        [$expr, $local] = $expr;
        $local = $local;

        return "<?php echo with($expr)->format('F d, Y g:i a') . ' - ' . $local; ?>";
    }
}