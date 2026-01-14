@include('components.head', ['title' => $title ?? 'Sign In'])
<div class="min-h-screen bg-stone-50">
  <div class="mx-auto flex min-h-screen w-full max-w-[1200px] flex-col px-6 py-8">

    <div class="flex flex-1 items-center justify-center py-10">
      <div class="w-full max-w-md rounded-2xl border border-stone-200 bg-white px-6 py-6 shadow-sm">
        @yield('content')
      </div>
    </div>

    <footer class="text-xs text-center font-semibold uppercase tracking-widest text-stone-400">
      {{ $brandName }}
    </footer>
  </div>
</div>
@include('components.footer')
