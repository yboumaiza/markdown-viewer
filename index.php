<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markdown Viewer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter&family=Roboto&family=Open+Sans&family=Lato&family=Merriweather&family=Source+Code+Pro&family=Fira+Code&family=Nunito&family=PT+Serif&family=IBM+Plex+Sans&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/marked/lib/marked.umd.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Inter', system-ui, sans-serif;
            background: #1e1e2e;
            color: #cdd6f4;
        }

        #toolbar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #181825;
            border-bottom: 1px solid #313244;
            flex-wrap: wrap;
        }

        #toolbar input[type="text"],
        #toolbar select {
            padding: 6px 10px;
            border: 1px solid #45475a;
            border-radius: 6px;
            background: #1e1e2e;
            color: #cdd6f4;
            font-size: 13px;
            outline: none;
        }

        #toolbar input[type="text"]:focus,
        #toolbar select:focus {
            border-color: #89b4fa;
        }

        #filePath { flex: 1; min-width: 200px; }
        #customFont { width: 160px; }

        #toolbar button, .upload-label {
            padding: 6px 14px;
            border: 1px solid #45475a;
            border-radius: 6px;
            background: #313244;
            color: #cdd6f4;
            font-size: 13px;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.15s;
        }

        #toolbar button:hover, .upload-label:hover {
            background: #45475a;
        }

        #toolbar button:disabled {
            opacity: 0.4;
            cursor: default;
        }

        .upload-label {
            display: inline-block;
        }

        .separator {
            color: #6c7086;
            font-size: 12px;
        }

        #status {
            font-size: 12px;
            margin-left: auto;
            white-space: nowrap;
        }

        #main {
            display: flex;
            flex: 1;
            min-height: 0;
        }

        #editor {
            width: 50%;
            padding: 0;
            border: none;
            border-right: 1px solid #313244;
            background: #1e1e2e;
            color: #cdd6f4;
            font-family: 'Source Code Pro', 'Fira Code', monospace;
            font-size: 14px;
            line-height: 1.6;
            resize: none;
            outline: none;
            padding: 20px;
            tab-size: 4;
        }

        #preview {
            width: 50%;
            border: none;
            background: #fff;
        }
    </style>
