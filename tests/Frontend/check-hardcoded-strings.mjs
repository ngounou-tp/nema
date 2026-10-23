// Fails when a .vue file shows text that does not go through the translations,
// or uses a translation key missing from lang/fr.json or lang/en.json.
// Run: npm run lint:i18n
import { readFileSync, readdirSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';
import { parse } from 'vue/compiler-sfc';

const ROOT = new URL('../..', import.meta.url).pathname.replace(
    /^\/([A-Za-z]:)/,
    '$1',
);
const SOURCES = join(ROOT, 'resources/js');
const SKIPPED_DIRS = new Set(['actions', 'routes', 'wayfinder']);

/** Visible text attributes, on native elements and on components alike. */
const TEXT_ATTRIBUTES = new Set([
    'alt',
    'aria-label',
    'aria-description',
    'aria-placeholder',
    'aria-valuetext',
    'description',
    'label',
    'placeholder',
    'title',
    'text',
    'button-text',
]);

/** Script literals assigned to these keys end up on screen (menus, modal configs…). */
const SCRIPT_TEXT_KEYS =
    /\b(title|description|label|buttonText|placeholder|message)\s*:\s*(['"`])((?:(?!\2).)*\p{L}(?:(?!\2).)*)\2/gu;

/** "login.title", "nav.dashboard"… */
const TRANSLATION_KEY = /^[a-z0-9_]+(\.[a-z0-9_]+)+$/;

/** Units and codes written the same way in both languages. */
const ALLOWED = new Set(['FCFA', 'XAF', 'kg', 'ha', 't']);

/** Intl option values inside {{ formatDate(…, { dateStyle: 'full' }) }}. */
const INTL_OPTIONS = new Set([
    'full',
    'long',
    'medium',
    'short',
    'narrow',
    'numeric',
    '2-digit',
]);

/** Keys owned by lang/{fr,en}/*.php files (enum labels, framework messages). */
const PHP_GROUPS = [
    'enums.',
    'validation.',
    'auth.',
    'passwords.',
    'pagination.',
];

const NODE_ELEMENT = 1;
const NODE_TEXT = 2;
const NODE_INTERPOLATION = 5;
const PROP_ATTRIBUTE = 6;

const fr = JSON.parse(readFileSync(join(ROOT, 'lang/fr.json'), 'utf8'));
const en = JSON.parse(readFileSync(join(ROOT, 'lang/en.json'), 'utf8'));

const problems = [];

function vueFiles(directory) {
    return readdirSync(directory).flatMap((entry) => {
        const path = join(directory, entry);

        if (statSync(path).isDirectory()) {
            return SKIPPED_DIRS.has(entry) ? [] : vueFiles(path);
        }

        return entry.endsWith('.vue') ? [path] : [];
    });
}

function hasWords(text) {
    const trimmed = text.trim();

    return trimmed !== '' && /\p{L}/u.test(trimmed) && !ALLOWED.has(trimmed);
}

function report(file, line, message) {
    problems.push(`${relative(ROOT, file)}:${line}  ${message}`);
}

function walk(node, file) {
    if (node.type === NODE_TEXT && hasWords(node.content)) {
        report(file, node.loc.start.line, `text « ${node.content.trim()} »`);
    }

    // {{ isOpen ? 'Hide' : 'View' }}: string literals with words, outside $t(…) calls.
    if (node.type === NODE_INTERPOLATION) {
        const expression = node.content.content.replace(
            /\$t(Choice)?\([^)]*\)/g,
            '',
        );

        for (const literal of expression.matchAll(/(['"`])((?:(?!\1).)*)\1/g)) {
            if (
                hasWords(literal[2]) &&
                !TRANSLATION_KEY.test(literal[2]) &&
                !INTL_OPTIONS.has(literal[2])
            ) {
                report(
                    file,
                    node.loc.start.line,
                    `literal « ${literal[2]} » in {{ ${node.content.content.trim()} }}`,
                );
            }
        }
    }

    if (node.type === NODE_ELEMENT) {
        for (const prop of node.props) {
            if (
                prop.type === PROP_ATTRIBUTE &&
                TEXT_ATTRIBUTES.has(prop.name) &&
                prop.value &&
                hasWords(prop.value.content)
            ) {
                report(
                    file,
                    prop.loc.start.line,
                    `attribute ${prop.name}="${prop.value.content}"`,
                );
            }
        }
    }

    for (const child of node.children ?? []) {
        walk(child, file);
    }
}

function checkKey(key, file, line) {
    if (PHP_GROUPS.some((group) => key.startsWith(group))) {
        return;
    }

    if (!(key in fr)) {
        report(file, line, `key "${key}" missing from lang/fr.json`);
    }

    if (!(key in en)) {
        report(file, line, `key "${key}" missing from lang/en.json`);
    }
}

function checkKeys(source, file) {
    for (const match of source.matchAll(
        /(?:\$t|\btrans|\$tChoice|\btransChoice)\(\s*(['"])([\w.-]+)\1/g,
    )) {
        checkKey(
            match[2],
            file,
            source.slice(0, match.index).split('\n').length,
        );
    }
}

for (const file of vueFiles(SOURCES)) {
    const source = readFileSync(file, 'utf8');
    const { descriptor } = parse(source, { filename: file });

    if (descriptor.template?.ast) {
        walk(descriptor.template.ast, file);
    }

    for (const block of [descriptor.script, descriptor.scriptSetup]) {
        if (!block) {
            continue;
        }

        for (const match of block.content.matchAll(SCRIPT_TEXT_KEYS)) {
            const line =
                block.loc.start.line +
                block.content.slice(0, match.index).split('\n').length -
                1;

            // A translation key (layout titles in defineOptions are translated by the layout).
            if (TRANSLATION_KEY.test(match[3])) {
                checkKey(match[3], file, line);
                continue;
            }

            report(file, line, `script ${match[1]}: "${match[3]}"`);
        }
    }

    checkKeys(source, file);
}

for (const file of readdirSync(join(SOURCES, 'composables')).map((name) =>
    join(SOURCES, 'composables', name),
)) {
    checkKeys(readFileSync(file, 'utf8'), file);
}

if (problems.length > 0) {
    console.error(
        `${problems.length} untranslated string(s) or missing key(s):\n`,
    );
    console.error(problems.join('\n'));
    process.exit(1);
}

console.log('i18n: every visible string goes through the translations.');
