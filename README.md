# Markdown Viewer

A lightweight, browser-based markdown editor and live preview tool with Mermaid diagram support.

## Features

- **Split-pane interface** — edit markdown on the left, see the rendered preview on the right in real time
- **Multiple file loading methods** — load a file by server path or upload directly from your computer
- **Mermaid diagrams** — render flowcharts, pie charts, and other diagrams from fenced code blocks
- **Font picker** — choose from 10 Google Fonts or enter any custom font name
- **Print / PDF export** — print the rendered preview with optimized print styles
- **Tab support** — the editor inserts spaces on Tab instead of losing focus
- **No build step** — all JS libraries (Marked, Mermaid) are loaded via CDN

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML, CSS, Vanilla JavaScript |
| Markdown parsing | [Marked](https://marked.js.org/) v11+ |
| Diagram rendering | [Mermaid](https://mermaid.js.org/) v11+ |
| Typography | Google Fonts API |
| Backend | PHP 7.0+ |

## Getting Started

### Prerequisites

- PHP 7.0 or higher
- A modern web browser (Chrome, Firefox, Safari, Edge)

### Option A — PHP built-in server

```bash
git clone https://github.com/yboumaiza/markdown-viewer.git
cd markdown-viewer
php -S localhost:8090
```

Then open `http://localhost:8090` in your browser.

### Option B — XAMPP

1. Place the project folder inside `C:\xampp\htdocs\`
2. Start Apache in the XAMPP control panel
3. Open `http://localhost/markdown-viewer`

## Usage

| Action | How |
|---|---|
| Load a file from disk | Enter the server-side file path in the input field and click **Load** |
| Upload a local file | Click **Upload File** and pick a `.md` file |
| Change font | Select from the dropdown or type a custom font name and press Enter |
| Export to PDF | Click **Print** and choose "Save as PDF" in the print dialog |

## Project Structure

```
markdown-viewer/
├── index.php    # Frontend — editor, preview iframe, all client-side logic
├── api.php      # Backend — serves .md files from the server with validation
└── sample.md    # Example file demonstrating supported markdown features
```
