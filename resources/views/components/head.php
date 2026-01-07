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
  </head>
  <body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
