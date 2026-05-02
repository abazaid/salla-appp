# Blog Post Workflow

## Blog structure
- Source: `blog-src/`
- Posts (markdown): `blog-src/src/content/posts/`
- Built output: `blog/` (auto-deployed to rankxseo.com/blog)
- App reference for terminology: `blog-src/APP-REFERENCE.md`

## How to publish a blog post
When the user says "اكتب مقال" or gives a keyword/topic:

1. **Keyword research** — use WebSearch to find keyword data, search volume, competition
2. **Competitor analysis** — search Google for top-ranking articles on the topic
3. **Write** — create SEO-optimized Arabic article (1500-2500 words, Tajawal font, RTL, following APP-REFERENCE.md terminology)
4. **Create post file** — in `blog-src/src/content/posts/` with Arabic kebab-case filename (slug), e.g. `تحسين-متجر-العبايات.md` not `seo-abaya-store.md`
5. **Build** — run `cd blog-src && npm run build` — MUST run build and verify no errors
6. **Publish** — git add, commit, and push to deploy — MUST do all three, never skip

## Internal links
Always link to: https://rankxseo.com (main site), https://app.rankxseo.com (dashboard), and relevant service pages.
