<?php

namespace Tests\Feature\Ticket;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TicketCrudTest extends TestCase
{
    use DatabaseMigrations;

    public function test_requires_authentication_for_ticket_routes(): void
    {
        $this->get(route('tickets.index'))->assertRedirect(route('login'));
        $this->get(route('tickets.create'))->assertRedirect(route('login'));
    }

    public function test_lists_only_the_authenticated_users_tickets(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownTicket = Ticket::factory()->for($user)->create([
            'title' => 'My ticket',
        ]);

        Ticket::factory()->for($otherUser)->create([
            'title' => 'Other ticket',
        ]);

        $this->actingAs($user)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee($ownTicket->title)
            ->assertDontSee('Other ticket');
    }

    public function test_shows_a_users_own_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee($ticket->title);
    }

    public function test_forbids_viewing_another_users_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAs($user)
            ->get(route('tickets.show', $ticket))
            ->assertForbidden();
    }

    public function test_updates_an_owned_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->for($user)->create([
            'status' => TicketStatus::Open,
            'priority' => TicketPriority::Low,
        ]);

        $response = $this->actingAs($user)->put(route('tickets.update', $ticket), [
            'title' => 'Updated title',
            'message' => 'Updated message with enough length.',
            'status' => TicketStatus::Resolved->value,
            'priority' => TicketPriority::Urgent->value,
        ]);

        $response
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $ticket->refresh();

        $this->assertSame('Updated title', $ticket->title);
        $this->assertSame('Updated message with enough length.', $ticket->message);
        $this->assertSame(TicketStatus::Resolved, $ticket->status);
        $this->assertSame(TicketPriority::Urgent, $ticket->priority);
    }

    public function test_forbids_updating_another_users_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAs($user)
            ->put(route('tickets.update', $ticket), [
                'title' => 'Updated title',
                'message' => 'Updated message with enough length.',
                'status' => TicketStatus::Resolved->value,
                'priority' => TicketPriority::Urgent->value,
            ])
            ->assertForbidden();
    }

    public function test_deletes_an_owned_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('tickets.destroy', $ticket))
            ->assertRedirect(route('tickets.index'))
            ->assertSessionHasNoErrors();

        $this->assertModelMissing($ticket);
    }

    public function test_forbids_deleting_another_users_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAs($user)
            ->delete(route('tickets.destroy', $ticket))
            ->assertForbidden();
    }
}
