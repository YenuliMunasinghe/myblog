<?php
// myblog/includes/markdown.php

/**
 * Lightweight, safe Markdown parser that escapes raw HTML for security
 * while converting common Markdown syntax into HTML formatting.
 * 
 * @param string $text
 * @return string Safe HTML string
 */
function parse_markdown($text) {
    if (empty($text)) {
        return '';
    }

    // 1. Normalize line endings
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    // 2. Escape raw HTML to prevent XSS before parsing markdown elements
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    // 3. Fenced Code Blocks (```code```)
    $text = preg_replace_callback('/```([a-zA-Z0-9_-]*)\n(.*?)```/s', function ($matches) {
        return '<pre><code class="language-' . htmlspecialchars($matches[1]) . '">' . $matches[2] . '</code></pre>';
    }, $text);

    // 4. Headers (# H1, ## H2, ### H3)
    $text = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $text);

    // 5. Blockquotes (> quote)
    $text = preg_replace('/^&gt; (.*?)$/m', '<blockquote>$1</blockquote>', $text);

    // 6. Horizontal Rules (--- or ***)
    $text = preg_replace('/^(---|[*]{3})$/m', '<hr>', $text);

    // 7. Inline Formatting: Bold, Italics, Code, Links
    // Bold: **text**
    $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
    // Italics: *text*
    $text = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $text);
    // Inline code: `code`
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    // Links: [label](url)
    $text = preg_replace_callback('/\[(.*?)\]\((https?:\/\/[^\s\)]+)\)/', function ($m) {
        return '<a href="' . $m[2] . '" target="_blank" rel="noopener noreferrer">' . $m[1] . '</a>';
    }, $text);

    // 8. Unordered Lists (- item or * item)
    $lines = explode("\n", $text);
    $in_list = false;
    $output = [];

    foreach ($lines as $line) {
        if (preg_match('/^[\-\*] (.*?)$/', $line, $matches)) {
            if (!$in_list) {
                $output[] = '<ul>';
                $in_list = true;
            }
            $output[] = '<li>' . $matches[1] . '</li>';
        } else {
            if ($in_list) {
                $output[] = '</ul>';
                $in_list = false;
            }
            $output[] = $line;
        }
    }
    if ($in_list) {
        $output[] = '</ul>';
    }

    $text = implode("\n", $output);

    // 9. Paragraph wrapping for plain lines (excluding HTML blocks)
    $blocks = explode("\n\n", $text);
    $formatted_blocks = [];

    foreach ($blocks as $block) {
        $trimmed = trim($block);
        if (empty($trimmed)) {
            continue;
        }
        if (preg_match('/^<(h[1-6]|ul|ol|blockquote|pre|hr)/i', $trimmed)) {
            $formatted_blocks[] = $trimmed;
        } else {
            $formatted_blocks[] = '<p>' . nl2br($trimmed) . '</p>';
        }
    }

    return implode("\n", $formatted_blocks);
}
