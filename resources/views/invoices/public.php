@include('components.head', ['title' => $pageTitle ?? 'Invoice'])
<div class="mx-auto w-full max-w-[960px] px-6 py-10">
    @include('invoices/partials/detail', $detailView ?? [])
</div>
@include('components.footer')
