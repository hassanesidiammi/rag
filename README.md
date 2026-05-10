# RAG

POC RAG (Retrieval Augmented Generation) en Symfony — démontre l'intégration d'un LLM dans un stack PHP/Symfony pour Q&A sur documents propriétaires.

## Architecture cible

- Backend : Symfony 8 / PHP 8.4
- Vector store : PostgreSQL 16 + extension pgvector
- LLM : provider-agnostic (OpenAI ou Mistral, sélectionné via `LLM_PROVIDER`)
- Ingestion : script Python (lecture PDF → chunking → embeddings → insert pgvector)
- API : endpoint `POST /ask` (question → embedding → recherche cosine → contexte → LLM → réponse)
- Run local : `docker compose up -d` (Postgres+pgvector) + `symfony serve`

## Roadmap MVP

- [x] Bootstrap Symfony + Docker compose Postgres/pgvector
- [x] Endpoint `/ask` qui appelle un LLM sans contexte (premier flow E2E)
- [ ] Script Python d'ingestion : 1 PDF → chunks → embeddings → pgvector
- [ ] Endpoint `/ask` enrichi : recherche vectorielle + injection contexte au prompt
- [ ] README démo avec exemple question/réponse

## Quick start

```bash
docker compose up -d                  # Postgres 16 + pgvector
composer install
cp .env .env.local                    # puis renseigne OPENAI_API_KEY ou MISTRAL_API_KEY
symfony serve --no-tls
```

Test :

```bash
curl -X POST http://localhost:8000/ask \
  -H "Content-Type: application/json" \
  -d '{"question":"Qu est-ce qu un RAG en une phrase ?"}'
```

## Pourquoi ce projet

Démontrer qu'on peut livrer un cas d'usage IA appliqué end-to-end, sans se reconvertir en data scientist et en utilisant Symfony.
