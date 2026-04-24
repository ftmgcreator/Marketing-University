@php
    $logo = asset('images/logo.png');
@endphp

<x-filament-panels::page.simple>
    <style>
        .fi-simple-layout {
            background: radial-gradient(circle at 20% 0%, rgba(99, 102, 241, 0.12), transparent 40%),
                        radial-gradient(circle at 80% 100%, rgba(79, 70, 229, 0.08), transparent 40%),
                        #0f1115 !important;
            min-height: 100vh;
        }

        .fi-simple-main-ctn {
            padding: 1rem;
        }

        .fi-simple-header {
            display: none !important;
        }

        .fi-simple-main {
            background: rgba(24, 26, 32, 0.85) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(99, 102, 241, 0.18);
            border-radius: 1rem !important;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.7),
                        inset 0 1px 0 rgba(255, 255, 255, 0.04);
            padding: 1.75rem !important;
            max-width: 26rem;
            width: 100%;
            position: relative;
            overflow: hidden;
            gap: 1rem !important;
        }

        .fi-simple-main::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #6366f1, transparent);
        }

        .tisu-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .tisu-brand-logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
            filter: drop-shadow(0 6px 16px rgba(99, 102, 241, 0.3));
        }

        .tisu-brand-title {
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #a5b4fc 0%, #818cf8 50%, #6366f1 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .tisu-brand-subtitle {
            font-size: 0.8rem;
            color: #9ca3af;
            margin: 0;
            letter-spacing: 0.01em;
        }

        .fi-input-wrp {
            background: rgba(15, 17, 21, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            transition: all 0.2s ease;
        }

        .fi-input-wrp:focus-within {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
        }

        .fi-input {
            color: #f3f4f6 !important;
        }

        .fi-fo-field-lbl-label,
        label {
            color: #d1d5db !important;
            font-size: 0.8rem !important;
        }

        .fi-btn-color-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
            border: none !important;
            font-weight: 600 !important;
            letter-spacing: 0.02em;
            box-shadow: 0 8px 20px -5px rgba(99, 102, 241, 0.35) !important;
            transition: all 0.2s ease !important;
        }

        .fi-btn-color-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 25px -5px rgba(99, 102, 241, 0.45) !important;
        }

        .fi-checkbox-input:checked {
            background-color: #6366f1 !important;
            border-color: #6366f1 !important;
        }

        .tisu-footer {
            text-align: center;
            margin-top: 0.5rem;
            color: #6b7280;
            font-size: 0.7rem;
        }
    </style>

    <div class="tisu-brand">
        <img src="{{ $logo }}" alt="TISU Marketing" class="tisu-brand-logo">
        <h1 class="tisu-brand-title">TISU Marketing</h1>
        <p class="tisu-brand-subtitle">Xush kelibsiz, tizimga kiring</p>
    </div>

    {{ $this->content }}

    <div class="tisu-footer">
        &copy; {{ date('Y') }} TISU Marketing
    </div>
</x-filament-panels::page.simple>
