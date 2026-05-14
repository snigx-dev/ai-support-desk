<?php

namespace Tests\Feature\Ticket;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CreateTicketTest extends TestCase
{
    use DatabaseMigrations;

    public function test_creates_a_ticket_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tickets.store'), [
            'title' => 'Cannot access the portal',
            'message' => 'The portal returns a 500 error whenever I try to sign in.',
            'status' => TicketStatus::Open->value,
            'priority' => TicketPriority::High->value,
        ]);

        $ticket = $user->tickets()->firstOrFail();

        $response
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $this->assertSame('Cannot access the portal', $ticket->title);
        $this->assertSame('The portal returns a 500 error whenever I try to sign in.', $ticket->message);
        $this->assertSame(TicketStatus::Open, $ticket->status);
        $this->assertSame(TicketPriority::High, $ticket->priority);
    }

    public function test_validates_ticket_input_on_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tickets.store'), [
            'title' => '',
            'message' => 'short',
            'status' => 'invalid',
            'priority' => 'invalid',
        ]);

        $response->assertSessionHasErrors(['title', 'message', 'status', 'priority']);
    }
}
