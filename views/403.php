<?php http_response_code(403); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 – Forbidden</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Quicksand:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        .mosaic-bg {
            background-color: #1e2060;
            background-image:
                linear-gradient(135deg, #5b63d3cc 0%, #0c0e2acc 100%),
                repeating-linear-gradient(0deg, transparent, transparent 79px,
                    rgba(255, 255, 255, 0.04) 79px, rgba(255, 255, 255, 0.04) 80px),
                repeating-linear-gradient(90deg, transparent, transparent 79px,
                    rgba(255, 255, 255, 0.04) 79px, rgba(255, 255, 255, 0.04) 80px),
                repeating-linear-gradient(135deg,
                    rgba(255, 255, 255, 0.03) 0px, rgba(255, 255, 255, 0.03) 80px,
                    rgba(0, 0, 0, 0.06) 80px, rgba(0, 0, 0, 0.06) 160px);
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .anim {
            opacity: 0;
            animation: fadeUp 0.7s ease forwards;
        }

        .anim-1 {
            animation-delay: 0.1s;
        }

        .anim-2 {
            animation-delay: 0.25s;
        }

        .anim-3 {
            animation-delay: 0.4s;
        }

        .anim-4 {
            animation-delay: 0.55s;
        }

        /* Slow spin on the lock icon */
        @keyframes pulse-scale {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.08);
                opacity: 0.8;
            }
        }

        .lock-pulse {
            animation: pulse-scale 3s ease-in-out infinite;
        }
    </style>
</head>

<body class="m-0 p-0 font-[Quicksand]">

    <div class="mosaic-bg min-h-screen w-full flex items-center justify-center px-4">
        <div class="text-center text-white max-w-2xl mx-auto">

            <!-- 4🔒3 headline -->
            <div class="anim anim-1 flex items-center justify-center leading-none mb-6 select-none"
                style="font-family:'Montserrat',sans-serif; font-weight:900; font-size: clamp(5rem, 18vw, 9rem); letter-spacing: -0.02em;">
                <span>4</span>

                <!-- Lock icon replacing the "0" -->
                <span class="lock-pulse relative inline-flex items-center justify-center mx-2" style="width:0.9em; height:1em;">
                    <svg viewBox="0 0 100 110" fill="none" xmlns="http://www.w3.org/2000/svg"
                        style="width:100%; height:100%;">
                        <!-- Shackle -->
                        <path d="M28 48 V34 C28 17 72 17 72 34 V48" stroke="white" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                        <!-- Body -->
                        <rect x="14" y="46" width="72" height="54" rx="8" fill="white" fill-opacity="0.15" stroke="white" stroke-width="5" />
                        <!-- Keyhole circle -->
                        <circle cx="50" cy="69" r="8" stroke="white" stroke-width="4" fill="none" />
                        <!-- Keyhole stem -->
                        <line x1="50" y1="77" x2="50" y2="88" stroke="white" stroke-width="4" stroke-linecap="round" />
                    </svg>
                </span>

                <span>3</span>
            </div>

            <!-- Heading -->
            <h1 class="anim anim-2 text-xl md:text-2xl font-bold tracking-wide mb-4"
                style="font-family:'Montserrat',sans-serif;">
                Access Denied
            </h1>

            <!-- Body copy -->
            <p class="anim anim-3 text-sm md:text-base text-white/70 leading-relaxed mb-10 max-w-lg mx-auto">
                You don't have permission to view this page. If you believe this is a mistake,
                please contact the site administrator or try logging in with the correct account.
            </p>

            <!-- Actions -->
            <div class="anim anim-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="/"
                    class="inline-block bg-[#2d3ac4] hover:bg-[#2430b0] text-white text-sm font-semibold px-8 py-3 rounded transition-colors duration-200">
                    Back to Homepage
                </a>
                <a href="/login"
                    class="inline-block bg-[#5b63d3] hover:bg-[#4a52c4] text-white text-sm font-semibold px-8 py-3 rounded transition-colors duration-200">
                    Sign In
                </a>
            </div>

        </div>
    </div>

</body>

</html>
