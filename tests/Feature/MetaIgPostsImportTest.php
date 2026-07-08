<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MetaIgPostsImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_story_import_normalizes_raw_meta_headers_before_upsert(): void
    {
        $this->postJson('/api/meta-posts/story/import', [
            'rows' => [
                [
                    'Navigation' => '251',
                    'Account username' => 'purapura.ponsel',
                    'Publish time' => '06/29/2026 21:04',
                    'Views' => '280',
                    'Post ID' => '17933202951319110',
                    'Post type' => 'IG story',
                    'Description' => 'Story promo',
                    'Reach' => '238',
                    'Profile visits' => '4',
                    'Replies' => '0',
                    'Link clicks' => '',
                    'Sticker taps' => '',
                    'Follows' => '',
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('inserted', 1)
            ->assertJsonPath('updated', 0);

        $this->assertDatabaseHas('meta_ig_posts', [
            'post_id' => '17933202951319110',
            'dataset' => 'story',
            'account' => 'purapura.ponsel',
            'publish_time' => '2026-06-30 12:04:00',
            'views' => 280,
            'navigation' => 251,
        ]);
    }

    public function test_feed_import_updates_existing_post_from_alias_headers(): void
    {
        $this->postJson('/api/meta-posts/feed/import', [
            'rows' => [
                [
                    'Post ID' => '17912380476180805',
                    'Account username' => 'purapura.ponsel',
                    'Description' => 'Feed lama',
                    'Publish time' => '06/29/2026 10:30',
                    'Post type' => 'REEL',
                    'Views' => '1000',
                    'Reach' => '900',
                    'Comments' => '3',
                    'Shares' => '4',
                    'Saves' => '5',
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('inserted', 1);

        $this->postJson('/api/meta-posts/feed/import', [
            'rows' => [
                [
                    'post_id' => '17912380476180805',
                    'account username' => 'purapura.ponsel',
                    'caption' => 'Feed update',
                    'date published' => '2026-06-29 10:30:00',
                    'content type' => 'REEL',
                    'views' => '1,500',
                    'reach' => '1,200',
                    'comments' => '14',
                    'shares' => '10',
                    'saves' => '35',
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('status', 'confirm_required')
            ->assertJsonPath('requires_confirmation', true)
            ->assertJsonPath('duplicates', 1);

        $this->postJson('/api/meta-posts/feed/import', [
            'overwrite' => true,
            'rows' => [
                [
                    'post_id' => '17912380476180805',
                    'account username' => 'purapura.ponsel',
                    'caption' => 'Feed update',
                    'date published' => '2026-06-29 10:30:00',
                    'content type' => 'REEL',
                    'views' => '1,500',
                    'reach' => '1,200',
                    'comments' => '14',
                    'shares' => '10',
                    'saves' => '35',
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('inserted', 0)
            ->assertJsonPath('updated', 1);

        $this->getJson('/api/meta-posts/feed')
            ->assertOk()
            ->assertJsonPath('data.0.post_id', '17912380476180805')
            ->assertJsonPath('data.0.description', 'Feed update')
            ->assertJsonPath('data.0.views', 1500)
            ->assertJsonPath('data.0.comments', 14)
            ->assertJsonPath('data.0.saves', 35);
    }

    public function test_story_import_can_scan_export_meta_folder_and_skip_feed_files(): void
    {
        $directory = sys_get_temp_dir().'/meta-import-'.uniqid();
        File::ensureDirectoryExists($directory);

        $storyFile = $directory.'/test-meta-story.csv';
        $feedFile = $directory.'/test-meta-feed.csv';

        File::put($storyFile, implode("\n", [
            '"Post ID","Account username","Description","Publish time","Post type","Views","Reach","Navigation","Profile visits","Replies","Link clicks","Sticker taps","Follows"',
            '"17933202951319110","purapura.ponsel","Story promo","06/29/2026 21:04","IG story","280","238","251","4","0","","",""',
        ]));
        File::put($feedFile, implode("\n", [
            '"Post ID","Account username","Description","Publish time","Post type","Views","Reach","Comments","Shares","Saves"',
            '"17912380476180805","purapura.ponsel","Feed promo","06/29/2026 10:30","REEL","1500","1200","14","10","35"',
        ]));

        try {
            $this->postJson('/api/meta-posts/story/import-folder', ['directory' => $directory])->assertOk()
                ->assertJsonPath('inserted', 1)
                ->assertJsonPath('updated', 0)
                ->assertJsonPath('files_scanned', 2)
                ->assertJsonPath('files_matched', 1);

            $this->assertDatabaseHas('meta_ig_posts', [
                'post_id' => '17933202951319110',
                'dataset' => 'story',
            ]);

            $this->assertDatabaseMissing('meta_ig_posts', [
                'post_id' => '17912380476180805',
            ]);
        } finally {
            File::delete([$storyFile, $feedFile]);
            File::deleteDirectory($directory);
        }
    }

    public function test_feed_import_requires_confirmation_before_overwriting_existing_posts(): void
    {
        $this->postJson('/api/meta-posts/feed/import', [
            'rows' => [
                [
                    'Post ID' => '17912380476180805',
                    'Account username' => 'purapura.ponsel',
                    'Description' => 'Feed awal',
                    'Publish time' => '06/29/2026 10:30',
                    'Post type' => 'REEL',
                    'Views' => '1000',
                    'Reach' => '900',
                    'Comments' => '3',
                    'Shares' => '4',
                    'Saves' => '5',
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('inserted', 1);

        $this->postJson('/api/meta-posts/feed/import', [
            'rows' => [
                [
                    'Post ID' => '17912380476180805',
                    'Account username' => 'purapura.ponsel',
                    'Description' => 'Feed overwrite',
                    'Publish time' => '06/29/2026 10:30',
                    'Post type' => 'REEL',
                    'Views' => '1500',
                    'Reach' => '1200',
                    'Comments' => '14',
                    'Shares' => '10',
                    'Saves' => '35',
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('status', 'confirm_required')
            ->assertJsonPath('requires_confirmation', true)
            ->assertJsonPath('duplicates', 1)
            ->assertJsonPath('inserted', 0)
            ->assertJsonPath('updated', 0);

        $this->assertDatabaseHas('meta_ig_posts', [
            'post_id' => '17912380476180805',
            'description' => 'Feed awal',
            'views' => 1000,
        ]);

        $this->postJson('/api/meta-posts/feed/import', [
            'overwrite' => true,
            'rows' => [
                [
                    'Post ID' => '17912380476180805',
                    'Account username' => 'purapura.ponsel',
                    'Description' => 'Feed overwrite',
                    'Publish time' => '06/29/2026 10:30',
                    'Post type' => 'REEL',
                    'Views' => '1500',
                    'Reach' => '1200',
                    'Comments' => '14',
                    'Shares' => '10',
                    'Saves' => '35',
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('inserted', 0)
            ->assertJsonPath('updated', 1);

        $this->assertDatabaseHas('meta_ig_posts', [
            'post_id' => '17912380476180805',
            'description' => 'Feed overwrite',
            'views' => 1500,
        ]);
    }
}
