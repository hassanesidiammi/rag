#!/usr/bin/env python3
"""Ingest a PDF into the documents table: parse, chunk, embed, insert."""

from __future__ import annotations

import argparse
import os
import sys
from pathlib import Path

import psycopg
from dotenv import load_dotenv
from mistralai import Mistral
from pgvector.psycopg import register_vector
from pypdf import PdfReader

ROOT = Path(__file__).resolve().parent.parent
load_dotenv(ROOT / ".env")
load_dotenv(ROOT / ".env.local", override=True)

EMBEDDING_MODEL = "mistral-embed"
CHUNK_SIZE = 1000
CHUNK_OVERLAP = 200
EMBED_BATCH_SIZE = 32


def extract_text(pdf_path: Path) -> str:
    reader = PdfReader(pdf_path)
    return "\n\n".join(page.extract_text() or "" for page in reader.pages)


def chunk_text(text: str, size: int = CHUNK_SIZE, overlap: int = CHUNK_OVERLAP) -> list[str]:
    text = " ".join(text.split())  # normalize whitespace
    if not text:
        return []
    step = size - overlap
    return [text[i : i + size] for i in range(0, len(text), step) if text[i : i + size].strip()]


def embed(client: Mistral, chunks: list[str]) -> list[list[float]]:
    vectors: list[list[float]] = []
    for start in range(0, len(chunks), EMBED_BATCH_SIZE):
        batch = chunks[start : start + EMBED_BATCH_SIZE]
        resp = client.embeddings.create(model=EMBEDDING_MODEL, inputs=batch)
        vectors.extend(item.embedding for item in resp.data)
    return vectors


def db_dsn() -> str:
    url = os.environ.get("DATABASE_URL")
    if not url:
        sys.exit("DATABASE_URL is not set (check rag/.env and rag/.env.local)")
    # Strip Doctrine-specific query params (serverVersion, charset).
    return url.split("?", 1)[0]


def insert_rows(
    conn: psycopg.Connection,
    source: str,
    chunks: list[str],
    embeddings: list[list[float]],
    replace: bool,
) -> int:
    with conn.cursor() as cur:
        if replace:
            cur.execute("DELETE FROM documents WHERE source = %s", (source,))
        cur.executemany(
            "INSERT INTO documents (source, chunk_index, content, embedding) VALUES (%s, %s, %s, %s)",
            [(source, i, content, vec) for i, (content, vec) in enumerate(zip(chunks, embeddings))],
        )
    conn.commit()
    return len(chunks)


def main() -> int:
    parser = argparse.ArgumentParser(description="Ingest a PDF into pgvector.")
    parser.add_argument("pdf", type=Path, help="Path to the PDF file.")
    parser.add_argument("--source", help="Logical source name. Defaults to the PDF filename.")
    parser.add_argument(
        "--replace",
        action="store_true",
        help="Delete existing rows for this source before inserting.",
    )
    args = parser.parse_args()

    if not args.pdf.is_file():
        sys.exit(f"PDF not found: {args.pdf}")

    api_key = os.environ.get("MISTRAL_API_KEY")
    if not api_key:
        sys.exit("MISTRAL_API_KEY is not set (put it in rag/.env.local)")

    source = args.source or args.pdf.name

    print(f"-> Extracting text from {args.pdf}")
    text = extract_text(args.pdf)
    print(f"   {len(text)} characters")

    print(f"-> Chunking (size={CHUNK_SIZE}, overlap={CHUNK_OVERLAP})")
    chunks = chunk_text(text)
    print(f"   {len(chunks)} chunks")

    if not chunks:
        sys.exit("No content extracted — aborting.")

    print(f"-> Embedding via {EMBEDDING_MODEL}")
    client = Mistral(api_key=api_key)
    embeddings = embed(client, chunks)
    print(f"   {len(embeddings)} vectors of dim {len(embeddings[0])}")

    print(f"-> Inserting into documents (source={source!r}, replace={args.replace})")
    with psycopg.connect(db_dsn()) as conn:
        register_vector(conn)
        n = insert_rows(conn, source, chunks, embeddings, args.replace)
    print(f"   {n} rows inserted")
    print("Done.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
