<?php

namespace App\Enums;

enum SuratStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case DISETUJUI = 'disetujui';
    case DITOLAK = 'ditolak';
    case TERBIT = 'terbit';
    case ARSIP = 'arsip';
}
