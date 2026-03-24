<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 – Page Not Found</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Quicksand:wght@400;500;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <style>
    /* ── Mosaic tile background ────────────────────────────────────
       Pure CSS recreation of the screenshot's square-tile grid.
       Each tile is a semi-transparent rectangle; we use a repeating
       gradient to fake alternating light/dark squares.              */
    .mosaic-bg {
      background-color: #1e2060;
      background-image:
        /* diagonal 45° gradient overlay (matches screenshot) */
        linear-gradient(135deg, #5b63d3cc 0%, #0c0e2acc 100%),
        /* tile grid – two-layer repeating pattern */
        repeating-linear-gradient(
          0deg,
          transparent,
          transparent 79px,
          rgba(255,255,255,0.04) 79px,
          rgba(255,255,255,0.04) 80px
        ),
        repeating-linear-gradient(
          90deg,
          transparent,
          transparent 79px,
          rgba(255,255,255,0.04) 79px,
          rgba(255,255,255,0.04) 80px
        ),
        /* individual tile fills – alternating bright/dark */
        repeating-linear-gradient(
          135deg,
          rgba(255,255,255,0.03) 0px,
          rgba(255,255,255,0.03) 80px,
          rgba(0,0,0,0.06) 80px,
          rgba(0,0,0,0.06) 160px
        );
    }

    /* ── Crosshair / target icon (replaces the 0) ──────────────── */
    .crosshair {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 1em;
      height: 1em;
    }
    .crosshair svg {
      width: 100%;
      height: 100%;
    }

    /* ── Staggered fade-up entrance ───────────────────────────── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(30px); }
      to   { opacity: 1; transform: translateY(0);    }
    }
    .anim { opacity: 0; animation: fadeUp 0.7s ease forwards; }
    .anim-1 { animation-delay: 0.1s; }
    .anim-2 { animation-delay: 0.25s; }
    .anim-3 { animation-delay: 0.4s; }
    .anim-4 { animation-delay: 0.55s; }
    .anim-5 { animation-delay: 0.7s; }

    /* ── Search input focus ring ──────────────────────────────── */
    .search-field:focus { outline: none; box-shadow: none; }
  </style>
</head>

<body class="m-0 p-0 font-[Quicksand]">

  <div class="mosaic-bg min-h-screen w-full flex items-center justify-center px-4">
    <div class="text-center text-white max-w-2xl mx-auto">

      <!-- 404 headline with crosshair zero -->
      <div class="anim anim-1 flex items-center justify-center leading-none mb-6 select-none"
           style="font-family:'Montserrat',sans-serif; font-weight:900; font-size: clamp(5rem, 18vw, 9rem); letter-spacing: -0.02em;">
        <span>4</span>

        <!-- Crosshair replacing the "0" -->
        <span class="relative inline-flex items-center justify-center mx-2" style="width:1em; height:1em;">
          <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg"
               style="width:100%; height:100%;">
            <!-- Outer circle -->
            <circle cx="50" cy="50" r="35" stroke="white" stroke-width="3.5"/>
            <!-- Inner circle -->
            <circle cx="50" cy="50" r="8"  stroke="white" stroke-width="3.5"/>
            <!-- Cross lines -->
            <line x1="50" y1="0"  x2="50" y2="15" stroke="white" stroke-width="3.5" stroke-linecap="round"/>
            <line x1="50" y1="85" x2="50" y2="100" stroke="white" stroke-width="3.5" stroke-linecap="round"/>
            <line x1="0"  y1="50" x2="15" y2="50" stroke="white" stroke-width="3.5" stroke-linecap="round"/>
            <line x1="85" y1="50" x2="100" y2="50" stroke="white" stroke-width="3.5" stroke-linecap="round"/>
          </svg>
        </span>

        <span>4</span>
      </div>

      <!-- Heading -->
      <h1 class="anim anim-2 text-xl md:text-2xl font-bold tracking-wide mb-4"
          style="font-family:'Montserrat',sans-serif;">
        We Are Sorry, Page Not Found
      </h1>

      <!-- Body copy -->
      <p class="anim anim-3 text-sm md:text-base text-white/70 leading-relaxed mb-10 max-w-lg mx-auto">
        Unfortunately the page you were looking for could not be found. It may be temporarily
        unavailable, moved or no longer exist. Check the URL you entered for any mistakes and try again.
      </p>

      <!-- Search bar -->
      <form role="search" method="get" action="/jobs"
            class="anim anim-4 flex items-stretch w-full max-w-xl mx-auto mb-6 rounded overflow-hidden shadow-lg">
        <input
          type="search"
          name="s"
          placeholder="Search …"
          class="search-field flex-1 bg-white text-gray-700 placeholder-gray-400 text-sm px-5 py-4 border-0"
        >
        <button type="submit"
                class="bg-[#5b63d3] hover:bg-[#4a52c4] text-white font-semibold text-sm px-7 py-4 transition-colors duration-200 cursor-pointer whitespace-nowrap">
          Search
        </button>
      </form>

      <!-- Back to homepage -->
      <div class="anim anim-5">
        <a href="/"
           class="inline-block bg-[#2d3ac4] hover:bg-[#2430b0] text-white text-sm font-semibold px-8 py-3 rounded transition-colors duration-200">
          Back to Homepage
        </a>
      </div>

    </div>
  </div>

</body>
</html>
