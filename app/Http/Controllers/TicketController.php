<?php

namespace App\Http\Controllers;

use App\Actions\Tickets\CreateTicket;
use App\Actions\Tickets\DeleteTicket;
use App\Actions\Tickets\ListTickets;
use App\Actions\Tickets\UpdateTicket;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Ticket::class, 'ticket');
    }

    public function index(Request $request, ListTickets $listTickets): View
    {
        return view('tickets.index', [
            'tickets' => $listTickets->handle($request->user()),
        ]);
    }

    public function create(): View
    {
        return view('tickets.create');
    }

    public function store(StoreTicketRequest $request, CreateTicket $createTicket): RedirectResponse
    {
        $ticket = $createTicket->handle($request->user(), $request->validated());

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'ticket-created');
    }

    public function show(Ticket $ticket): View
    {
        return view('tickets.show', [
            'ticket' => $ticket,
        ]);
    }

    public function edit(Ticket $ticket): View
    {
        return view('tickets.edit', [
            'ticket' => $ticket,
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket, UpdateTicket $updateTicket): RedirectResponse
    {
        $updateTicket->handle($ticket, $request->validated());

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'ticket-updated');
    }

    public function destroy(Ticket $ticket, DeleteTicket $deleteTicket): RedirectResponse
    {
        $deleteTicket->handle($ticket);

        return redirect()
            ->route('tickets.index')
            ->with('status', 'ticket-deleted');
    }
}
