# RAG

POC RAG (Retrieval Augmented Generation) en Symfony — démontre l'intégration d'un LLM dans un stack PHP/Symfony pour Q&A sur documents propriétaires.

## Architecture cible

- Backend : Symfony 7 / PHP 8.3
- Vector store : PostgreSQL 16 + extension pgvector
- LLM : OpenAI API (gpt-4o-mini pour completion, text-embedding-3-small pour embeddings)
- Ingestion : script Python (lecture PDF → chunking → embeddings → insert pgvector)
- API : endpoint `POST /ask` (question → embedding → recherche cosine → contexte → LLM → réponse)
- Run local : `docker compose up` (Postgres+pgvector) + `symfony serve`

## Roadmap MVP

- [ ] Bootstrap Symfony + Docker compose Postgres/pgvector
- [ ] Endpoint `/ask` qui appelle OpenAI sans contexte (premier flow E2E)
- [ ] Script Python d'ingestion : 1 PDF → chunks → embeddings → pgvector
- [ ] Endpoint `/ask` enrichi : recherche vectorielle + injection contexte au prompt
- [ ] README démo avec exemple question/réponse

## Pourquoi ce projet

Démontrer qu'on peut livrer un cas d'usage IA appliqué end-to-end, sans se reconvertir en data scientist et en utilisant Symfony.
