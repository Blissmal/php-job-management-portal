<?php http_response_code(405); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>405 – Method Not Allowed</title>
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

    /* Wobble on the slash icon */
    @keyframes wobble {

      0%,
      100% {
        transform: rotate(0deg);
      }

      20% {
        transform: rotate(-12deg);
      }

      40% {
        transform: rotate(10deg);
      }

      60% {
        transform: rotate(-6deg);
      }

      80% {
        transform: rotate(4deg);
      }
    }

    .wobble {
      animation: wobble 3.5s ease-in-out infinite;
      transform-origin: center;
    }
  </style>
</head>

<body class="m-0 p-0 font-[Quicksand]">

  <div class="mosaic-bg min-h-screen w-full flex items-center justify-center px-4">
    <div class="text-center text-white max-w-2xl mx-auto">

      <!-- 4⊘5 headline -->
      <div class="anim anim-1 flex items-center justify-center leading-none mb-6 select-none"
        style="font-family:'Montserrat',sans-serif; font-weight:900; font-size: clamp(5rem, 18vw, 9rem); letter-spacing: -0.02em;">
        <span>4</span>

        <!-- Prohibited / no-entry icon replacing the "0" -->
        <span class="wobble relative inline-flex items-center justify-center mx-2" style="width:1em; height:1em;">
          <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg"
            style="width:100%; height:100%;">
            <!-- Outer circle -->
            <circle cx="50" cy="50" r="44" stroke="white" stroke-width="5.5" fill="white" fill-opacity="0.08" />
            <!-- Diagonal strike-through bar -->
            <line x1="19" y1="81" x2="81" y2="19" stroke="white" stroke-width="7" stroke-linecap="round" />
          </svg>
        </span>

        <span>5</span>
      </div>

      <!-- Heading -->
      <h1 class="anim anim-2 text-xl md:text-2xl font-bold tracking-wide mb-4"
        style="font-family:'Montserrat',sans-serif;">
        Method Not Allowed
      </h1>

      <!-- Body copy -->
      <p class="anim anim-3 text-sm md:text-base text-white/70 leading-relaxed mb-10 max-w-lg mx-auto">
        The request method used is not supported for this URL. This usually means a form
        was submitted in an unexpected way or a URL was called directly that only accepts
        a different HTTP method.
      </p>

      <!-- Actions -->
      <div class="anim anim-4 flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="/"
          class="inline-block bg-[#2d3ac4] hover:bg-[#2430b0] text-white text-sm font-semibold px-8 py-3 rounded transition-colors duration-200">
          Back to Homepage
        </a>
        <a href="javascript:history.back()"
          class="inline-block bg-[#5b63d3] hover:bg-[#4a52c4] text-white text-sm font-semibold px-8 py-3 rounded transition-colors duration-200">
          Go Back
        </a>
      </div>

    </div>
  </div>

</body>

</html>
