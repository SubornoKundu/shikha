<?php

namespace App\Services;

/**
 * The text of the About / Contact / Returns / Privacy / Terms pages.
 *
 * defaults() is the built-in wording (it follows the shop's own settings). The admin can replace any page
 * in Admin > Page content with plain text: a line starting "## " begins a section, "- " lines are bullet
 * points and everything else is a paragraph; the text before the first "## " is the introduction.
 */
class InfoPages
{
    /** slug => page title */
    public const PAGES = [
        'about' => 'About us',
        'contact' => 'Contact us',
        'returns' => 'Returns & refunds',
        'privacy' => 'Privacy policy',
        'terms' => 'Terms & conditions',
    ];

    /** @return array<string, array{intro: string, sections: list<array<string, mixed>>}> */
    public static function defaults(): array
    {
        $shop = (string) config('site.name', config('app.name', 'Rongshikha'));
        $sym = (string) config('shop.currency_symbol', '৳');
        $adv = ((int) config('shop.advance_percent')) ?: 20;
        $fee = $sym.number_format((float) config('shop.shipping_fee'));
        $free = (float) config('shop.free_shipping_over');
        $freeText = $free > 0 ? ' Orders of '.$sym.number_format($free).' or more ship free.' : '';
        $email = (string) config('shop.contact.email');

        $pages = [
            'about' => [
                'intro' => "{$shop} makes handmade paintings and textured wall art: landscapes, gold-detail florals and round canvases, delivered across Bangladesh.",
                'sections' => [
                    ['heading' => 'What we do', 'paragraphs' => [
                        "Every painting here is made by hand. Each product page shows the real price for every size, so you can pick the one that fits your wall.",
                    ]],
                    ['heading' => 'Why shop with us', 'items' => [
                        'Handmade landscapes, textured art, combo sets and round canvases',
                        "Pay {$adv}% in advance by bKash or Nagad, the rest in cash on delivery",
                        'Delivery across Bangladesh, with the time confirmed when we confirm your order',
                        'Tell us within 7 days if a painting arrives damaged',
                        'Friendly help on WhatsApp, email and social media',
                    ]],
                ],
            ],
            'contact' => [
                'intro' => 'Questions about an order, a size or a custom idea? Reach us any of these ways and we will reply as soon as we can.',
                'sections' => [
                    ['heading' => 'Before you write', 'paragraphs' => [
                        'If your question is about an order, keep your order number (it looks like ORD-20261003-AB12CD) handy, or use the Track order page to see where it is right now.',
                    ]],
                ],
            ],
            'returns' => [
                'intro' => 'Not happy with what arrived? Here is how replacements and refunds work.',
                'sections' => [
                    ['heading' => 'Damaged on arrival', 'paragraphs' => [
                        'If a painting arrives damaged or different from what you ordered, tell us within 7 days of delivery with a photo. Paintings are handmade, so we cannot take back one that was ordered correctly and simply changed your mind.',
                    ]],
                    ['heading' => 'How to ask', 'items' => [
                        'Message us on WhatsApp or email with your order number and a photo of the problem',
                        'Keep the painting and its packaging as they arrived until we reply',
                        'We agree the fix with you (repair, replacement or refund) as soon as the painting is checked',
                    ]],
                    ['heading' => 'Refunds', 'paragraphs' => [
                        'If a replacement is not possible we refund you. Cancelled orders are refunded to the bKash or Nagad number you paid from.',
                        'You can cancel an order yourself from My orders until it starts processing.',
                    ]],
                    ['heading' => 'Delivery charge', 'paragraphs' => [
                        "Delivery costs {$fee}.{$freeText} The delivery charge is not refunded for a change-of-mind return.",
                    ]],
                ],
            ],
            'privacy' => [
                'intro' => "How {$shop} looks after your information.",
                'sections' => [
                    ['heading' => 'What we collect', 'items' => [
                        'Your name, email and phone number when you register',
                        'Delivery addresses you save or enter at checkout',
                        'Your orders, wishlist and the reviews or questions you post',
                    ]],
                    ['heading' => 'How we use it', 'paragraphs' => [
                        'We use your details only to run your account, deliver and support your orders and send order updates by email. We do not sell your information.',
                    ]],
                    ['heading' => 'Payments', 'paragraphs' => [
                        'We never store your card number. We do not take card payments. Your bKash or Nagad transaction id is used only to match your payment.',
                    ]],
                    ['heading' => 'Your choices', 'paragraphs' => [
                        'You can change your details and delete saved addresses from your account at any time. To close your account or remove your data, contact us'.($email ? " at {$email}" : '').'.',
                    ]],
                ],
            ],
            'terms' => [
                'intro' => "Please read these terms before ordering from {$shop}.",
                'sections' => [
                    ['heading' => 'Orders', 'paragraphs' => [
                        'An order is accepted when you receive the confirmation. If an item turns out to be out of stock we will tell you and cancel or adjust the order.',
                    ]],
                    ['heading' => 'Prices & payment', 'paragraphs' => [
                        "All prices are in Bangladeshi Taka ({$sym}). Prices and stock can change without notice, but the price you see at checkout is the price you pay. You pay {$adv}% of the total in advance by bKash or Nagad (Send Money to our Personal number) and enter the transaction id at checkout. The rest is paid in cash to the delivery man. We confirm the advance manually before we process the order.",
                    ]],
                    ['heading' => 'Delivery', 'paragraphs' => [
                        "We confirm the delivery time with you when we confirm your order. Delivery costs {$fee}.{$freeText} Please give a correct address and phone number so the courier can reach you.",
                    ]],
                    ['heading' => 'Returns', 'paragraphs' => [
                        'A painting that arrives damaged or wrong can be put right within 7 days. See the Returns & refunds page for details.',
                    ]],
                    ['heading' => 'Your account', 'paragraphs' => [
                        'Keep your password private. We may suspend accounts that are used to cheat the shop or other customers.',
                    ]],
                ],
            ],
        ];

        return $pages;
    }

