<?php

namespace Tests\Unit;

use App\Support\MetaIgImportNormalizer;
use PHPUnit\Framework\TestCase;

class MetaIgImportNormalizerTest extends TestCase
{
    public function test_it_normalizes_story_rows_with_flexible_headers(): void
    {
        $normalizer = new MetaIgImportNormalizer();

        $rows = $normalizer->normalizeImportRows([
            [
                "\ufeffPost ID" => '17933202951319110',
                'Account username' => 'purapura.ponsel',
                'Account name' => 'Pura Pura Ponsel',
                'Description' => 'Story promo',
                'Duration (sec)' => '12',
                'Publish time' => '06/29/2026 21:04',
                'Permalink' => 'https://www.instagram.com/stories/purapura.ponsel/3930668915601152621',
                'Post type' => 'IG story',
                'Views' => '280',
                'Reach' => '238',
                'Likes' => '1',
                'Shares' => '0',
                'Profile visits' => '4',
                'Replies' => '0',
                'Navigation' => '251',
                'Link clicks' => '',
                'Sticker taps' => '',
                'Follows' => '',
            ],
        ], 'story');

        $this->assertCount(1, $rows);
        $this->assertSame('17933202951319110', $rows[0]['post_id']);
        $this->assertSame('purapura.ponsel', $rows[0]['account']);
        $this->assertSame('2026-06-30 12:04:00', $rows[0]['publish_time']);
        $this->assertSame(280, $rows[0]['views']);
        $this->assertSame(251, $rows[0]['navigation']);
        $this->assertSame(0, $rows[0]['link_clicks']);
        $this->assertSame(0, $rows[0]['sticker_taps']);
        $this->assertArrayHasKey('raw_payload', $rows[0]);
    }

    public function test_it_accepts_canonical_keys_and_aliases_for_feed_rows(): void
    {
        $normalizer = new MetaIgImportNormalizer();

        $rows = $normalizer->normalizeImportRows([
            [
                'post_id' => '17912380476180805',
                'account_name' => 'Pura Pura Ponsel',
                'account username' => 'purapura.ponsel',
                'caption' => 'Feed reel promo',
                'date published' => '2026-06-29 10:30:00',
                'content type' => 'REEL',
                'views' => '1,500',
                'reach' => '1,200',
                'likes' => '120',
                'shares' => '10',
                'follows' => '8',
                'comments' => '14',
                'saves' => '35',
            ],
        ], 'feed');

        $this->assertCount(1, $rows);
        $this->assertSame('Feed reel promo', $rows[0]['description']);
        $this->assertSame('2026-06-30 01:30:00', $rows[0]['publish_time']);
        $this->assertSame('REEL', $rows[0]['post_type']);
        $this->assertSame(1500, $rows[0]['views']);
        $this->assertSame(35, $rows[0]['saves']);
    }
}
