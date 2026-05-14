<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Tickets') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Manage the tickets you have created.') }}
                </p>
            </div>

            <a href="{{ route('tickets.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                {{ __('New Ticket') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status') === 'ticket-created')
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ __('Ticket created.') }}
                </div>
            @elseif (session('status') === 'ticket-updated')
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ __('Ticket updated.') }}
                </div>
            @elseif (session('status') === 'ticket-deleted')
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ __('Ticket deleted.') }}
                </div>
            @endif

            <div class="grid gap-6">
                @forelse ($tickets as $ticket)
                    <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
                        <div class="p-6">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="hover:text-indigo-600">
                                            {{ $ticket->title }}
                                        </a>
                                    </h3>
                                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-gray-600">
                                        {{ $ticket->message }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2 text-xs font-medium">
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">
                                        {{ __(str_replace('_', ' ', $ticket->status->value)) }}
                                    </span>

                                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-indigo-700">
                                        {{ $ticket->priority ? __(str_replace('_', ' ', $ticket->priority->value)) : __('No priority') }}
                                    </span>

                                    @if ($ticket->ai_category)
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">
                                            {{ $ticket->ai_category }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-6 flex items-center justify-between gap-4">
                                <p class="text-sm text-gray-500">
                                    {{ __('Created :date', ['date' => $ticket->created_at->format('M j, Y')]) }}
                                </p>

                                <a href="{{ route('tickets.edit', $ticket) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                                    {{ __('Edit') }}
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('No tickets yet') }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ __('Create your first ticket to get started.') }}</p>
                        <div class="mt-6">
                            <a href="{{ route('tickets.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                                {{ __('New Ticket') }}
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
