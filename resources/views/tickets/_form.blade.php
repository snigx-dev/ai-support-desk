@php
    use App\Enums\TicketPriority;
    use App\Enums\TicketStatus;

    $ticket ??= null;
    $selectedStatus = old('status', $ticket?->status?->value ?? TicketStatus::Open->value);
    $selectedPriority = old('priority', $ticket?->priority?->value);
@endphp

<div class="grid gap-6">
    <div>
        <x-input-label for="title" :value="__('Title')" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $ticket?->title)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('title')" />
    </div>

    <div>
        <x-input-label for="message" :value="__('Message')" />
        <textarea id="message" name="message" rows="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('message', $ticket?->message) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('message')" />
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="status" :value="__('Status')" />
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (TicketStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>
                        {{ __(str_replace('_', ' ', $status->value)) }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('status')" />
        </div>

        <div>
            <x-input-label for="priority" :value="__('Priority')" />
            <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('None') }}</option>
                @foreach (TicketPriority::cases() as $priority)
                    <option value="{{ $priority->value }}" @selected($selectedPriority === $priority->value)>
                        {{ __(str_replace('_', ' ', $priority->value)) }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('priority')" />
        </div>
    </div>
</div>
