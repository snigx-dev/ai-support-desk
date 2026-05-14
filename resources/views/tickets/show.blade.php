<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $ticket->title }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Ticket details') }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('tickets.edit', $ticket) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    {{ __('Edit') }}
                </a>

                <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" onsubmit="return confirm('{{ __('Delete this ticket?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-red-600 ring-1 ring-inset ring-red-200 transition hover:bg-red-50">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            @if (session('status') === 'ticket-created' || session('status') === 'ticket-updated')
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') === 'ticket-created' ? __('Ticket created.') : __('Ticket updated.') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="grid gap-6 p-6 sm:p-8">
                    <div class="flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full bg-gray-100 px-3 py-1 font-medium text-gray-700">
                            {{ __(str_replace('_', ' ', $ticket->status->value)) }}
                        </span>

                        <span class="rounded-full bg-indigo-50 px-3 py-1 font-medium text-indigo-700">
                            {{ $ticket->priority ? __(str_replace('_', ' ', $ticket->priority->value)) : __('No priority') }}
                        </span>

                        @if ($ticket->ai_category)
                            <span class="rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700">
                                {{ $ticket->ai_category }}
                            </span>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                            {{ __('Message') }}
                        </h3>
                        <p class="mt-3 whitespace-pre-line text-gray-900">
                            {{ $ticket->message }}
                        </p>
                    </div>

                    @if ($ticket->ai_summary)
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('AI Summary') }}
                            </h3>
                            <p class="mt-3 whitespace-pre-line text-gray-900">
                                {{ $ticket->ai_summary }}
                            </p>
                        </div>
                    @endif

                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Created') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $ticket->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Updated') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $ticket->updated_at->format('M j, Y g:i A') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
