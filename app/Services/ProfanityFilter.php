<?php

namespace App\Services;

/**
 * ProfanityFilter
 *
 * Detects and masks offensive / harmful words in user-submitted text.
 * Covers French and English terms commonly found in art-gallery comments.
 *
 * Usage:
 *   $filter = new ProfanityFilter();
 *   if ($filter->contains($text)) { ... }
 *   $clean = $filter->mask($text);
 */
class ProfanityFilter
{
    /**
     * Offensive word list — French + English.
     * All entries are lowercase; detection is case-insensitive.
     */
    private array $words = [
        // ── English ──────────────────────────────────────────────────────────
        'fuck', 'fucking', 'fucker', 'fucked', 'fck',
        'shit', 'shitty', 'bullshit',
        'bitch', 'bitches', 'bastard',
        'asshole', 'ass', 'arse',
        'dick', 'cock', 'cunt',
        'whore', 'slut', 'hoe',
        'nigger', 'nigga',
        'faggot', 'fag',
        'retard', 'retarded',
        'idiot', 'moron', 'imbecile',
        'kill yourself', 'kys',
        'rape', 'rapist',
        'nazi', 'terrorist',
        'hate', 'die',
        // ── French ───────────────────────────────────────────────────────────
        'merde', 'putain', 'pute', 'salope',
        'connard', 'connasse', 'con',
        'enculé', 'encule', 'enculer',
        'bâtard', 'batard',
        'nique', 'niquer', 'niqué',
        'fdp', 'fils de pute',
        'ta gueule', 'ferme la',
        'crétin', 'cretin', 'idiot',
        'abruti', 'débile', 'debile',
        'raciste', 'racist',
        'nazi', 'terroriste',
        'suicid', // catches "suicide", "suicidez-vous"
        'tue toi', 'tue-toi',
        'va mourir', 'crève', 'creve',
        'mongol', 'gogol',
        'pédophile', 'pedophile',
        'violer', 'viol',
        'salopard', 'ordure',
        'déchet', 'dechet',
    ];

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Returns true if the text contains any offensive word.
     */
    public function contains(string $text): bool
    {
        $normalized = $this->normalize($text);

        foreach ($this->words as $word) {
            // Word-boundary–aware search: match whole word or phrase
            $pattern = '/(?<![a-z0-9])' . preg_quote($word, '/') . '(?![a-z0-9])/ui';
            if (preg_match($pattern, $normalized)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the first offensive word found (for error messages), or null.
     */
    public function firstMatch(string $text): ?string
    {
        $normalized = $this->normalize($text);

        foreach ($this->words as $word) {
            $pattern = '/(?<![a-z0-9])' . preg_quote($word, '/') . '(?![a-z0-9])/ui';
            if (preg_match($pattern, $normalized)) {
                return $word;
            }
        }

        return null;
    }

    /**
     * Replaces offensive words with asterisks (e.g. "merde" → "m***e").
     * Useful if you want to allow borderline comments but mask the word.
     */
    public function mask(string $text): string
    {
        foreach ($this->words as $word) {
            $pattern     = '/(?<![a-z0-9])(' . preg_quote($word, '/') . ')(?![a-z0-9])/ui';
            $replacement = $word[0] . str_repeat('*', max(1, mb_strlen($word) - 2)) . mb_substr($word, -1);
            $text        = preg_replace($pattern, $replacement, $text);
        }

        return $text;
    }

    // ── Private ───────────────────────────────────────────────────────────────

    /**
     * Normalise text: lowercase + replace common leet-speak substitutions
     * so "5hit", "b1tch", "f@ck" etc. are still caught.
     */
    private function normalize(string $text): string
    {
        $leet = [
            '@' => 'a', '4' => 'a',
            '3' => 'e', '€' => 'e',
            '1' => 'i', '!' => 'i',
            '0' => 'o',
            '5' => 's', '$' => 's',
            '7' => 't',
            '+' => 't',
        ];

        $text = mb_strtolower($text);
        $text = strtr($text, $leet);

        // Remove zero-width / invisible Unicode characters
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);

        return $text;
    }
}
