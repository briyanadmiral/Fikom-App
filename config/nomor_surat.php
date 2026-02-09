<?php

return [
    'formats' => [
        'SK' => [
            'pattern' => '{NO}{SUF}/{KLAS}/{UNIT}/{BULAN}/{TAHUN}',
            'zero_pad' => 0,
            'unit' => 'FIKOM',
        ],
        'ST' => [
            'pattern' => '{NO}{SUF}/{KLAS}/{UNIT}/{BULAN}/{TAHUN}',
            'zero_pad' => 3,
            'unit' => 'ST.IKOM',
        ],
    ],
    // aktifkan jika mau auto huruf A,B,C saat {NO} bentrok di scope
    'allow_suffix' => true,
];
