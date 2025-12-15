{{-- Watermark Partial for PDF Exports --}}
{{-- Include in PDF layouts with @include('partials.pdf_watermark', ['watermark' => 'draft']) --}}

@php
    $watermarkText = match($watermark ?? '') {
        'draft' => 'D R A F T',
        'copy' => 'S A L I N A N',
        'confidential' => 'R A H A S I A',
        'sample' => 'C O N T O H',
        'void' => 'B A T A L',
        default => null,
    };
    
    $watermarkColor = match($watermark ?? '') {
        'draft' => 'rgba(128, 128, 128, 0.15)',
        'copy' => 'rgba(0, 100, 200, 0.12)',
        'confidential' => 'rgba(200, 0, 0, 0.12)',
        'sample' => 'rgba(0, 150, 0, 0.12)',
        'void' => 'rgba(200, 0, 0, 0.15)',
        default => 'rgba(128, 128, 128, 0.15)',
    };
@endphp

@if($watermarkText)
<style>
    .pdf-watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 72pt;
        font-weight: bold;
        color: {{ $watermarkColor }};
        letter-spacing: 20px;
        text-transform: uppercase;
        pointer-events: none;
        z-index: 9999;
        white-space: nowrap;
        font-family: Arial, sans-serif;
    }
    
    /* For DomPDF compatibility */
    @media print {
        .pdf-watermark {
            position: fixed;
        }
    }
</style>
<div class="pdf-watermark">{{ $watermarkText }}</div>
@endif