    /**
     * The page as the shop shows it: the admin's text when there is some, the built-in wording otherwise.
     *
     * @return array{intro: string, sections: list<array<string, mixed>>}
     */
    public static function resolve(string $slug): array
    {
        $custom = trim((string) (((array) SiteContent::get('pages'))[$slug] ?? ''));

        return $custom !== '' ? self::fromText($custom) : self::defaults()[$slug];
    }

    /** @return array{intro: string, sections: list<array<string, mixed>>} */
    public static function fromText(string $text): array
    {
        $intro = [];
        $sections = [];
        $current = null;

        foreach (preg_split('/\R{2,}/', trim(str_replace("\r", '', $text))) ?: [] as $block) {
            $block = trim($block);
            $lines = array_values(array_filter(array_map('trim', explode("\n", $block)), fn ($l) => $l !== ''));

            // A block may start with a heading line, then carry its own text below it.
            while ($lines !== [] && str_starts_with($lines[0], '## ')) {
                if ($current) {
                    $sections[] = $current;
                }
                $current = ['heading' => trim(substr($lines[0], 3))];
                array_shift($lines);
            }

            if ($lines === []) {
                continue;
            }

            if (collect($lines)->every(fn ($l) => str_starts_with($l, '- '))) {
                $items = array_map(fn ($l) => trim(substr($l, 2)), $lines);
                if ($current) {
                    $current['items'] = array_merge($current['items'] ?? [], $items);
                } else {
                    $intro[] = implode(' ', $items);
                }

                continue;
            }

            $paragraph = implode(' ', $lines);
            if ($current) {
                $current['paragraphs'][] = $paragraph;
            } else {
                $intro[] = $paragraph;
            }
        }

        if ($current) {
            $sections[] = $current;
        }

        return ['intro' => implode(' ', $intro), 'sections' => $sections];
    }

    /** The reverse of fromText(), so the editor can start from the wording the page shows now. */
    public static function toText(array $page): string
    {
        $out = [trim((string) ($page['intro'] ?? ''))];

        foreach ($page['sections'] ?? [] as $section) {
            $out[] = '## '.($section['heading'] ?? '');

            foreach ($section['paragraphs'] ?? [] as $p) {
                $out[] = $p;
            }

            if (! empty($section['items'])) {
                $out[] = implode("\n", array_map(fn ($i) => '- '.$i, $section['items']));
            }
        }

        return trim(implode("\n\n", array_filter($out, fn ($b) => $b !== '')));
    }
}
