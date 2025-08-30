<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="96" height="96">
    <defs>
        <!-- Gradient stroke -->
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#4facfe"/>
            <stop offset="100%" stop-color="#8f00ff"/>
        </linearGradient>
    </defs>

    <!-- Book (bottom, flatter curve) -->
    <path d="M64 384
           Q160 368 256 368
           Q352 368 448 384
           L448 416 Q352 400 256 400
           Q160 400 64 416 Z"
          fill="none" stroke="url(#grad)" stroke-width="20" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Roof -->
    <path d="M96 208 L256 112 L416 208"
          fill="none" stroke="url(#grad)" stroke-width="20" stroke-linejoin="round"/>

    <!-- Walls -->
    <rect x="128" y="208" width="256" height="160"
          fill="none" stroke="url(#grad)" stroke-width="20"/>

    <!-- Door -->
    <rect x="224" y="272" width="64" height="96"
          fill="none" stroke="url(#grad)" stroke-width="16"/>

    <!-- Windows -->
    <rect x="160" y="240" width="48" height="48"
          fill="none" stroke="url(#grad)" stroke-width="16"/>
    <rect x="304" y="240" width="48" height="48"
          fill="none" stroke="url(#grad)" stroke-width="16"/>
</svg>
