<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'PKBM IBADURRAHMAN' }}</title>
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "tertiary-fixed-dim": "#83da85",
                        "error": "#ba1a1a",
                        "inverse-on-surface": "#e0f4ff",
                        "on-tertiary": "#ffffff",
                        "on-secondary-container": "#0c7521",
                        "surface-dim": "#c0dfee",
                        "on-tertiary-container": "#c7ffc3",
                        "surface": "#f4faff",
                        "outline-variant": "#bfcaba",
                        "surface-container-lowest": "#ffffff",
                        "on-background": "#001f2a",
                        "primary-container": "#2e7d32",
                        "secondary-fixed": "#98f994",
                        "outline": "#707a6c",
                        "on-surface": "#001f2a",
                        "primary-fixed": "#a3f69c",
                        "surface-container": "#d9f2ff",
                        "primary-fixed-dim": "#88d982",
                        "inverse-surface": "#163440",
                        "on-error-container": "#93000a",
                        "on-primary-fixed-variant": "#005312",
                        "on-tertiary-fixed-variant": "#005318",
                        "on-secondary-fixed": "#002204",
                        "tertiary-container": "#277d34",
                        "on-surface-variant": "#40493d",
                        "surface-container-high": "#ceedfd",
                        "surface-container-highest": "#c9e7f7",
                        "tertiary-fixed": "#9ff79f",
                        "secondary-fixed-dim": "#7ddc7a",
                        "on-tertiary-fixed": "#002105",
                        "secondary-container": "#98f994",
                        "surface-bright": "#f4faff",
                        "on-secondary": "#ffffff",
                        "background": "#f4faff",
                        "on-primary-container": "#cbffc2",
                        "surface-variant": "#c9e7f7",
                        "on-primary-fixed": "#002204",
                        "tertiary": "#00631e",
                        "error-container": "#ffdad6",
                        "on-secondary-fixed-variant": "#005313",
                        "primary": "#0d631b",
                        "surface-container-low": "#e6f6ff",
                        "surface-tint": "#1b6d24",
                        "on-primary": "#ffffff",
                        "on-error": "#ffffff",
                        "secondary": "#006e1c",
                        "inverse-primary": "#88d982"
                     },
                     "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px",
                        "12px": "12px"
                     },
                     "spacing": {
                        "card-gap": "24px",
                        "gutter": "20px",
                        "unit": "4px",
                        "container-padding": "24px",
                        "stack-md": "16px",
                        "stack-sm": "8px",
                        "stack-lg": "32px"
                     },
                     "fontFamily": {
                        "label-md": ["Plus Jakarta Sans"],
                        "label-sm": ["Plus Jakarta Sans"],
                        "headline-lg-mobile": ["Plus Jakarta Sans"],
                        "headline-sm": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"],
                        "body-lg": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "body-sm": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "display-lg": ["Plus Jakarta Sans"]
                     },
                     "fontSize": {
                        "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "600" }],
                        "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "600" }],
                        "headline-lg-mobile": ["28px", { "lineHeight": "36px", "fontWeight": "700" }],
                        "headline-sm": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                        "headline-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
                        "body-sm": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                        "display-lg": ["48px", { "lineHeight": "60px", "letterSpacing": "-0.02em", "fontWeight": "700" }]
                     }
                 }
             }
         }
     </script>
     <style>
         .portal-bg {
             background: linear-gradient(135deg, #f4faff 0%, #e6f6ff 50%, #d9f2ff 100%);
         }
         .glass-card {
             background: rgba(255, 255, 255, 0.7);
             backdrop-filter: blur(10px);
             border: 1px solid rgba(255, 255, 255, 0.5);
             box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
         }
         .login-card-shadow {
             box-shadow: 0px 12px 40px rgba(38, 50, 56, 0.08), 0px 4px 12px rgba(38, 50, 56, 0.04);
         }
         .custom-input:focus {
             outline: none;
             border-color: #2E7D32;
             box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.2);
         }
         .bg-pattern {
             background-image: radial-gradient(circle at 2px 2px, rgba(46, 125, 50, 0.05) 1px, transparent 0);
             background-size: 24px 24px;
         }
     </style>
 </head>
 <body class="portal-bg min-h-screen flex flex-col justify-between font-body-md text-on-surface antialiased overflow-x-hidden">
     {{ $slot }}
 </body>
 </html>
