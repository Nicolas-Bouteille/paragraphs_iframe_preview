## Problem

When editing a node, paragraphs can be collapsed — either by default or by clicking the **Collapse** button.

In the *Form display* settings of a paragraphs field, you can choose what should be shown when a paragraph is collapsed:
- a summary
- or a preview

When the **Preview** option is selected, the preview is rendered using the **admin theme**.
As a result, the preview does not accurately reflect how the paragraph will look on the frontend.

---

## Solution

The goal of this module is to render paragraph previews using the **frontend theme**, so the preview is as faithful as possible to the final output.

To achieve this, the paragraph preview is rendered inside an **iFrame**.

Note that it also works with paragraphs embedded inside other paragraphs.

For an optimal user experience, you may want to add specific CSS rules that apply to the iframe preview context.
The iframe is surrounded with a div that has the class .paragraph-iframe-preview as well as the paragraph bundle.
By default, the iframe is 500px height, but you can adjust this by bundle from your admin theme's css. ex:
```
.paragraph-iframe-preview.comment iframe {
  height: 150px;
}

```
The body inside the iframe has .path-paragraph-iframe-preview class.
You could define a `max-width` for individual paragraphs that are meant to be rendered inline alongside others and are not intended to span the full width.
```
body.path-paragraph-iframe-preview {
  .paragraph--type--card {
    max-width: 300px;
  }
}
```

---

## How it works

We override `paragraph--preview.html.twig` to render the paragraph inside an iFrame

---

### Handling unsaved paragraphs

When a node has not been saved yet, the paragraph entity does not exist in the database.
However, the iframe preview must still work when collapsing a paragraph during node creation or editing.
For that reason, inside the iframe, we cannot just render the paragraph from its ID.

To solve this, the paragraph entity is temporarily stored using the same mechanism as Drupal’s node preview system,
using the `PrivateTempStoreFactory` that relies on the `key_value_expire` table, with:

- collection: `tempstore.private.paragraph_preview`
- storage key: the paragraph **UUID** (since the paragraph may not have an ID yet)

The temporary storage is handled in `template_preprocess_paragraph__preview()`

---

### Rendering the iframe preview

Inside the iFrame, we call a custom route `paragraphs_iframe_preview.paragraph_iframe_preview`
that is handled by `ParagraphsIframePreviewController::paragraphIframePreview()`

In this controller:
- the paragraph data is retrieved from `PrivateTempStoreFactory`
- the paragraph is rendered using `TwigEnvironment::renderInline()`

This ensures the paragraph is rendered exactly as it would be on the frontend.

---

### Minimal page rendering for the iframe

Because the iframe renders a full page, the site’s header and footer are not needed for the paragraph preview.

In `template_preprocess_html()`, we unset `page_top` and `page_bottom`, as well as some `attributes` classes that basically go on the body

We also override `page.html.twig` only for our iframe context with:

- `page--paragraph-iframe-preview.html.twig`
that only contains {{ page.content }}

This results in a clean, lightweight preview focused solely on the paragraph content.
