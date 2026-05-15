<?php

namespace Hostinger\AiTheme;

defined( 'ABSPATH' ) || exit;

class Assets {
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'japanese_assets' ) );
        add_action( 'enqueue_block_assets', array( $this, 'japanese_assets' ) );
    }

    public function japanese_assets(): void {
        // Enqueue Japanese font if needed
        if ( $this->is_japanese_locale() ) {
            wp_enqueue_style(
                'noto-sans',
                'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100;300;400;500;700;900&display=swap',
                [],
                wp_get_theme()->get( 'Version' )
            );

            $japanese_font_css = "
                body,
                .wp-theme-hostinger-ai-theme, 
                .editor-styles-wrapper, 
                .block-editor-block-list__layout,
                .wp-block-paragraph,
                .wp-block-heading {
                    font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont,
                        'Hiragino Sans', 'Hiragino Kaku Gothic ProN', 'Segoe UI',
                        'Yu Gothic UI', Meiryo, sans-serif;
                    font-weight: 400;
                    -webkit-font-smoothing: antialiased;
                    -moz-osx-font-smoothing: grayscale;
                    word-break: keep-all;
                },
                .entry-content p,
			    .entry-content h1,
			    .entry-content h2,
			    .entry-content h3,
			    .entry-content h4,
			    .entry-content h5,
			    .entry-content h6,
			    nav,
			    footer {
			        word-spacing: -0.25em;
			    }
            ";

            wp_add_inline_style( 'noto-sans', wp_strip_all_tags( $japanese_font_css ) );

            wp_register_script(
                'hostinger-ai-japanese-segmenter',
                '',
                [],
                wp_get_theme()->get( 'Version' ),
            );

            $word_segmentation_script = "
                document.addEventListener('DOMContentLoaded', function () {
                    if (window.Intl && Intl.Segmenter) {
                        const segmenter = new Intl.Segmenter('ja-JP', { granularity: 'word' });
                        const elements = document.querySelectorAll('h1, h2, h3, h4, h5, h6, p, .wp-block-paragraph, .wp-block-heading, .wp-block-quote, .wp-block-pullquote, .wp-block-verse, .wp-block-preformatted, .wp-block-table, .wp-block-button__link, .wp-block-media-text__content, .wp-block-cover__inner-container, .wp-block-columns, .wp-block-column, .wp-block-group__inner-container, .wp-block-navigation-item__content, .wp-block-latest-posts__post-title, .wp-block-latest-posts__post-excerpt, .wp-block-latest-comments__comment-excerpt, .wp-block-gallery figcaption, .blocks-gallery-caption, .wp-block-image figcaption, .wp-block-embed figcaption, .wp-block-search__label, .wp-block-search__input, .wp-block-table caption, .wp-block-latest-posts__post-author, .wp-block-latest-posts__post-date, .wp-block-latest-comments__comment-author, .wp-block-latest-comments__comment-date');
                        
                        function segmentTextNodes(node) {
                            node.childNodes.forEach((child) => {
                                if (child.nodeType === Node.TEXT_NODE && child.nodeValue.trim()) {
                                    const segments = segmenter.segment(child.nodeValue);
                                    child.nodeValue = Array.from(segments).map(segment => segment.segment).join(' ');
                                } else if (child.nodeType === Node.ELEMENT_NODE) {
                                    segmentTextNodes(child); // Recursively process child elements
                                }
                            });
                        }
                        
                        elements.forEach(segmentTextNodes);
                    } else {
                        console.warn('Intl.Segmenter is not supported in this browser.');
                    }
                });
            ";

            // Add the script code and enqueue it
            wp_add_inline_script('hostinger-ai-japanese-segmenter', wp_strip_all_tags($word_segmentation_script));
            wp_enqueue_script('hostinger-ai-japanese-segmenter');
        }
    }

    /**
     * Check if current locale is Japanese
     * @return bool
     */
    private function is_japanese_locale(): bool {
        $japanese_locales = array( 'ja', 'ja_JP' );

        return in_array( get_locale(), $japanese_locales, true );
    }

    /**
     * Enqueue frontend styles
     * @return void
     */
    public function frontend_styles(): void {
        wp_enqueue_style(
            'hostinger-ai-style',
            get_stylesheet_directory_uri() . '/assets/css/style.min.css',
            [],
            wp_get_theme()->get( 'Version' ),
        );

        if( !is_admin() ) {
            wp_add_inline_style(
                'hostinger-ai-style',
                '.hostinger-ai-fade-up { opacity: 0; }'
            );

            $dark_theme_css = <<<'CSS'
body:not(.wp-admin) {
    background:
        radial-gradient(circle at top, rgba(34, 211, 238, 0.14), transparent 34%),
        linear-gradient(180deg, #0b1220 0%, #09111d 100%);
    color: #f8fafc;
}

body:not(.wp-admin),
body:not(.wp-admin) .wp-site-blocks,
body:not(.wp-admin) .wp-site-blocks > * {
    background-color: transparent;
}

body:not(.wp-admin) .wp-site-blocks,
body:not(.wp-admin) .site {
    color: #f8fafc;
}

body:not(.wp-admin) a {
    color: #d9f6ff;
}

body:not(.wp-admin) a:hover,
body:not(.wp-admin) a:focus-visible {
    color: #22d3ee;
}

body:not(.wp-admin) .hostinger-ai-menu,
body:not(.wp-admin) .hostinger-ai-menu-wrapper,
body:not(.wp-admin) .site-header,
body:not(.wp-admin) .site-footer {
    background-color: #0b1220;
}

body:not(.wp-admin) .wp-block-group,
body:not(.wp-admin) .wp-block-columns,
body:not(.wp-admin) .wp-block-column,
body:not(.wp-admin) .wp-block-post,
body:not(.wp-admin) .wp-block-query,
body:not(.wp-admin) .wp-block-media-text__content {
    color: inherit;
}

body:not(.wp-admin) .hostinger-ai-project-card {
    position: relative;
    display: grid;
    gap: 0.9rem;
    padding-bottom: 1rem;
    border-radius: 24px;
}

body:not(.wp-admin) .hostinger-ai-project-card > .wp-block-group:first-child,
body:not(.wp-admin) .hostinger-ai-project-card > .wp-block-image:first-child,
body:not(.wp-admin) .hostinger-ai-project-card > figure:first-child {
    position: relative;
}

body:not(.wp-admin) .hostinger-ai-project-toggle {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 2;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 999px;
    border: 1px solid rgba(248, 250, 252, 0.18);
    background: rgba(15, 23, 42, 0.72);
    color: #f8fafc;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(14px);
    box-shadow: 0 12px 30px rgba(2, 6, 23, 0.35);
    transition: transform 0.28s ease, background-color 0.28s ease, border-color 0.28s ease, box-shadow 0.28s ease;
}

body:not(.wp-admin) .hostinger-ai-project-toggle:hover,
body:not(.wp-admin) .hostinger-ai-project-toggle:focus-visible {
    transform: translateY(-1px) scale(1.03);
    border-color: rgba(34, 211, 238, 0.7);
    background: rgba(15, 23, 42, 0.92);
}

body:not(.wp-admin) .hostinger-ai-project-toggle svg {
    width: 1rem;
    height: 1rem;
    transition: transform 0.28s ease;
}

body:not(.wp-admin) .hostinger-ai-project-card.is-open .hostinger-ai-project-toggle svg {
    transform: rotate(45deg);
}

body:not(.wp-admin) .hostinger-ai-project-details {
    display: grid;
    grid-template-rows: 0fr;
    opacity: 0;
    transform: translateY(-0.5rem);
    transition: grid-template-rows 0.35s ease, opacity 0.35s ease, transform 0.35s ease;
}

body:not(.wp-admin) .hostinger-ai-project-card.is-open .hostinger-ai-project-details {
    grid-template-rows: 1fr;
    opacity: 1;
    transform: translateY(0);
}

body:not(.wp-admin) .hostinger-ai-project-details-inner {
    min-height: 0;
    overflow: hidden;
}

body:not(.wp-admin) .hostinger-ai-project-title,
body:not(.wp-admin) .hostinger-ai-project-description {
    color: #f8fafc;
}

body:not(.wp-admin) .hostinger-ai-project-description {
    margin-top: 0;
}

@media (prefers-reduced-motion: reduce) {
    body:not(.wp-admin) .hostinger-ai-project-toggle,
    body:not(.wp-admin) .hostinger-ai-project-toggle svg,
    body:not(.wp-admin) .hostinger-ai-project-details {
        transition: none;
    }
}
CSS;

            wp_add_inline_style( 'hostinger-ai-style', $dark_theme_css );

            $landing_home_css = <<<'CSS'
body.home {
    background:
        radial-gradient(circle at top left, rgba(34, 211, 238, 0.22), transparent 28%),
        radial-gradient(circle at 85% 15%, rgba(139, 92, 246, 0.20), transparent 24%),
        radial-gradient(circle at 20% 80%, rgba(52, 211, 153, 0.14), transparent 26%),
        linear-gradient(180deg, #09111f 0%, #040814 100%);
    position: relative;
    overflow-x: hidden;
}

body.home::before {
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
    background-size: 44px 44px;
    content: "";
    inset: 0;
    opacity: 0.22;
    pointer-events: none;
    position: fixed;
    mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.85), transparent 88%);
    z-index: 0;
}

body.home .wp-site-blocks {
    position: relative;
    z-index: 1;
}

body.home .landing-home {
    padding: 24px 0 40px;
}

body.home .landing-shell {
    margin: 0 auto;
    width: min(1120px, calc(100% - 32px));
}

body.home .landing-topbar {
    align-items: center;
    backdrop-filter: blur(16px);
    background: rgba(5, 10, 18, 0.55);
    border: 1px solid rgba(167, 180, 199, 0.18);
    border-radius: 999px;
    box-shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
    display: flex;
    gap: 16px;
    justify-content: space-between;
    padding: 14px 18px;
    position: sticky;
    top: 16px;
    z-index: 10;
}

body.home .landing-brand {
    align-items: center;
    color: #f4f7fb;
    display: inline-flex;
    gap: 12px;
    text-decoration: none;
}

body.home .landing-brand-mark {
    background: linear-gradient(135deg, #22d3ee, #8b5cf6);
    border-radius: 11px;
    box-shadow: 0 10px 24px rgba(34, 211, 238, 0.22);
    flex: 0 0 34px;
    height: 34px;
    width: 34px;
}

body.home .landing-brand-title a {
    color: inherit;
    text-decoration: none;
}

body.home .landing-brand-title {
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0;
    margin: 0;
    text-transform: uppercase;
}

body.home .landing-badge {
    align-items: center;
    background: rgba(34, 211, 238, 0.12);
    border: 1px solid rgba(34, 211, 238, 0.22);
    border-radius: 999px;
    color: #d9fbff;
    display: inline-flex;
    font-size: 0.92rem;
    padding: 10px 14px;
    white-space: nowrap;
}

body.home .landing-hero {
    align-items: stretch;
    display: grid;
    gap: 24px;
    grid-template-columns: 1.15fr 0.85fr;
    padding: 72px 0 28px;
}

body.home .landing-panel,
body.home .landing-section {
    backdrop-filter: blur(18px);
    background: rgba(10, 19, 34, 0.78);
    border: 1px solid rgba(167, 180, 199, 0.18);
    box-shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
}

body.home .landing-hero-copy {
    border-radius: 32px;
    overflow: hidden;
    padding: 42px;
    position: relative;
}

body.home .landing-hero-copy::after {
    background: radial-gradient(circle, rgba(34, 211, 238, 0.18), transparent 68%);
    border-radius: 50%;
    bottom: -35%;
    content: "";
    height: 320px;
    pointer-events: none;
    position: absolute;
    right: -12%;
    width: 320px;
}

body.home .landing-eyebrow {
    align-items: center;
    background: rgba(139, 92, 246, 0.14);
    border: 1px solid rgba(139, 92, 246, 0.24);
    border-radius: 999px;
    color: #efe7ff;
    display: inline-flex;
    font-size: 0.9rem;
    gap: 10px;
    letter-spacing: 0;
    padding: 9px 14px;
}

body.home .landing-hero h1 {
    font-family: "DM Serif Display", serif;
    font-size: 5.7rem;
    letter-spacing: 0;
    line-height: 0.95;
    margin: 18px 0 16px;
    max-width: 11ch;
}

body.home .landing-lede {
    color: #a7b4c7;
    font-size: 1.1rem;
    line-height: 1.8;
    margin: 0;
    max-width: 58ch;
}

body.home .landing-actions,
body.home .landing-contact-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

body.home .landing-actions {
    margin-top: 28px;
}

body.home .landing-button .wp-block-button__link {
    align-items: center;
    border-radius: 999px;
    display: inline-flex;
    font-weight: 700;
    justify-content: center;
    min-height: 48px;
    padding: 0 18px;
    text-decoration: none;
    transition: transform 160ms ease, border-color 160ms ease, background 160ms ease;
}

body.home .landing-button .wp-block-button__link:hover,
body.home .landing-button .wp-block-button__link:focus-visible {
    transform: translateY(-1px);
}

body.home .landing-button--primary .wp-block-button__link {
    background: linear-gradient(135deg, #22d3ee, #8ee8f3);
    color: #04111d;
}

body.home .landing-button--secondary .wp-block-button__link {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(167, 180, 199, 0.18);
    color: #f4f7fb;
}

body.home .landing-hero-side {
    display: grid;
    gap: 16px;
}

body.home .landing-card {
    border-radius: 24px;
    padding: 22px;
}

body.home .landing-card h2,
body.home .landing-section h2 {
    font-size: 1.15rem;
    letter-spacing: 0;
    margin: 0 0 10px;
}

body.home .landing-card p,
body.home .landing-section p {
    color: #a7b4c7;
    line-height: 1.7;
    margin: 0;
}

body.home .landing-metrics {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(3, 1fr);
}

body.home .landing-metric {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 18px;
    padding: 18px;
}

body.home .landing-metric strong {
    display: block;
    font-size: 1.1rem;
    margin-bottom: 8px;
}

body.home .landing-metric span {
    color: #a7b4c7;
    font-size: 0.93rem;
    line-height: 1.5;
}

body.home .landing-sections {
    display: grid;
    gap: 16px;
    padding-bottom: 40px;
}

body.home .landing-section {
    border-radius: 28px;
    padding: 28px;
}

body.home .landing-grid {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(3, 1fr);
    margin-top: 18px;
}

body.home .landing-tile {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 20px;
    padding: 18px;
}

body.home .landing-tile h3 {
    font-size: 1rem;
    margin: 0 0 8px;
}

body.home .landing-tile p {
    color: #a7b4c7;
    line-height: 1.6;
    margin: 0;
}

body.home .landing-project-grid,
body.home .landing-gamejam-grid {
    display: grid;
    gap: 14px;
    margin-top: 18px;
}

body.home .landing-project-grid {
    grid-template-columns: repeat(2, 1fr);
}

body.home .landing-gamejam-grid {
    grid-template-columns: repeat(3, 1fr);
}

body.home .landing-project-card,
body.home .landing-gamejam-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 20px;
    padding: 18px;
}

body.home .landing-project-card h3,
body.home .landing-gamejam-panel h3 {
    color: #f4f7fb;
    font-size: 1rem;
    margin: 0 0 8px;
}

body.home .landing-project-list {
    color: #a7b4c7;
    line-height: 1.7;
    margin: 0;
    padding-left: 1.1rem;
}

body.home .landing-project-list li + li {
    margin-top: 0.35rem;
}

body.home .landing-tag-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 18px;
}

body.home .landing-tag {
    background: rgba(52, 211, 153, 0.12);
    border: 1px solid rgba(52, 211, 153, 0.2);
    border-radius: 999px;
    color: #c8f7e4;
    font-size: 0.88rem;
    padding: 7px 10px;
}

body.home .landing-gamejam-section {
    opacity: 0.9;
}

body.home .landing-gamejam-card {
    align-items: start;
    display: grid;
    gap: 12px;
}

body.home .landing-icon-button {
    align-items: center;
    background: rgba(15, 23, 42, 0.72);
    border: 1px solid rgba(248, 250, 252, 0.18);
    border-radius: 999px;
    color: #f4f7fb;
    cursor: pointer;
    display: inline-flex;
    font-size: 1.2rem;
    height: 44px;
    justify-content: center;
    width: 44px;
}

body.home .landing-icon-button:hover,
body.home .landing-icon-button:focus-visible {
    border-color: rgba(34, 211, 238, 0.7);
}

body.home .landing-gamejam-panel[hidden] {
    display: none;
}

body.home .landing-gamejam-panel p {
    color: #a7b4c7;
    line-height: 1.6;
    margin: 0;
}

body.home .landing-note {
    color: #9bb3c9;
    font-size: 0.94rem;
    margin-top: 16px;
}

body.home .landing-footer {
    color: #8ea2b6;
    font-size: 0.9rem;
    padding: 8px 0 22px;
    text-align: center;
}

@media (max-width: 880px) {
    body.home .landing-hero {
        grid-template-columns: 1fr;
        padding-top: 34px;
    }

    body.home .landing-hero-copy {
        padding: 30px 22px;
    }

    body.home .landing-hero h1 {
        font-size: 3.5rem;
    }

    body.home .landing-metrics,
    body.home .landing-grid,
    body.home .landing-project-grid,
    body.home .landing-gamejam-grid {
        grid-template-columns: 1fr;
    }

    body.home .landing-topbar {
        align-items: flex-start;
        border-radius: 26px;
        flex-direction: column;
    }
}
CSS;

            wp_add_inline_style( 'hostinger-ai-style', $landing_home_css );
        }

        $this->output_font_css();
    }

    private function output_font_css(): void {
        $heading_font = get_option( 'hostinger_ai_font', false );
        $body_font    = get_option( 'hostinger_ai_body_font', false );

        if ( ! $heading_font || ! $body_font ) {
            return;
        }

        $css = sprintf(
            '.hostinger-ai-font-title { font-family: %s; } body.elementor-page { font-family: %s; }',
            sanitize_text_field( $heading_font ),
            sanitize_text_field( $body_font )
        );

        wp_add_inline_style( 'hostinger-ai-style', $css );
    }

    /**
     * @return void
     */
    public function frontend_scripts(): void {
        wp_enqueue_script(
            'hostinger-ai-scripts',
            get_stylesheet_directory_uri() . '/assets/js/front-scripts.min.js',
            [
                'jquery',
                'wp-i18n',
            ],
            wp_get_theme()->get( 'Version' ),
            true,
        );

        $project_toggle_script = <<<'JS'
document.addEventListener('DOMContentLoaded', function () {
    const projectTitles = document.querySelectorAll('.hostinger-ai-project-title');
    const processedCards = new WeakSet();

    const toggleIcon = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M11 5h2v14h-2zM5 11h14v2H5z"></path></svg>';

    function findProjectCard(titleNode) {
        return titleNode.closest('.project-item') || titleNode.closest('.wp-block-group');
    }

    function getDirectProjectImage(card) {
        return Array.from(card.children).find(function (child) {
            return child.querySelector('.hostinger-ai-project-image') || child.classList.contains('hostinger-ai-project-image') || child.matches('figure, .wp-block-image');
        }) || null;
    }

    function buildProjectCard(card) {
        if (!card || processedCards.has(card)) {
            return;
        }

        const title = card.querySelector('.hostinger-ai-project-title');
        const description = card.querySelector('.hostinger-ai-project-description');
        const imageWrapper = getDirectProjectImage(card);

        if (!title || !description || !imageWrapper) {
            return;
        }

        const directChildren = Array.from(card.children);
        const imageIndex = directChildren.indexOf(imageWrapper);

        if (imageIndex === -1) {
            return;
        }

        const detailsWrapper = document.createElement('div');
        detailsWrapper.className = 'hostinger-ai-project-details';

        const detailsInner = document.createElement('div');
        detailsInner.className = 'hostinger-ai-project-details-inner';

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'hostinger-ai-project-toggle';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Show project details');
        toggle.innerHTML = toggleIcon;

        directChildren.slice(imageIndex + 1).forEach(function (child) {
            detailsInner.appendChild(child);
        });

        detailsWrapper.appendChild(detailsInner);
        imageWrapper.insertAdjacentElement('afterend', toggle);
        toggle.insertAdjacentElement('afterend', detailsWrapper);

        card.classList.add('hostinger-ai-project-card');
        processedCards.add(card);

        toggle.addEventListener('click', function () {
            const isOpen = card.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            toggle.setAttribute('aria-label', isOpen ? 'Hide project details' : 'Show project details');
        });
    }

    projectTitles.forEach(function (titleNode) {
        buildProjectCard(findProjectCard(titleNode));
    });

    document.querySelectorAll('.landing-gamejam-card').forEach(function (card) {
        const button = card.querySelector('.landing-icon-button');
        const panel = card.querySelector('.landing-gamejam-panel');

        if (!button || !panel) {
            return;
        }

        button.addEventListener('click', function () {
            const isOpen = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            button.textContent = isOpen ? '+' : 'x';
            panel.hidden = isOpen;
        });
    });
});
JS;

        wp_add_inline_script( 'hostinger-ai-scripts', $project_toggle_script );
    }
}
