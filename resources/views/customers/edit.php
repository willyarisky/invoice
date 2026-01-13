@layout('layouts.app', ['title' => 'Edit Customer'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Edit customer</h1>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('customers.index') }}" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                Back to customers
            </a>
        </div>
    </div>
    <div class="rounded-xl border border-stone-200 bg-white px-6 py-6 shadow-sm">

        <form method="POST" action="{{ route('customers.update', ['customer' => $customer['id']]) }}" class="mt-6 grid gap-4 lg:grid-cols-2">
            <label class="flex flex-col text-sm font-medium text-stone-700">
                Customer name
                <input type="text" name="name" value="{{ $values['name'] ?? '' }}" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                @if (isset($errors['name']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['name'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex flex-col text-sm font-medium text-stone-700">
                Email address
                <input type="email" name="email" value="{{ $values['email'] ?? '' }}" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                @if (isset($errors['email']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['email'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex flex-col text-sm font-medium text-stone-700 lg:col-span-2">
                Mailing address
                <textarea name="address" rows="4" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">{{ $values['address'] ?? '' }}</textarea>
                @if (isset($errors['address']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['address'] ?? '' }}</span>
                @endif
            </label>

            <div class="flex items-center justify-end gap-3 lg:col-span-2">
                <a href="{{ route('customers.index') }}" class="rounded-xl border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                    Cancel
                </a>
                <button type="submit" class="rounded-xl bg-stone-800 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
