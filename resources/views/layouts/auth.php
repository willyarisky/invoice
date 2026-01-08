<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Sign In' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Space+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      :root {
        --auth-cream: #f6f1e7;
        --auth-ink: #1c1917;
      }
      body {
        font-family: "Space Grotesk", system-ui, -apple-system, "Segoe UI", sans-serif;
        color: var(--auth-ink);
        background: var(--auth-cream);
      }
      .font-display {
        font-family: "Fraunces", "Times New Roman", serif;
      }

      button,
      input,
      select,
      textarea {
        border-radius: 0.5rem !important;
      }

      a.rounded-lg {
        border-radius: 0.5rem !important;
      }
    </style>
  </head>
  <body class="min-h-screen antialiased">
    @php
      $businessName = \App\Models\Setting::getValue('business_name');
      $brandName = $businessName !== '' ? $businessName : 'Simple Invoice Suite';
    @endphp
    <div class="relative min-h-screen overflow-hidden">
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-32 left-1/3 h-72 w-72 rounded-lg bg-amber-200/70 blur-3xl"></div>
        <div class="absolute top-1/2 right-10 h-80 w-80 rounded-lg bg-emerald-200/60 blur-3xl"></div>
        <div class="absolute bottom-0 left-10 h-64 w-64 rounded-lg bg-sky-200/60 blur-3xl"></div>
      </div>

      <div class="relative mx-auto grid min-h-screen max-w-6xl gap-8 px-6 py-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
        <div class="space-y-6">
          <div class="inline-flex items-center gap-2 rounded-lg border border-stone-200/70 bg-white/70 px-4 py-2 text-xs uppercase tracking-[0.3em] text-stone-500">
            {{ $brandName }}
          </div>
          <h1 class="font-display text-4xl font-semibold text-stone-900 sm:text-5xl">
            Your invoices, beautifully organized.
          </h1>
          <p class="max-w-xl text-base text-stone-600 sm:text-lg">
            Keep your billing workflow calm and controlled. Track outstanding invoices, manage clients, and share updates
            with your team from one place.
          </p>
          <div class="flex flex-wrap gap-3 text-sm text-stone-500">
            <span class="rounded-lg border border-stone-200/80 bg-white/70 px-4 py-2">Secure access</span>
            <span class="rounded-lg border border-stone-200/80 bg-white/70 px-4 py-2">Live status tracking</span>
            <span class="rounded-lg border border-stone-200/80 bg-white/70 px-4 py-2">Custom billing</span>
          </div>
        </div>

        <div class="rounded-lg border border-stone-200/70 bg-white/85 p-6 shadow-2xl shadow-stone-200/60 backdrop-blur sm:p-8">
          @yield('content')
        </div>
      </div>
    </div>
  </body>
</html>
