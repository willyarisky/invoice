<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Simple Invoice App' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              brand: {
                DEFAULT: '#1c1917',
              },
            },
          },
        },
      }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
      [x-cloak] {
        display: none !important;
      }

      button,
      input,
      select,
      textarea {
        border-radius: 0.5rem !important;
      }

      a.rounded-xl {
        border-radius: 0.5rem !important;
      }
    </style>
  </head>
  <body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
