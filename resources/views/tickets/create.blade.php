<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Ticket') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl bg-white p-6 shadow-sm sm:p-8">
                <form method="POST" action="{{ route('tickets.store') }}" class="space-y-6">
                    @csrf

                    @include('tickets._form')

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Create Ticket') }}</x-primary-button>

                        <a href="{{ route('tickets.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