</head>
<body>
    <div id="toolbar">
        <input type="text" id="filePath" placeholder="Enter path to .md file (e.g., C:\docs\readme.md)">
        <button id="loadBtn">Load</button>
        <span class="separator">or</span>
        <label class="upload-label">
            Upload
            <input type="file" id="fileUpload" accept=".md,.markdown,.txt" hidden>
        </label>
        <span class="separator">|</span>
        <select id="fontSelect">
            <option value="Inter">Inter</option>
            <option value="Roboto">Roboto</option>
            <option value="Open Sans">Open Sans</option>
            <option value="Lato">Lato</option>
            <option value="Merriweather">Merriweather</option>
            <option value="Source Code Pro">Source Code Pro</option>
            <option value="Fira Code">Fira Code</option>
            <option value="Nunito">Nunito</option>
            <option value="PT Serif">PT Serif</option>
            <option value="IBM Plex Sans">IBM Plex Sans</option>
        </select>
        <input type="text" id="customFont" placeholder="Custom font...">
        <button id="printBtn" disabled>Print</button>
        <span id="status"></span>
    </div>
    <div id="main">
        <textarea id="editor" spellcheck="false" placeholder="Type or load markdown here..."></textarea>
        <iframe id="preview" sandbox="allow-scripts allow-same-origin allow-modals"></iframe>
    </div>

    <script>
        const filePath = document.getElementById('filePath');
        const loadBtn = document.getElementById('loadBtn');
        const fileUpload = document.getElementById('fileUpload');
        const fontSelect = document.getElementById('fontSelect');
        const customFont = document.getElementById('customFont');
        const printBtn = document.getElementById('printBtn');
        const editor = document.getElementById('editor');
        const preview = document.getElementById('preview');
        const status = document.getElementById('status');

        let currentFont = 'Inter';

        marked.setOptions({ gfm: true, breaks: true });

        // --- File loading ---

        async function loadFromPath() {
            const path = filePath.value.trim();
            if (!path) {
                showStatus('Please enter a file path', true);
                return;
            }
            showStatus('Loading...', false);
            try {
                const res = await fetch('api.php?path=' + encodeURIComponent(path));
                const data = await res.json();
                if (!data.success) {
                    showStatus(data.error, true);
                    return;
                }
                editor.value = data.content;
                showStatus('Loaded: ' + data.filename, false);
                renderPreview();
                printBtn.disabled = false;
            } catch (e) {
                showStatus('Failed to connect to server', true);
            }
        }

        function loadFromUpload(file) {
            const reader = new FileReader();
            reader.onload = () => {
                editor.value = reader.result;
                showStatus('Loaded: ' + file.name, false);
                renderPreview();
                printBtn.disabled = false;
            };
            reader.onerror = () => showStatus('Failed to read file', true);
            reader.readAsText(file);
        }

        // --- Rendering ---

        function renderPreview() {
            const markdown = editor.value;
            let html = marked.parse(markdown);

            // Transform mermaid code blocks
            html = html.replace(
                /<pre><code class="language-mermaid">([\s\S]*?)<\/code><\/pre>/g,
                (match, content) => {
                    const decoded = content
                        .replace(/&amp;/g, '&')
                        .replace(/&lt;/g, '<')
                        .replace(/&gt;/g, '>')
                        .replace(/&quot;/g, '"')
                        .replace(/&#39;/g, "'");
                    return '<pre class="mermaid">' + decoded + '</pre>';
                }
            );

            const hasMermaid = html.includes('class="mermaid"');
            const fontFamily = currentFont;
            const fontUrl = 'https://fonts.googleapis.com/css2?family=' +
                encodeURIComponent(fontFamily.replace(/ /g, '+')) + '&display=swap';

            const iframeHTML = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="${fontUrl}">
    <style>
        body {
            font-family: '${fontFamily}', sans-serif;
            line-height: 1.7;
            color: #1a1a2e;
            max-width: 860px;
            margin: 0 auto;
            padding: 24px 32px;
            word-wrap: break-word;
        }
        h1, h2, h3, h4, h5, h6 { margin-top: 1.4em; margin-bottom: 0.5em; color: #0f0f1a; }
        h1 { font-size: 2em; border-bottom: 2px solid #e8e8ef; padding-bottom: 0.3em; }
        h2 { font-size: 1.5em; border-bottom: 1px solid #e8e8ef; padding-bottom: 0.25em; }
        h3 { font-size: 1.25em; }
        p { margin: 0.8em 0; }
        pre {
            background: #f4f4f8;
            border: 1px solid #e0e0e8;
            border-radius: 8px;
            padding: 16px;
            overflow-x: auto;
            font-size: 0.88em;
            line-height: 1.5;
        }
        code {
            background: #f0f0f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Source Code Pro', 'Fira Code', 'Consolas', monospace;
            font-size: 0.88em;
        }
        pre code { background: none; padding: 0; font-size: inherit; }
        table { border-collapse: collapse; width: 100%; margin: 1em 0; }
        th, td { border: 1px solid #ddd; padding: 10px 14px; text-align: left; }
        th { background: #f4f4f8; font-weight: 600; }
        tr:nth-child(even) { background: #fafafc; }
        blockquote {
            border-left: 4px solid #89b4fa;
            margin: 1em 0;
            padding: 0.5em 1em;
            color: #555;
            background: #f8f9fc;
            border-radius: 0 6px 6px 0;
        }
        img { max-width: 100%; height: auto; border-radius: 6px; }
        a { color: #2563eb; }
        ul, ol { padding-left: 2em; }
        li { margin: 0.3em 0; }
        hr { border: none; border-top: 2px solid #e8e8ef; margin: 2em 0; }
        pre.mermaid {
            background: none;
            border: none;
            text-align: center;
            padding: 16px 0;
        }
        @media print {
            body { max-width: none; padding: 0; margin: 0; color: #000; }
            pre { white-space: pre-wrap; word-wrap: break-word; }
            a { color: #000; text-decoration: underline; }
            pre.mermaid svg { max-width: 100%; }
        }
    </style>
</head>
<body>
    ${html}
    ${hasMermaid ? `
    <script type="module">
        import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.esm.min.mjs';
        mermaid.initialize({ startOnLoad: false, theme: 'default', securityLevel: 'loose' });
        await mermaid.run({ nodes: document.querySelectorAll('.mermaid') });
    <\/script>` : ''}
</body>
</html>`;

            preview.srcdoc = iframeHTML;
        }

        // --- Font management ---

        function updateFont() {
            const custom = customFont.value.trim();
            currentFont = custom || fontSelect.value;
            if (editor.value) renderPreview();
        }

        // --- Print ---

        function printPreview() {
            const iframeWindow = preview.contentWindow;
            if (iframeWindow) {
                iframeWindow.focus();
                iframeWindow.print();
            }
        }

        // --- Utilities ---

        function debounce(fn, delay) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => fn(...args), delay);
            };
        }

        function showStatus(message, isError) {
            status.textContent = message;
            status.style.color = isError ? '#f38ba8' : '#a6e3a1';
        }

        // --- Event listeners ---

        loadBtn.addEventListener('click', loadFromPath);
        filePath.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') loadFromPath();
        });

        fileUpload.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                loadFromUpload(e.target.files[0]);
                e.target.value = '';
            }
        });

        fontSelect.addEventListener('change', () => {
            customFont.value = '';
            updateFont();
        });

        customFont.addEventListener('input', debounce(() => {
            updateFont();
        }, 500));

        editor.addEventListener('input', debounce(() => {
            renderPreview();
            if (!printBtn.disabled) return;
            if (editor.value.trim()) printBtn.disabled = false;
        }, 300));

        printBtn.addEventListener('click', printPreview);

        // Enable tab key in editor
        editor.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = editor.selectionStart;
                const end = editor.selectionEnd;
                editor.value = editor.value.substring(0, start) + '    ' + editor.value.substring(end);
                editor.selectionStart = editor.selectionEnd = start + 4;
                editor.dispatchEvent(new Event('input'));
            }
        });
    </script>
</body>
</html>
