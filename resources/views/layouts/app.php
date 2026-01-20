@include('components.head', ['title' => $title ?? $brandName])
<div class="min-h-screen bg-stone-50 ">
    <header class="border-b border-stone-200 bg-white text-stone-900 print:hidden">
        <div class="relative mx-auto flex w-full max-w-[1200px] flex-row items-center justify-between gap-4 px-6 py-4">
            <div class="flex items-center gap-3">
                <div>
                    <a href="/" class="text-lg font-semibold text-stone-900">{{ $brandName }}</a>
                </div>
            </div>
            <div class="flex flex-1 flex-row items-center justify-end gap-2">
                <div class="flex items-center gap-2">
                    @if ($currentUser)
                        <a href="{{ route('settings.index') }}" class="inline-flex items-center justify-center rounded-xl border border-stone-200 bg-white p-2 text-stone-700 hover:bg-stone-50 {{ !empty($settingsActive) ? 'bg-stone-100 text-stone-900 border-stone-300' : '' }}" aria-label="Settings">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-settings"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                        </a>
                        <form method="POST" action="{{ route('auth.logout') }}">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-stone-200 bg-white p-2 text-sm font-semibold text-stone-700 hover:bg-stone-50" aria-label="Log out">
                                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path d="M10 17l5-5-5-5M4 12h10M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('auth.login.show') }}" class="inline-flex items-center gap-2 border border-stone-200 bg-stone-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-stone-800">
                            <span>Sign in</span>
                        </a>
                    @endif
                </div>
                <details class="relative lg:hidden">
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                        <span>Menu</span>
                        <svg aria-hidden="true" class="h-4 w-4 transition group-open:rotate-180" viewBox="0 0 20 20" fill="none">
                            <path d="M5 7l5 5 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </summary>
                    <div class="fixed inset-0 z-40 flex items-end justify-center bg-black/40 px-4 pb-6" onclick="if (event.target === this) { this.closest('details').removeAttribute('open'); }">
                        <div class="w-full max-w-sm rounded-2xl border border-stone-200 bg-white px-4 py-4 shadow-xl">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-stone-900">Menu</p>
                                <button type="button" class="rounded-lg border border-stone-200 px-2 py-1 text-xs text-stone-500 hover:bg-stone-50" onclick="this.closest('details').removeAttribute('open')">Close</button>
                            </div>
                            <div class="mt-3 space-y-1">
                                @foreach ($navItems ?? [] as $item)
                                    <a href="{{ $item['href'] ?? '#' }}"
                                        class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium {{ !empty($item['isActive']) ? 'bg-stone-100 text-stone-900' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-800' }}"
                                        @if (!empty($item['isActive'])) aria-current="page" @endif>
                                        <span>{{ $item['label'] ?? '' }}</span>
                                        @if (!empty($item['isActive']))
                                            <span class="h-2 w-2 rounded-full bg-stone-900" aria-hidden="true"></span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </details>
            </div>
        </div>
        <nav class="mx-auto w-full max-w-[1200px] px-6 text-sm text-stone-500">
            <div class="hidden lg:flex gap-6 overflow-x-auto text-stone-500">
                @foreach ($navItems ?? [] as $item)
                    <a href="{{ $item['href'] ?? '#' }}" class="border-b-2 px-1 py-3 font-medium {{ !empty($item['isActive']) ? 'border-stone-900 text-stone-900' : 'border-transparent hover:border-stone-300 hover:text-stone-800' }}" @if (!empty($item['isActive'])) aria-current="page" @endif>
                        {{ $item['label'] ?? '' }}
                    </a>
                @endforeach
            </div>
        </nav>
    </header>
    <main class="mx-auto w-full md:min-h-[80vh] min-h-[87vh] max-w-[1200px] px-6 py-6">
        @yield('content')
    </main>
    <footer class="border-t border-stone-200 bg-white text-stone-500 print:hidden">
        <div class="mx-auto flex justify-between w-full max-w-[1200px] px-6 py-5 text-xs uppercase tracking-widest text-center">
            <span class="font-semibold">{{ $brandName }} &copy; {{ date('Y') }}</span>
            <span>V1.0.0</span>
        </div>
    </footer>
    <div id="app-modal-root" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 print:hidden" role="dialog" aria-modal="true" aria-labelledby="app-modal-title" aria-describedby="app-modal-message" aria-hidden="true">
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-stone-900 shadow-xl">
            <p class="text-lg font-semibold" id="app-modal-title" data-modal-title>Notice</p>
            <p class="mt-2 text-sm text-stone-600 whitespace-pre-line" id="app-modal-message" data-modal-message></p>
            <div class="mt-6 flex items-center justify-end gap-2">
                <button type="button" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50" data-modal-cancel>Cancel</button>
                <button type="button" class="rounded-xl bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-800" data-modal-confirm>OK</button>
            </div>
        </div>
    </div>
</div>
<script>
(() => {
    const root = document.getElementById('app-modal-root');
    if (!root) {
        return;
    }

    const titleEl = root.querySelector('[data-modal-title]');
    const messageEl = root.querySelector('[data-modal-message]');
    const cancelBtn = root.querySelector('[data-modal-cancel]');
    const confirmBtn = root.querySelector('[data-modal-confirm]');
    let resolver = null;
    let currentMode = 'alert';

    const openModal = ({ title, message, confirmText, cancelText, showCancel, mode }) => {
        currentMode = mode || 'alert';
        titleEl.textContent = title || 'Notice';
        messageEl.textContent = message || '';
        confirmBtn.textContent = confirmText || 'OK';
        cancelBtn.textContent = cancelText || 'Cancel';
        cancelBtn.classList.toggle('hidden', !showCancel);
        root.classList.remove('hidden');
        root.classList.add('flex');
        root.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        confirmBtn.focus();
    };

    const resolveAndClose = (value) => {
        if (resolver) {
            resolver(value);
            resolver = null;
        }
        root.classList.add('hidden');
        root.classList.remove('flex');
        root.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    confirmBtn.addEventListener('click', () => resolveAndClose(true));
    cancelBtn.addEventListener('click', () => resolveAndClose(false));
    root.addEventListener('click', (event) => {
        if (event.target === root) {
            resolveAndClose(currentMode === 'alert');
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !root.classList.contains('hidden')) {
            event.preventDefault();
            resolveAndClose(currentMode === 'alert');
        }
    });

    window.appAlert = (message, options = {}) => new Promise((resolve) => {
        resolver = resolve;
        openModal({
            title: options.title || 'Notice',
            message: message,
            confirmText: options.confirmText || 'OK',
            cancelText: options.cancelText || 'Cancel',
            showCancel: false,
            mode: 'alert',
        });
    });

    window.appConfirm = (message, options = {}) => new Promise((resolve) => {
        resolver = resolve;
        openModal({
            title: options.title || 'Confirm',
            message: message,
            confirmText: options.confirmText || 'Confirm',
            cancelText: options.cancelText || 'Cancel',
            showCancel: true,
            mode: 'confirm',
        });
    });

    window.alert = (message) => {
        window.appAlert(message);
    };

    window.confirm = (message) => {
        console.warn('window.confirm is async; use window.appConfirm instead.');
        window.appConfirm(message);
        return false;
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form || !form.hasAttribute('data-confirm')) {
            return;
        }
        event.preventDefault();
        const message = form.getAttribute('data-confirm') || 'Are you sure?';
        window.appConfirm(message).then((confirmed) => {
            if (confirmed) {
                form.submit();
            }
        });
    });
})();
</script>
@include('components.footer')
