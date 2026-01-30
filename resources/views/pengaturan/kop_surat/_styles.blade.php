{{-- resources/views/pengaturan/kop_surat/_styles.blade.php --}}
{{-- Extracted CSS styles for Kop Surat settings page --}}

@push('styles')
<style>
    .gradient-blue {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .card-modern {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .card-modern:hover {
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.25);
    }
    .card-header-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 20px 25px;
        font-weight: 600;
        font-size: 18px;
    }
    .radio-card {
        border: 2px solid #e3e8ef;
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }
    .radio-card:hover {
        border-color: #667eea;
        background: #f8f9ff;
        transform: scale(1.02);
    }
    .radio-card.active {
        border-color: #667eea;
        background: linear-gradient(135deg, #f8f9ff 0%, #e8ecff 100%);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
    }
    .radio-card input[type="radio"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    .radio-card-icon {
        font-size: 2.5rem;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .form-control:focus, .custom-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .btn-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    .btn-gradient:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }
    .section-box {
        border: 2px solid #e3e8ef;
        border-radius: 15px;
        padding: 25px;
        background: white;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    .section-box.active {
        border-color: #667eea;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
    }
    .badge-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .align-selector {
        display: inline-flex;
        border: 2px solid #667eea;
        border-radius: 10px;
        overflow: hidden;
    }
    .align-selector input[type="radio"] { display: none; }
    .align-selector label {
        padding: 10px 20px;
        cursor: pointer;
        background: white;
        color: #667eea;
        transition: all 0.3s ease;
        margin: 0;
        border-right: 1px solid #e3e8ef;
    }
    .align-selector label:last-child { border-right: none; }
    .align-selector input[type="radio"]:checked + label {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
    }

    /* Custom Range Slider Styling */
    .custom-range {
        width: 100%;
        height: 8px;
        background: #e3e8ef;
        outline: none;
        border-radius: 10px;
        -webkit-appearance: none;
        appearance: none;
    }
    .custom-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        cursor: pointer;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    }
    .custom-range::-webkit-slider-thumb:hover {
        transform: scale(1.2);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.6);
    }
    .custom-range::-moz-range-thumb {
        width: 20px;
        height: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        cursor: pointer;
        border-radius: 50%;
        border: none;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    }
    .custom-range::-moz-range-thumb:hover {
        transform: scale(1.2);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.6);
    }
    .custom-range:focus { outline: none; }
    .custom-range:focus::-webkit-slider-thumb {
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
    }

    /* A4 PREVIEW STYLING */
    .preview-wrapper {
        background: #535353;
        padding: 20px;
        border-radius: 15px;
        overflow-y: auto;
        max-height: 90vh;
    }
    .a4-preview {
        width: 21cm;
        height: 29.7cm;
        margin: 0 auto;
        background: white;
        box-shadow: 0 0 20px rgba(0,0,0,0.3);
        position: relative;
        overflow: hidden;
        transform-origin: top center;
    }

    .preview-controls {
        text-align: center;
        margin-bottom: 15px;
    }
    .preview-controls button {
        background: white;
        border: 2px solid #667eea;
        color: #667eea;
        padding: 8px 15px;
        border-radius: 8px;
        margin: 0 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .preview-controls button:hover {
        background: #667eea;
        color: white;
    }
    .preview-controls button.active {
        background: #667eea;
        color: white;
    }

    /* Sticky hanya di layar LG ke atas */
    @media (min-width: 992px) {
        .kop-preview-sticky {
            position: sticky;
            top: 20px;
        }
    }
</style>
@endpush
