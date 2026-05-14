# Project Architecture Guidelines

This project follows modern Laravel 13 architecture practices focused on maintainability, scalability, and AI-driven workflows.

The goal is to keep the codebase production-ready, testable, and easy to extend.

---

# Core Principles

- Follow Laravel conventions first
- Prefer simplicity over abstraction
- Keep classes focused and cohesive
- Prefer explicitness over magic
- Optimize for readability and maintainability
- Avoid premature optimization

---

# Architectural Style

The application uses a layered architecture with domain-oriented organization.

## Layers

### Presentation Layer

Responsible for:

- HTTP controllers
- Livewire components
- API resources
- Form requests

This layer should only orchestrate requests and responses.

Business logic must never live here.

---

### Application Layer

Responsible for:

- actions
- services
- workflows
- orchestration
- AI coordination

Examples:

- AnalyzeTicket
- GenerateAiReply
- FindSimilarTickets

---

### Domain Layer

Responsible for core business concepts:

- entities
- enums
- DTOs
- domain services
- policies
- business rules

Domain logic must remain framework-agnostic where reasonable.

---

### Infrastructure Layer

Responsible for external systems:

- AI providers
- queues
- embeddings
- vector search
- Redis
- external APIs

Infrastructure concerns must not leak into domain logic.

---

# Folder Structure

Preferred structure:

app/
├── Actions/
├── Data/
├── Enums/
├── Events/
├── Exceptions/
├── Http/
├── Jobs/
├── Listeners/
├── Livewire/
├── Models/
├── Policies/
├── Providers/
├── Services/
│   ├── AI/
│   ├── Embeddings/
│   └── Search/
├── Support/
└── ValueObjects/

---

# Design Patterns

Use patterns pragmatically.

Do not introduce patterns unless they solve a real problem.

---

## Service Pattern

Use services for reusable business workflows.

Examples:

- TicketAnalyzer
- AiReplyGenerator
- EmbeddingGenerator

Services should:

- have a single responsibility
- avoid framework coupling where possible
- use constructor injection

---

## Action Pattern

Use actions for focused business operations.

Examples:

- CreateTicket
- ResolveTicket
- GenerateTicketEmbedding

Actions should:

- perform one business operation
- be easily testable
- avoid side effects outside their responsibility

---

## Strategy Pattern

Use strategy pattern when multiple interchangeable behaviors exist.

Examples:

- AI provider selection
- embedding provider selection
- search ranking algorithms

---

## Factory Pattern

Use factories for:

- DTO creation
- AI response normalization
- provider instantiation

---

## DTO Pattern

Use DTOs extensively.

All structured data passed between layers should use DTOs.

Avoid raw arrays for business data.

Examples:

- TicketAnalysisData
- AiReplyData
- SearchResultData

DTOs should:

- be immutable where possible
- use typed properties
- contain no business logic

---

## Repository Pattern

Avoid repositories unless:
- multiple data sources exist
- persistence complexity becomes significant

Prefer Eloquent directly in most cases.

---

# Event-Driven Architecture

Use events when workflows become asynchronous or cross-domain.

Examples:

- TicketCreated
- TicketAnalyzed
- AiReplyGenerated
- EmbeddingGenerated

Prefer events for:

- queue workflows
- decoupling
- async processing
- side effects

Avoid excessive event fragmentation.

---

# AI Architecture

AI integrations must remain isolated.

Never place prompts directly inside:
- controllers
- Livewire components
- models

Use dedicated AI services.

Examples:

- TicketAnalyzer
- ConversationSummarizer
- SimilarTicketFinder

---

## Prompt Rules

Prompts must:

- be deterministic
- have explicit output expectations
- avoid ambiguity
- prefer structured outputs

Prompts should be versionable and reusable.

---

## Structured Outputs

Prefer typed structured outputs over free-text parsing.

Use DTOs or Laravel Data objects.

Never parse AI responses using regex unless unavoidable.

---

# Queue Architecture

All AI operations must run asynchronously unless low latency is required.

Use jobs for:
- AI analysis
- embeddings generation
- semantic indexing
- document processing

Jobs must:
- be idempotent where possible
- handle retries gracefully
- log failures appropriately

---

# Livewire Guidelines

Prefer server-driven UI.

Livewire components should:
- remain thin
- delegate business logic to actions/services
- avoid direct AI orchestration

Avoid large stateful components.

---

# Model Rules

Models should:

- represent persistence state
- contain relationships
- contain small domain helpers

Models should NOT:
- contain AI logic
- contain orchestration logic
- contain large business workflows

---

# Validation Rules

Use Form Requests for all validation.

Never validate directly in controllers or Livewire components.

---

# Enum Usage

Use PHP enums instead of magic strings.

Examples:

- TicketStatus
- TicketPriority
- AiTaskType

---

# Testing Philosophy

Every feature must be tested.

Use:
- Pest
- feature tests
- unit tests
- integration tests for AI workflows

Mock external AI providers where appropriate.

---

# AI Testing

AI workflows must support:
- fake responses
- deterministic tests
- snapshot-style verification where useful

Avoid tests that depend on live AI APIs.

---

# Performance Guidelines

Optimize only after correctness and clarity.

Prefer:
- eager loading
- queued processing
- caching for expensive AI operations

Avoid:
- premature caching
- unnecessary abstractions

---

# Error Handling

Use domain-specific exceptions where useful.

Avoid:
- silent failures
- broad catch blocks
- swallowing exceptions

AI failures must:
- be logged
- be retryable where appropriate
- fail gracefully for users

---

# Security Guidelines

Never trust AI-generated content.

Always:
- validate outputs
- sanitize rendered content
- authorize all actions
- escape user-generated data

---

# Code Quality Standards

Required:
- typed properties
- return types
- constructor injection
- small focused methods

Preferred:
- readonly DTOs
- early returns
- composition over inheritance

Avoid:
- god classes
- deep inheritance
- static state
- hidden side effects

---

# Definition of Good Code

Good code is:

- understandable
- testable
- predictable
- cohesive
- easy to change

Not:
- overly abstract
- pattern-heavy
- artificially generic

---

# Future Architecture Direction

The system should remain extensible for:

- multi-agent AI workflows
- RAG pipelines
- vector search
- real-time streaming
- multi-provider AI support
- background processing pipelines
- internal knowledge bases
- semantic search
- audit logging
```
