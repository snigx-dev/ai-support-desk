# AI Support Desk — Technical Specification

## Project Overview

AI Support Desk is a Laravel 13 application that helps support teams process customer tickets using AI.

The system allows users to:

- create support tickets
- receive AI-generated summaries
- classify ticket priority
- suggest AI replies
- search similar historical tickets
- stream AI responses in real time

---

# Tech Stack

## Backend

- PHP 8.3+
- Laravel 13
- Laravel AI SDK
- PostgreSQL
- Redis
- Laravel Queues

## Frontend

- Blade + Livewire
- TailwindCSS

## Testing

- Pest PHP

---

# Architecture Rules

## General Principles

- Follow Laravel conventions
- Prefer readability over cleverness
- Keep controllers thin
- Business logic belongs in services/actions
- Use dependency injection everywhere possible
- Avoid static helper abuse

---

# Controllers

Controllers must:

- only orchestrate requests
- validate through Form Requests
- delegate business logic to services/actions

Controllers must NOT:

- contain AI prompts
- contain database business logic
- contain complex conditionals

---

# Validation

- Use Form Request classes for all validation
- Never validate directly in controllers

---

# Business Logic

Business logic must live in:

- Services
- Actions
- Domain classes

Examples:

- TicketAnalyzer
- GenerateAiReply
- FindSimilarTickets

---

# AI Integration Rules

## AI Access

Never call AI facade directly inside controllers.

Use dedicated services:

- TicketAnalyzer
- AiReplyGenerator
- EmbeddingGenerator

---

## Structured Outputs

Prefer structured outputs using DTO/Data objects.

AI responses should never be parsed manually with regex.

---

## Prompting

Prompts must:

- be deterministic
- be concise
- avoid unnecessary verbosity
- include explicit output expectations

Prompts must NOT:

- contain HTML
- contain markdown unless required
- request unstructured responses

---

# Queue Rules

All AI processing must run in queues.

Examples:

- ticket analysis
- embeddings generation
- AI reply generation

Avoid synchronous AI calls in HTTP requests.

---

# Database Rules

- Use PostgreSQL
- Use UUIDs for public models where appropriate
- Use foreign key constraints
- Avoid polymorphic relations unless necessary

---

# Ticket Domain

## Ticket Statuses

- open
- in_progress
- resolved
- closed

## Ticket Priorities

- low
- medium
- high
- urgent

---

# Code Style

## Prefer

- small focused classes
- constructor injection
- early returns
- enums
- typed properties
- readonly DTOs where possible

## Avoid

- facades inside domain logic
- giant service classes
- deeply nested conditionals
- magic arrays

---

# Testing Rules

Every feature must include tests.

## Use

- Pest
- Feature tests
- Unit tests for services
- AI fakes/mocks

## Test Coverage Expectations

Must test:

- authorization
- validation
- queue dispatching
- AI workflows
- failed AI responses

---

# Frontend Rules

- Keep UI minimal and functional
- Prefer server-driven interactions
- Use Livewire for dynamic behavior
- Avoid unnecessary JavaScript

---

# Definition of Done

A task is complete only if:

- code follows architecture rules
- tests pass
- no debug code remains
- Pint passes
- PHPStan passes
- feature works end-to-end

---

# Forbidden Patterns

Do NOT:

- put business logic in controllers
- call AI directly from controllers
- use raw SQL unless necessary
- duplicate prompt logic
- create god services
- skip tests

---

# Preferred Folder Structure

app/
├── Actions/
├── Data/
├── Enums/
├── Jobs/
├── Services/
│   └── AI/
├── Livewire/
├── Policies/
└── Support/

---

# AI Features Planned

## MVP

- Ticket CRUD
- AI ticket summary
- AI priority classification
- AI reply suggestions

## Phase 2

- embeddings
- semantic search
- conversation history
- streaming AI responses

## Phase 3

- knowledge base RAG
- internal notes
- multi-agent workflows
