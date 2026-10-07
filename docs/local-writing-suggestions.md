## Local Writing Suggestions

The Tiptap editor supports optional browser-side writing suggestions.

The implementation uses:

```text
@huggingface/transformers
SmolLM2-135M-Instruct
WebGPU
Web Worker
custom Tiptap GhostText extension
```

When enabled, Poet-Web:

1. waits briefly after the user stops typing;
2. extracts up to 500 characters before the cursor;
3. sends the context to a browser Web Worker;
4. generates a short continuation with the local model;
5. renders the result as non-persistent ghost text.

The user can:

```text
Tab → accept suggestion
Esc → dismiss suggestion
```

Suggestion generation does not use a Poet-Web Laravel endpoint or external AI text-generation API. The draft context is processed by the model running in the browser.

The feature requires WebGPU and automatically rejects stale model responses when the editor content changes before generation finishes.

See: [Local writing suggestions](local-writing-suggestions.md)