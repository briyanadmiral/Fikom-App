<?php

/**
 * Konfigurasi Preset Template Kop Surat
 * 
 * Setiap preset berisi konfigurasi siap pakai yang dapat dipilih user
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Preset Key
    |--------------------------------------------------------------------------
    */
    'default' => 'unika_standard',

    /*
    |--------------------------------------------------------------------------
    | Available Presets
    |--------------------------------------------------------------------------
    */
    'presets' => [
        'unika_standard' => [
            'name' => 'UNIKA Standard',
            'description' => 'Template standar Universitas Katolik Soegijapranata',
            'config' => [
                'mode_type' => 'custom',
                'text_align' => 'left',
                'nama_fakultas' => 'FAKULTAS ILMU KOMPUTER',
                'alamat_lengkap' => 'Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234',
                'telepon_lengkap' => 'Telp. (024) 8441555, 8505003 (hunting) Fax. (024) 8415429 – 8445265',
                'email_website' => 'e-mail: unika@unika.ac.id http://www.unika.ac.id/',
                'logo_size' => 160,
                'font_size_title' => 19,
                'font_size_text' => 12,
                'text_color' => '#000000',
                'header_padding' => 5,
                'background_opacity' => 100,
                'tampilkan_logo_kanan' => true,
                'tampilkan_logo_kiri' => false,
            ],
        ],

        'formal_centered' => [
            'name' => 'Formal Centered',
            'description' => 'Template formal dengan teks di tengah',
            'config' => [
                'mode_type' => 'custom',
                'text_align' => 'center',
                'nama_fakultas' => 'FAKULTAS ILMU KOMPUTER',
                'alamat_lengkap' => 'Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234',
                'telepon_lengkap' => 'Telp. (024) 8441555, 8505003 (hunting) Fax. (024) 8415429 – 8445265',
                'email_website' => 'e-mail: unika@unika.ac.id http://www.unika.ac.id/',
                'logo_size' => 120,
                'font_size_title' => 18,
                'font_size_text' => 11,
                'text_color' => '#000000',
                'header_padding' => 10,
                'background_opacity' => 100,
                'tampilkan_logo_kanan' => true,
                'tampilkan_logo_kiri' => true,
            ],
        ],

        'minimalist' => [
            'name' => 'Minimalist',
            'description' => 'Template minimalis dengan font lebih kecil',
            'config' => [
                'mode_type' => 'custom',
                'text_align' => 'right',
                'nama_fakultas' => 'FAKULTAS ILMU KOMPUTER',
                'alamat_lengkap' => 'Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234',
                'telepon_lengkap' => 'Telp. (024) 8441555, 8505003',
                'email_website' => 'unika@unika.ac.id | www.unika.ac.id',
                'logo_size' => 100,
                'font_size_title' => 14,
                'font_size_text' => 10,
                'text_color' => '#333333',
                'header_padding' => 5,
                'background_opacity' => 100,
                'tampilkan_logo_kanan' => true,
                'tampilkan_logo_kiri' => false,
            ],
        ],

        'dual_logo' => [
            'name' => 'Dual Logo',
            'description' => 'Template dengan logo di kiri dan kanan, teks di tengah',
            'config' => [
                'mode_type' => 'custom',
                'text_align' => 'center',
                'nama_fakultas' => 'FAKULTAS ILMU KOMPUTER',
                'alamat_lengkap' => 'Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234',
                'telepon_lengkap' => 'Telp. (024) 8441555, 8505003 (hunting) Fax. (024) 8415429 – 8445265',
                'email_website' => 'e-mail: unika@unika.ac.id http://www.unika.ac.id/',
                'logo_size' => 140,
                'font_size_title' => 16,
                'font_size_text' => 11,
                'text_color' => '#000000',
                'header_padding' => 10,
                'background_opacity' => 100,
                'tampilkan_logo_kanan' => true,
                'tampilkan_logo_kiri' => true,
            ],
        ],

        'compact' => [
            'name' => 'Compact',
            'description' => 'Template kompak untuk dokumen dengan margin kecil',
            'config' => [
                'mode_type' => 'custom',
                'text_align' => 'left',
                'nama_fakultas' => 'FAKULTAS ILMU KOMPUTER',
                'alamat_lengkap' => 'Jl. Pawiyatan Luhur IV/1 Semarang',
                'telepon_lengkap' => 'T: (024) 8441555 | F: (024) 8415429',
                'email_website' => 'unika@unika.ac.id',
                'logo_size' => 80,
                'font_size_title' => 14,
                'font_size_text' => 9,
                'text_color' => '#000000',
                'header_padding' => 3,
                'background_opacity' => 100,
                'tampilkan_logo_kanan' => true,
                'tampilkan_logo_kiri' => false,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Paper Size Options
    |--------------------------------------------------------------------------
    */
    'paper_sizes' => [
        'A4' => [
            'name' => 'A4',
            'width' => '21cm',
            'height' => '29.7cm',
        ],
        'Letter' => [
            'name' => 'Letter',
            'width' => '21.59cm',
            'height' => '27.94cm',
        ],
        'Legal' => [
            'name' => 'Legal',
            'width' => '21.59cm',
            'height' => '35.56cm',
        ],
        'F4' => [
            'name' => 'F4/Folio',
            'width' => '21cm',
            'height' => '33cm',
        ],
    ],
];
